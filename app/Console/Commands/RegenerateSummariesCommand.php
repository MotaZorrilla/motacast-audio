<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RegenerateSummariesCommand extends Command
{
    protected $signature = 'books:regenerate-summaries {book_id? : ID del libro específico} {--all : Forzar regeneración para todos los libros}';

    protected $description = 'Regenera el resumen ejecutivo en texto y el archivo de audio (summary.mp3) para libros existentes';

    public function handle(PdfExtractorService $extractor, AudioSynthesisService $synthesizer): int
    {
        $bookId = $this->argument('book_id');
        $forceAll = $this->option('all');

        $query = Book::query();

        if ($bookId) {
            $books = $query->where('id', $bookId)->get();
            if ($books->isEmpty()) {
                $this->error("No se encontró el libro con ID {$bookId}");
                return 1;
            }
        } elseif ($forceAll) {
            $books = $query->orderBy('id', 'asc')->get();
        } else {
            $books = $query->where(function ($q) {
                $q->whereNull('summary')
                  ->orWhere('summary', '')
                  ->orWhereNull('summary_audio_path')
                  ->orWhere('summary_audio_path', '');
            })->orderBy('id', 'asc')->get();
        }

        if ($books->isEmpty()) {
            $this->info("No hay libros pendientes de resumen o audio de resumen.");
            return 0;
        }

        $this->info("Se procesarán " . $books->count() . " libro(s)...");

        foreach ($books as $book) {
            $this->line("--------------------------------------------------");
            $this->info("Procesando Libro #{$book->id}: '{$book->title}'");

            // 1. Obtener o generar resumen de texto si falta
            if (empty($book->summary) || $forceAll) {
                $pdfAbsPath = !empty($book->pdf_path) ? Storage::disk('public')->path($book->pdf_path) : null;
                if ($pdfAbsPath && file_exists($pdfAbsPath)) {
                    $this->line("  -> Extrayendo texto y resumen desde archivo original...");
                    try {
                        $extraction = $extractor->extract($pdfAbsPath);
                        if (!empty($extraction['summary'])) {
                            $book->summary = $extraction['summary'];
                            $this->info("  ✓ Resumen generado: " . mb_substr($book->summary, 0, 70) . "...");
                        }
                    } catch (Throwable $e) {
                        $this->warn("  ! Extracción falló: " . $e->getMessage());
                    }
                }

                // Si aún no hay resumen, intentar construirlo desde el texto de los capítulos existentes
                if (empty($book->summary)) {
                    $chapterTexts = $book->chapters()->orderBy('chapter_number')->pluck('content_text')->implode("\n\n");
                    if (!empty($chapterTexts)) {
                        $this->line("  -> Construyendo resumen a partir del contenido de los capítulos...");
                        $sentences = preg_split('/(?<=[.!?])\s+/', trim($chapterTexts));
                        $validSentences = array_filter($sentences, fn($s) => mb_strlen(trim($s)) > 25);
                        $candidateSummary = implode(' ', array_slice($validSentences, 0, 3));
                        if (!empty($candidateSummary)) {
                            $book->summary = mb_substr($candidateSummary, 0, 600);
                            $this->info("  ✓ Resumen creado desde capítulos: " . mb_substr($book->summary, 0, 70) . "...");
                        }
                    }
                }
            }

            // 2. Sintetizar audio del resumen si hay texto de resumen
            if (!empty($book->summary) && mb_strlen($book->summary) >= 20) {
                $relativeSummaryPath = "audiobooks/{$book->id}/summary.mp3";
                $absoluteSummaryPath = Storage::disk('public')->path($relativeSummaryPath);

                $dir = dirname($absoluteSummaryPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $this->line("  -> Sintetizando audio del resumen con voz '{$book->voice}'...");
                try {
                    $synthesizer->synthesize(
                        text: $book->summary,
                        outputAbsolutePath: $absoluteSummaryPath,
                        voice: $book->voice ?? 'es-ES-AlvaroNeural',
                        rate: $book->speed_rate ?? '+0%',
                        pitch: $book->pitch ?? '+0Hz'
                    );
                    $book->summary_audio_path = $relativeSummaryPath;
                    $this->info("  ✓ Audio del resumen sintetizado con éxito.");
                } catch (Throwable $audioErr) {
                    $this->error("  ✗ Error al sintetizar audio de resumen: " . $audioErr->getMessage());
                    Log::warning("books:regenerate-summaries libro {$book->id}: " . $audioErr->getMessage());
                }
            } else {
                $this->warn("  ! No hay texto suficiente de resumen para sintetizar audio.");
            }

            $book->save();
        }

        $this->line("==================================================");
        $this->info("¡Proceso de regeneración completado!");

        return 0;
    }
}
