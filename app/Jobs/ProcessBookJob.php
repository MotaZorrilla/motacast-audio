<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\Chapter;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
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

    public function handle(PdfExtractorService $extractor, AudioSynthesisService $synthesizer): void
    {
        $book = Book::find($this->bookId);
        if (! $book) {
            Log::error("ProcessBookJob: Libro con ID {$this->bookId} no encontrado.");

            return;
        }

        try {
            // Step 1: Update status to extracting
            $book->update([
                'status' => 'extracting',
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

            $book->summary = !empty($extraction['summary']) ? $extraction['summary'] : null;
            $book->total_words = $extraction['total_words'] ?? 0;
            $book->total_chapters = count($extraction['chapters']);
            $book->processed_chapters = 0;
            $book->save();

            // Clear previous chapters if re-processing
            $book->chapters()->delete();

            // Create chapters
            foreach ($extraction['chapters'] as $chData) {
                Chapter::create([
                    'book_id' => $book->id,
                    'chapter_number' => $chData['chapter_number'],
                    'title' => $chData['title'],
                    'content_text' => $chData['text'],
                    'word_count' => $chData['word_count'],
                    'status' => 'pending',
                ]);
            }

            // Step 3: Synthesis
            $book->update(['status' => 'synthesizing']);

            // Synthesize Executive Summary Audio if summary is available
            if (!empty($book->summary) && mb_strlen($book->summary) >= 30) {
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
                    Log::warning("Error sintetizando resumen del libro {$book->id}: " . $summaryErr->getMessage());
                }
            }

            $chapters = $book->chapters()->orderBy('chapter_number')->get();
            $totalDuration = 0;

            foreach ($chapters as $chapter) {
                $chapter->update(['status' => 'synthesizing']);

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

                    $chapter->update([
                        'audio_path' => $relativeAudioPath,
                        'duration_seconds' => $duration,
                        'status' => 'ready',
                        'error_message' => null,
                    ]);

                    $book->increment('processed_chapters');
                    $book->update(['total_duration' => $totalDuration]);

                } catch (Throwable $chError) {
                    Log::error("Error sintetizando capítulo {$chapter->id}: ".$chError->getMessage());
                    $chapter->update([
                        'status' => 'failed',
                        'error_message' => $chError->getMessage(),
                    ]);
                }
            }

            // Step 4: Mark book as ready
            $book->update([
                'status' => 'ready',
                'total_duration' => $totalDuration,
            ]);

            Log::info("Audiolibro '{$book->title}' (ID: {$book->id}) procesado exitosamente.");

        } catch (Throwable $e) {
            Log::error("Error procesando audiolibro ID {$this->bookId}: ".$e->getMessage());
            $book->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
