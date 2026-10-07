<?php

namespace App\Jobs;

use App\Enums\BookStatus;
use App\Enums\ChapterStatus;
use App\Models\Book;
use App\Models\Chapter;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use App\Services\TtsTextNormalizerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessBookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookId;

    public int $timeout = 1800; // 30 minutes for large books

    public function __construct(int $bookId)
    {
        $this->bookId = $bookId;
    }

    public function handle(
        PdfExtractorService $extractor,
        AudioSynthesisService $synthesizer,
        TtsTextNormalizerService $normalizer
    ): void {
        $book = Book::find($this->bookId);
        if (! $book) {
            Log::error("ProcessBookJob: Libro con ID {$this->bookId} no encontrado.");

            return;
        }

        try {
            // Step 1: Update status to extracting
            $book->update([
                'status' => BookStatus::Extracting->value,
                'error_message' => null,
            ]);

            $pdfAbsolutePath = Storage::disk('public')->path($book->pdf_path);
            if (! file_exists($pdfAbsolutePath)) {
                throw new \Exception("El archivo PDF no se encuentra en el almacenamiento: {$pdfAbsolutePath}");
            }

            // Step 2: Extract text and chapters from PDF
            $extraction = $extractor->extract($pdfAbsolutePath);

            if (empty($book->title) || $book->title === 'Documento Sin Título') {
                $book->title = ! empty($extraction['title']) ? $extraction['title'] : 'Audiolibro #'.$book->id;
            }
            if (empty($book->author) && ! empty($extraction['author']) && $extraction['author'] !== 'Autor Desconocido') {
                $book->author = $extraction['author'];
            }

            $book->summary = ! empty($extraction['summary']) ? $extraction['summary'] : null;
            $book->total_words = $extraction['total_words'] ?? 0;
            $book->total_chapters = count($extraction['chapters']);
            $book->processed_chapters = 0;
            $book->save();

            // Clear previous chapters if re-processing
            $book->chapters()->delete();

            // Create chapters using injected normalizer (Dependency Injection)
            foreach ($extraction['chapters'] as $chData) {
                $rawText = $chData['text'] ?? '';
                $cleanContent = $normalizer->reconstructTripunctuatedText($rawText);

                Chapter::create([
                    'book_id' => $book->id,
                    'chapter_number' => $chData['chapter_number'],
                    'title' => $chData['title'],
                    'content_text' => $cleanContent,
                    'word_count' => $chData['word_count'],
                    'status' => ChapterStatus::Pending->value,
                ]);
            }

            // Step 3: Synthesis
            $book->update(['status' => BookStatus::Synthesizing->value]);

            // Synthesize Executive Summary Audio if summary is available
            if (! empty($book->summary) && mb_strlen($book->summary) >= 30) {
                $relativeSummaryPath = "audiobooks/{$book->id}/summary.mp3";
                $absoluteSummaryPath = Storage::disk('public')->path($relativeSummaryPath);
                try {
                    $synthesizer->synthesize(
                        text: $book->summary,
                        outputAbsolutePath: $absoluteSummaryPath,
                        voice: $book->voice,
                        rate: $book->speed_rate,
                        pitch: $book->pitch
                    );
                    $book->summary_audio_path = $relativeSummaryPath;
                    $book->save();
                } catch (Throwable $summaryErr) {
                    Log::warning("Error sintetizando resumen del libro {$book->id}: ".$summaryErr->getMessage());
                }
            }

            $chapters = $book->chapters()->orderBy('chapter_number')->get();
            $totalDuration = 0;
            $processedCount = 0;

            foreach ($chapters as $chapter) {
                $chapter->update(['status' => ChapterStatus::Synthesizing->value]);

                $relativeAudioPath = "audiobooks/{$book->id}/chapter_{$chapter->chapter_number}.mp3";
                $absoluteAudioPath = Storage::disk('public')->path($relativeAudioPath);

                try {
                    $ttsResult = $synthesizer->synthesize(
                        text: $chapter->content_text,
                        outputAbsolutePath: $absoluteAudioPath,
                        voice: $book->voice,
                        rate: $book->speed_rate,
                        pitch: $book->pitch
                    );

                    $duration = $ttsResult['duration_seconds'] ?? 0;
                    $totalDuration += $duration;
                    $processedCount++;

                    $chapter->update([
                        'audio_path' => $relativeAudioPath,
                        'duration_seconds' => $duration,
                        'status' => ChapterStatus::Ready->value,
                        'error_message' => null,
                    ]);

                    // Optimización Clean: Actualización consolidada en base de datos en una sola llamada
                    $book->update([
                        'processed_chapters' => $processedCount,
                        'total_duration' => $totalDuration,
                    ]);

                } catch (Throwable $chError) {
                    Log::error("Error sintetizando capítulo {$chapter->id}: ".$chError->getMessage());
                    $chapter->update([
                        'status' => ChapterStatus::Failed->value,
                        'error_message' => $chError->getMessage(),
                    ]);
                }
            }

            // Step 4: Mark book as ready
            $book->update([
                'status' => BookStatus::Ready->value,
                'total_duration' => $totalDuration,
            ]);

            // If the source was an audio or video file and user chose not to keep it,
            // delete the heavy source file to optimize server storage.
            $ext = strtolower(pathinfo($book->pdf_path, PATHINFO_EXTENSION));
            $mediaExts = ['mp3', 'wav', 'm4a', 'ogg', 'aac', 'flac', 'mp4', 'mkv', 'mov', 'avi', 'webm'];
            if (! $book->keep_original_media && in_array($ext, $mediaExts)) {
                if (Storage::disk('public')->exists($book->pdf_path)) {
                    Storage::disk('public')->delete($book->pdf_path);
                    Log::info("ProcessBookJob: Archivo fuente multimedia {$book->pdf_path} (Libro ID: {$book->id}) descartado tras extracción para ahorrar almacenamiento.");
                }
            }

            Log::info("Audiolibro '{$book->title}' (ID: {$book->id}) procesado exitosamente.");

        } catch (Throwable $e) {
            Log::error("Error procesando audiolibro ID {$this->bookId}: ".$e->getMessage());
            $book->update([
                'status' => BookStatus::Failed->value,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
