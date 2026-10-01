<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    /**
     * Display a listing of all audiobooks.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Book::with(['user'])->withCount('chapters')->latest();

        $usersList = collect();
        $selectedUserId = null;

        if ($user->isAdmin()) {
            $usersList = User::orderBy('name')->get(['id', 'name', 'email']);

            if ($request->filled('user_id') && $request->get('user_id') !== 'all') {
                $query->where('user_id', $request->get('user_id'));
                $selectedUserId = (int) $request->get('user_id');
            } elseif ($request->get('scope') === 'mine') {
                $query->where('user_id', $user->id);
            }
        } else {
            // Regular user only sees their own books
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        $books = $query->paginate(12)->withQueryString();

        // Stats calculation based on active query scope
        $statsQuery = clone $query;
        // Strip pagination limits
        $allIds = $statsQuery->pluck('id');

        $stats = [
            'total' => $allIds->count(),
            'ready' => Book::whereIn('id', $allIds)->where('status', 'ready')->count(),
            'processing' => Book::whereIn('id', $allIds)->whereIn('status', ['pending', 'extracting', 'synthesizing'])->count(),
            'total_hours' => round(Book::whereIn('id', $allIds)->sum('total_duration') / 3600, 1),
        ];

        return view('books.index', compact('books', 'stats', 'usersList', 'selectedUserId'));
    }

    /**
     * Show form to upload a new PDF for conversion.
     */
    public function create(?Request $request = null)
    {
        $request = $request ?? request();

        if ($request->has('reset') || $request->has('new') || $request->has('reset_trial')) {
            session()->forget(['guest_book_id', 'guest_upload_count']);
            session()->save();
        }

        $voices = AudioSynthesisService::getAvailableVoices();
        $registeredUsers = collect();

        if (!Auth::check()) {
            // Check guest upload count
            $guestCount = session('guest_upload_count', 0);
            if ($guestCount >= 1) {
                return redirect()->route('register')
                    ->with('info', 'Has utilizado tu conversión de prueba gratuita. Regístrate en 10 segundos para seguir subiendo documentos.');
            }
        } else {
            $user = Auth::user();
            if (!$user->canUploadBook()) {
                return redirect()->route('books.index')
                    ->with('error', "Has alcanzado tu cuota de {$user->book_limit} libros. Contacta al Administrador para ampliar tu cuenta.");
            }

            if ($user->isAdmin()) {
                $registeredUsers = User::orderBy('name')->get(['id', 'name', 'email']);
            }
        }

        return view('books.create', compact('voices', 'registeredUsers'));
    }

    /**
     * Explicitly reset guest trial session to allow uploading another document.
     */
    public function resetGuest(Request $request)
    {
        session()->forget(['guest_book_id', 'guest_upload_count']);
        session()->save();

        return redirect()->route('books.create')
            ->with('info', 'Tu sesión de prueba gratuita ha sido reiniciada con éxito. Puedes cargar o pegar un nuevo documento.');
    }

    /**
     * Live OCR preview for uploaded or pasted images.
     */
    public function ocrPreview(Request $request, PdfExtractorService $extractor)
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,webp,bmp|max:20480',
        ]);

        $file = $request->file('image');
        $tempPath = $file->getRealPath();

        try {
            $extraction = $extractor->extract($tempPath);
            $fullText = '';
            if (!empty($extraction['chapters'])) {
                foreach ($extraction['chapters'] as $ch) {
                    $fullText .= ($fullText ? "\n\n" : '') . $ch['text'];
                }
            }
            if (empty($fullText) && !empty($extraction['summary'])) {
                $fullText = $extraction['summary'];
            }

            return response()->json([
                'success' => true,
                'title' => $extraction['title'] ?? 'Texto Extraído con OCR',
                'text' => $fullText,
                'words' => $extraction['total_words'] ?? str_word_count($fullText),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error procesando OCR: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store and start processing a new PDF audiobook.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            $guestCount = session('guest_upload_count', 0);
            if ($guestCount >= 1) {
                return redirect()->route('register')
                    ->with('info', 'Has utilizado tu conversión de prueba gratuita. Regístrate para continuar.');
            }
        } else {
            $user = Auth::user();
            if (!$user->canUploadBook()) {
                return redirect()->route('books.index')
                    ->with('error', "Has alcanzado el límite de tu cuenta ({$user->book_limit} libros).");
            }
        }

        $allowedMimes = 'pdf,docx,doc,txt,md,markdown,png,jpg,jpeg,webp,bmp,mp3,wav,m4a,ogg,aac,flac';
        $request->validate([
            'pdf_file' => "required_without:raw_text|nullable|file|mimes:{$allowedMimes}|max:102400", // 100MB max
            'raw_text' => 'required_without:pdf_file|nullable|string|min:10',
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'voice' => 'required|string',
            'speed_rate' => 'required|string',
            'pitch' => 'required|string',
            'assigned_user_id' => 'nullable|exists:users,id',
        ], [
            'pdf_file.required_without' => 'Debes adjuntar un archivo o ingresar texto en el modo de pegado directo.',
            'raw_text.required_without' => 'Debes adjuntar un archivo o ingresar texto en el modo de pegado directo.',
            'raw_text.min' => 'El texto pegado debe contener al menos 20 caracteres.',
        ]);

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $originalFilename = $file->getClientOriginalName();
            $cleanTitle = $request->filled('title')
                ? $request->input('title')
                : pathinfo($originalFilename, PATHINFO_FILENAME);

            $storedPath = $file->store('pdfs', 'public');
        } else {
            $rawText = trim($request->input('raw_text'));
            if ($request->filled('title')) {
                $cleanTitle = $request->input('title');
            } else {
                $firstLine = strtok($rawText, "\r\n");
                $cleanTitle = \Illuminate\Support\Str::limit(trim(preg_replace('/^[#\s*_-]+/', '', $firstLine)), 50, '...');
                if (empty($cleanTitle)) {
                    $cleanTitle = 'Texto Directo ' . now()->format('d/m/Y H:i');
                }
            }
            $slug = \Illuminate\Support\Str::slug($cleanTitle) ?: 'texto-directo';
            $fileName = $slug . '-' . time() . '.txt';
            $storedPath = 'pdfs/' . $fileName;
            Storage::disk('public')->put($storedPath, $rawText);
            $originalFilename = $cleanTitle . '.txt';
        }

        // Determine owner
        $ownerId = null;
        if (Auth::check()) {
            $currentUser = Auth::user();
            if ($currentUser->isAdmin() && $request->filled('assigned_user_id')) {
                $ownerId = (int) $request->input('assigned_user_id');
            } else {
                $ownerId = $currentUser->id;
            }
        }

        $book = Book::create([
            'user_id' => $ownerId,
            'title' => $cleanTitle,
            'author' => $request->input('author'),
            'original_filename' => $originalFilename,
            'pdf_path' => $storedPath,
            'voice' => $request->input('voice'),
            'speed_rate' => $request->input('speed_rate'),
            'pitch' => $request->input('pitch'),
            'status' => 'pending',
        ]);

        if (!Auth::check()) {
            session(['guest_upload_count' => session('guest_upload_count', 0) + 1]);
            session(['guest_book_id' => $book->id]);
        }

        // Dispatch background processing job
        ProcessBookJob::dispatch($book->id);

        return redirect()->route('books.show', $book->id)
            ->with('success', 'Documento recibido exitosamente. La extracción y síntesis ha comenzado en segundo plano.');
    }

    /**
     * Display the audiobook player and chapter playlist.
     */
    public function show(Book $book)
    {
        $this->authorizeBookAccess($book);

        $book->load('chapters');

        return view('books.show', compact('book'));
    }

    /**
     * Stream the original document file inline for in-browser reading/download.
     */
    public function pdfStream(Book $book)
    {
        $this->authorizeBookAccess($book);

        $path = Storage::disk('public')->path($book->pdf_path);
        if (!file_exists($path)) {
            // Check fallback in pdfs/manifiesto_homelab.pdf if available
            $fallback = Storage::disk('public')->path('pdfs/manifiesto_homelab.pdf');
            if (file_exists($fallback)) {
                $path = $fallback;
            } else {
                abort(404, 'Archivo de documento no encontrado en el almacenamiento.');
            }
        }

        $ext = strtolower(pathinfo($book->original_filename ?? $book->pdf_path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'txt' => 'text/plain; charset=utf-8',
            'md' => 'text/markdown; charset=utf-8',
            'markdown' => 'text/markdown; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'ogg' => 'audio/ogg',
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . basename($book->original_filename) . '"',
        ]);
    }

    /**
     * Retrieve book text chapters and document metadata for universal in-app reader modal.
     */
    public function documentContent(Book $book)
    {
        $this->authorizeBookAccess($book);

        $book->load('chapters');
        $ext = strtolower(pathinfo($book->original_filename ?? $book->pdf_path, PATHINFO_EXTENSION));

        return response()->json([
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'file_type' => $ext ?: 'pdf',
            'filename' => $book->original_filename,
            'summary' => $book->summary,
            'total_chapters' => $book->chapters->count(),
            'total_duration' => $book->formatted_duration,
            'chapters' => $book->chapters->map(function ($ch) {
                return [
                    'id' => $ch->id,
                    'chapter_number' => $ch->chapter_number,
                    'title' => $ch->title,
                    'duration' => $ch->formatted_duration,
                    'word_count' => $ch->word_count,
                    'content_text' => $ch->content_text,
                ];
            }),
        ]);
    }

    /**
     * Status polling API endpoint for real-time frontend updates.
     */
    public function status(Book $book)
    {
        $this->authorizeBookAccess($book);

        $book->load('chapters');

        return response()->json([
            'id' => $book->id,
            'status' => $book->status,
            'progress' => $book->progress_percentage,
            'total_chapters' => $book->total_chapters,
            'processed_chapters' => $book->processed_chapters,
            'total_duration' => $book->formatted_duration,
            'error_message' => $book->error_message,
            'chapters' => $book->chapters->map(function ($ch) {
                return [
                    'id' => $ch->id,
                    'chapter_number' => $ch->chapter_number,
                    'title' => $ch->title,
                    'status' => $ch->status,
                    'duration' => $ch->formatted_duration,
                    'duration_seconds' => $ch->duration_seconds,
                    'audio_url' => $ch->audio_stream_url,
                    'download_url' => $ch->audio_download_url,
                ];
            }),
        ]);
    }

    /**
     * Retry processing for a failed book.
     */
    public function retry(Book $book)
    {
        $this->authorizeBookAccess($book);

        $book->update([
            'status' => 'pending',
            'error_message' => null,
            'processed_chapters' => 0,
        ]);

        ProcessBookJob::dispatch($book->id);

        return redirect()->route('books.show', $book->id)
            ->with('info', 'Reintentando procesamiento del documento.');
    }

    /**
     * Stream an audio chapter with HTTP 206 Partial Content support for seeking.
     */
    public function streamChapter(Request $request, Chapter $chapter)
    {
        $this->authorizeBookAccess($chapter->book);

        if (!$chapter->audio_path) {
            abort(404, 'Audio aún no disponible para este capítulo.');
        }

        $fullPath = Storage::disk('public')->path($chapter->audio_path);

        if (!file_exists($fullPath)) {
            abort(404, 'Archivo de audio no encontrado en el servidor.');
        }

        $size = filesize($fullPath);
        $file = @fopen($fullPath, 'rb');

        if (!$file) {
            abort(500, 'No se pudo abrir el archivo de audio.');
        }

        $start = 0;
        $end = $size - 1;
        $status = 200;
        $headers = [
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
        ];

        $range = $request->header('Range') ?? $request->server('HTTP_RANGE');
        if ($range) {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $range, $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
                $status = 206;
                $headers['Content-Range'] = sprintf('bytes %d-%d/%d', $start, $end, $size);
            }
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = $length;

        fseek($file, $start);

        return new StreamedResponse(function () use ($file, $length) {
            $buffer = 1024 * 64; // 64KB buffer
            $remaining = $length;
            while (!feof($file) && $remaining > 0 && connection_status() == 0) {
                $read = min($buffer, $remaining);
                echo fread($file, $read);
                flush();
                $remaining -= $read;
            }
            fclose($file);
        }, $status, $headers);
    }

    /**
     * Stream executive summary audio.
     */
    public function streamSummary(Book $book)
    {
        $this->authorizeBookAccess($book);

        if (!$book->summary_audio_path || !Storage::disk('public')->exists($book->summary_audio_path)) {
            abort(404, 'Audio del resumen no disponible.');
        }

        $fullPath = Storage::disk('public')->path($book->summary_audio_path);
        $size = filesize($fullPath);
        $file = fopen($fullPath, 'rb');

        $start = 0;
        $end = $size - 1;
        $status = 200;

        $headers = [
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ];

        if (request()->hasHeader('Range')) {
            $rangeHeader = request()->header('Range');
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $rangeHeader, $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
                $status = 206;
                $headers['Content-Range'] = sprintf('bytes %d-%d/%d', $start, $end, $size);
            }
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = $length;

        fseek($file, $start);

        return new StreamedResponse(function () use ($file, $length) {
            $buffer = 1024 * 64;
            $remaining = $length;
            while (!feof($file) && $remaining > 0 && connection_status() == 0) {
                $read = min($buffer, $remaining);
                echo fread($file, $read);
                flush();
                $remaining -= $read;
            }
            fclose($file);
        }, $status, $headers);
    }

    /**
     * Direct download of chapter MP3.
     */
    public function downloadChapter(Chapter $chapter)
    {
        $this->authorizeBookAccess($chapter->book);

        if (!$chapter->audio_path) {
            abort(404, 'Audio no generado.');
        }

        $safeName = sprintf(
            '%s_Capitulo_%02d.mp3',
            \Illuminate\Support\Str::slug($chapter->book->title),
            $chapter->chapter_number
        );

        return Storage::disk('public')->download($chapter->audio_path, $safeName);
    }

    /**
     * Delete book, its chapters, and stored files.
     */
    public function destroy(Book $book)
    {
        $this->authorizeBookAccess($book);

        // Delete PDF file
        if ($book->pdf_path && Storage::disk('public')->exists($book->pdf_path)) {
            Storage::disk('public')->delete($book->pdf_path);
        }

        // Delete audio folder
        $audioDir = "audiobooks/{$book->id}";
        if (Storage::disk('public')->exists($audioDir)) {
            Storage::disk('public')->deleteDirectory($audioDir);
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Documento y pistas de audio eliminados correctamente.');
    }

    /**
     * Check if user is authorized to access the book.
     */
    protected function authorizeBookAccess(Book $book): void
    {
        // Allow guest to access their converted trial book
        if (!Auth::check()) {
            if ((int) session('guest_book_id') === (int) $book->id) {
                return;
            }
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('login')
                    ->with('info', 'Debes iniciar sesión o registrarte para acceder a este audiolibro.')
            );
        }

        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ($book->user_id && $book->user_id !== $user->id) {
            abort(403, 'No tienes autorización para acceder a este audiolibro.');
        }
    }
}
