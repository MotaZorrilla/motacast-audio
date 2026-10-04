<?php

namespace App\Http\Controllers;

use App\Http\Requests\OcrPreviewRequest;
use App\Http\Requests\StoreBookRequest;
use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use App\Services\AudioStreamingService;
use App\Services\AudioSynthesisService;
use App\Services\DocumentIngestService;
use App\Services\GuestFingerprintService;
use App\Services\GuestSessionService;
use App\Services\PdfExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    public function __construct(
        protected GuestSessionService $guestSession,
        protected DocumentIngestService $ingestService,
        protected AudioStreamingService $streamingService
    ) {}

    /**
     * Display a listing of all audiobooks with search, filters and statistics.
     */
    public function index(Request $request): View
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

        $allIds = (clone $query)->pluck('id');
        $stats = [
            'total' => $allIds->count(),
            'ready' => Book::whereIn('id', $allIds)->where('status', 'ready')->count(),
            'processing' => Book::whereIn('id', $allIds)->whereIn('status', ['pending', 'extracting', 'synthesizing'])->count(),
            'total_hours' => round(Book::whereIn('id', $allIds)->sum('total_duration') / 3600, 1),
        ];

        return view('books.index', compact('books', 'stats', 'usersList', 'selectedUserId'));
    }

    /**
     * Show form to upload a new document or text for conversion.
     */
    public function create(?Request $request = null): View|RedirectResponse
    {
        $request = $request ?? request();

        if ($request->has('reset') || $request->has('new') || $request->has('reset_trial')) {
            $this->guestSession->resetSession();
        }

        if (!Auth::check() && $this->guestSession->isTrialExhausted()) {
            return redirect()->route('register')
                ->with('info', 'Has utilizado tu conversión de prueba gratuita. Regístrate en 10 segundos para seguir subiendo documentos.');
        }

        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->canUploadBook()) {
                return redirect()->route('books.index')
                    ->with('error', "Has alcanzado tu cuota de {$user->book_limit} libros. Contacta al Administrador para ampliar tu cuenta.");
            }
        }

        $voices = AudioSynthesisService::getAvailableVoices();
        $registeredUsers = (Auth::check() && Auth::user()->isAdmin())
            ? User::orderBy('name')->get(['id', 'name', 'email'])
            : collect();

        return view('books.create', compact('voices', 'registeredUsers'));
    }

    /**
     * Reset guest trial session to allow uploading another document.
     */
    public function resetGuest(Request $request): RedirectResponse
    {
        $this->guestSession->resetSession();

        return redirect()->route('books.create')
            ->with('info', 'Tu sesión de prueba gratuita ha sido reiniciada con éxito. Puedes cargar o pegar un nuevo documento.');
    }

    /**
     * Live OCR preview endpoint for images from upload or clipboard.
     */
    public function ocrPreview(OcrPreviewRequest $request, PdfExtractorService $extractor): JsonResponse
    {
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
     * Store and start processing a new audiobook.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        if (!Auth::check() && $this->guestSession->isTrialExhausted()) {
            return redirect()->route('register')
                ->with('info', 'Has utilizado tu conversión de prueba gratuita. Regístrate para continuar.');
        }

        if (Auth::check() && !Auth::user()->canUploadBook()) {
            return redirect()->route('books.index')
                ->with('error', "Has alcanzado el límite de tu cuenta (" . Auth::user()->book_limit . " libros).");
        }

        $ingested = $this->ingestService->ingest($request);

        $ownerId = null;
        if (Auth::check()) {
            $currentUser = Auth::user();
            $ownerId = ($currentUser->isAdmin() && $request->filled('assigned_user_id'))
                ? (int) $request->input('assigned_user_id')
                : $currentUser->id;
        }

        $geo = GuestFingerprintService::resolve($request);

        $book = Book::create([
            'user_id' => $ownerId,
            'guest_fingerprint' => Auth::check() ? null : $geo['alias'],
            'country_code' => $geo['country_code'],
            'title' => $ingested['title'],
            'author' => $ingested['author'],
            'original_filename' => $ingested['original_filename'],
            'pdf_path' => $ingested['stored_path'],
            'voice' => $request->input('voice'),
            'speed_rate' => $request->input('speed_rate'),
            'pitch' => $request->input('pitch'),
            'status' => 'pending',
        ]);

        $this->guestSession->recordTrialUpload($book->id);
        ProcessBookJob::dispatch($book->id);

        return redirect()->route('books.show', $book->id)
            ->with('success', 'Documento recibido exitosamente. La extracción y síntesis ha comenzado en segundo plano.');
    }

    /**
     * Display the audiobook player and chapter playlist.
     */
    public function show(Book $book): View
    {
        $this->authorizeBookAccess($book);
        $book->load('chapters');

        return view('books.show', compact('book'));
    }

    /**
     * Stream the original document file inline for in-browser reading/download.
     */
    public function pdfStream(Book $book): BinaryFileResponse
    {
        $this->authorizeBookAccess($book);

        $path = Storage::disk('public')->path($book->pdf_path);
        if (!file_exists($path)) {
            $fallback = Storage::disk('public')->path('pdfs/manifiesto_homelab.pdf');
            $path = file_exists($fallback) ? $fallback : abort(404, 'Archivo no encontrado en el almacenamiento.');
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

        return response()->file($path, [
            'Content-Type' => $mimeTypes[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($book->original_filename) . '"',
        ]);
    }

    /**
     * Retrieve book text chapters and metadata for universal in-app reader modal.
     */
    public function documentContent(Book $book): JsonResponse
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
            'chapters' => $book->chapters->map(fn($ch) => [
                'id' => $ch->id,
                'chapter_number' => $ch->chapter_number,
                'title' => $ch->title,
                'duration' => $ch->formatted_duration,
                'word_count' => $ch->word_count,
                'content_text' => $ch->content_text,
            ]),
        ]);
    }

    /**
     * Status polling API endpoint for real-time frontend updates.
     */
    public function status(Book $book): JsonResponse
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
            'chapters' => $book->chapters->map(fn($ch) => [
                'id' => $ch->id,
                'chapter_number' => $ch->chapter_number,
                'title' => $ch->title,
                'status' => $ch->status,
                'duration' => $ch->formatted_duration,
                'duration_seconds' => $ch->duration_seconds,
                'audio_url' => $ch->audio_stream_url,
                'download_url' => $ch->audio_download_url,
            ]),
        ]);
    }

    /**
     * Retry processing for a failed book.
     */
    public function retry(Book $book): RedirectResponse
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
     * Stream an audio chapter with HTTP 206 Partial Content support.
     */
    public function streamChapter(Request $request, Chapter $chapter): StreamedResponse
    {
        $this->authorizeBookAccess($chapter->book);

        // Self-healing: verify audio_path existence on disk; if missing check standard book chapter path
        if (!$chapter->audio_path || !Storage::disk('public')->exists($chapter->audio_path)) {
            $candidate = "audiobooks/{$chapter->book_id}/chapter_{$chapter->chapter_number}.mp3";
            if (Storage::disk('public')->exists($candidate)) {
                $chapter->update(['audio_path' => $candidate]);
            } else {
                abort(404, 'Audio aún no disponible para este capítulo.');
            }
        }

        $fullPath = Storage::disk('public')->path($chapter->audio_path);

        return $this->streamingService->stream($fullPath, $request);
    }

    /**
     * Stream executive summary audio.
     */
    public function streamSummary(Book $book): StreamedResponse
    {
        $this->authorizeBookAccess($book);

        if (!$book->summary_audio_path || !Storage::disk('public')->exists($book->summary_audio_path)) {
            abort(404, 'Audio del resumen no disponible.');
        }

        $fullPath = Storage::disk('public')->path($book->summary_audio_path);

        return $this->streamingService->stream($fullPath, request(), [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Direct download of chapter MP3.
     */
    public function downloadChapter(Chapter $chapter): StreamedResponse
    {
        $this->authorizeBookAccess($chapter->book);

        if (!$chapter->audio_path || !Storage::disk('public')->exists($chapter->audio_path)) {
            $candidate = "audiobooks/{$chapter->book_id}/chapter_{$chapter->chapter_number}.mp3";
            if (Storage::disk('public')->exists($candidate)) {
                $chapter->update(['audio_path' => $candidate]);
            } else {
                abort(404, 'Audio no generado.');
            }
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
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorizeBookAccess($book);

        if ($book->pdf_path && Storage::disk('public')->exists($book->pdf_path)) {
            Storage::disk('public')->delete($book->pdf_path);
        }

        $audioDir = "audiobooks/{$book->id}";
        if (Storage::disk('public')->exists($audioDir)) {
            Storage::disk('public')->deleteDirectory($audioDir);
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Documento y pistas de audio eliminados correctamente.');
    }

    /**
     * Verify whether the authenticated user, guest, or social media crawler is authorized to access the book.
     */
    protected function authorizeBookAccess(Book $book): void
    {
        // 1. Social media crawlers (WhatsApp, Facebook, Twitter, Telegram, etc.)
        // always allowed so they can parse Open Graph metadata for rich link previews.
        $userAgent = request()->header('User-Agent', '');
        if (preg_match('/(facebookexternalhit|WhatsApp|Twitterbot|TelegramBot|LinkedInBot|Slackbot|Discordbot|meta-externalagent)/i', $userAgent)) {
            return;
        }

        // 2. Allow shared demo / guest books (user_id === null) so WhatsApp recipients can listen
        if (is_null($book->user_id) || $this->guestSession->guestOwnsBook($book)) {
            return;
        }

        // 3. For registered private books, require login and owner/admin check
        if (!Auth::check()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('login')
                    ->with('info', 'Debes iniciar sesión para acceder a este audiolibro.')
            );
        }

        $user = Auth::user();
        if ($user->isAdmin() || $book->user_id === $user->id) {
            return;
        }

        abort(403, 'No tienes autorización para acceder a este audiolibro.');
    }
}
