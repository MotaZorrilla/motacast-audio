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
    public function execute(Book $book): void
    {
        $bookId = $book->id;
        $pdfPath = $book->pdf_path;
        $audioDir = "audiobooks/{$bookId}";

        // Transacción en base de datos: asegura eliminación de capítulos y registros referenciados
        DB::transaction(function () use ($book) {
            $book->delete();
        });

        // Limpieza de activos físicos en disco después de asegurar el éxito de la BD
        if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        Storage::disk('public')->deleteDirectory($audioDir);

        Log::info("DeleteBookAction: Audiolibro ID {$bookId} y sus archivos asociados eliminados correctamente.");
    }
}
