<?php

namespace App\Actions\Book;

use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteBookAction
{
    /**
     * Elimina el registro del libro, sus relaciones y los activos físicos de almacenamiento de forma atómica.
     */
    public function handle(Book $book): void
    {
        $bookId = $book->id;
        $pdfPath = $book->pdf_path;
        $audioDir = "audiobooks/{$bookId}";

        // Transacción en base de datos: asegura eliminación de capítulos y registros referenciados
        DB::transaction(function () use ($book) {
            $book->delete();
        });

        // Limpieza de activos físicos en disco después de asegurar el éxito de la BD
        foreach (['public', 'local'] as $disk) {
            if ($pdfPath && Storage::disk($disk)->exists($pdfPath)) {
                Storage::disk($disk)->delete($pdfPath);
            }

            Storage::disk($disk)->deleteDirectory($audioDir);
        }

        Log::info("DeleteBookAction: Audiolibro ID {$bookId} y sus archivos asociados eliminados correctamente.");
    }

    /**
     * Alias por compatibilidad retroactiva.
     */
    public function execute(Book $book): void
    {
        $this->handle($book);
    }
}
