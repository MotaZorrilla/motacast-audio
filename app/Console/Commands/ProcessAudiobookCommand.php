<?php

namespace App\Console\Commands;

use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use Illuminate\Console\Command;

class ProcessAudiobookCommand extends Command
{
    protected $signature = 'audiobook:process {book_id? : El ID del audiolibro a procesar} {--sync : Ejecutar de manera síncrona en lugar de encolar}';

    protected $description = 'Procesa un libro PDF, extrayendo texto y sintetizando audiolibro con edge-tts';

    public function handle(PdfExtractorService $extractor, AudioSynthesisService $synthesizer)
    {
        $bookId = $this->argument('book_id');

        if ($bookId) {
            $book = Book::find($bookId);
            if (! $book) {
                $this->error("No se encontró ningún audiolibro con el ID {$bookId}");

                return 1;
            }
        } else {
            $book = Book::whereIn('status', ['pending', 'failed'])->oldest()->first();
            if (! $book) {
                $this->info('No hay audiolibros pendientes de procesamiento.');

                return 0;
            }
        }

        $this->info("Procesando audiolibro ID: {$book->id} ('{$book->title}')");

        if ($this->option('sync')) {
            $job = new ProcessBookJob($book->id);
            $job->handle($extractor, $synthesizer);
            $book->refresh();
            if ($book->status === 'ready') {
                $this->info("¡Audiolibro procesado con éxito! Duración total: {$book->formatted_duration}");
            } else {
                $this->error("Fallo en el procesamiento: {$book->error_message}");

                return 1;
            }
        } else {
            ProcessBookJob::dispatch($book->id);
            $this->info('Tarea encolada en segundo plano con éxito.');
        }

        return 0;
    }
}
