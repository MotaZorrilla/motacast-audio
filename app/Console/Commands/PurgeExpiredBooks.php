<?php

namespace App\Console\Commands;

use App\Actions\Book\DeleteBookAction;
use App\Models\Book;
use App\Services\ChunkedUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeExpiredBooks extends Command
{
    protected $signature = 'audiolibros:purge-expired';

    protected $description = 'Elimina libros expirados (retención de 7 días para usuarios estándar) y fragmentos temporales huérfanos';

    public function handle(DeleteBookAction $deleteBookAction, ChunkedUploadService $chunkedUploadService): int
    {
        $expiredBooks = Book::expired()->get();
        $deletedCount = 0;

        foreach ($expiredBooks as $book) {
            $deleteBookAction->handle($book);
            $deletedCount++;
        }

        $chunkTtlSeconds = (int) config('audiolibros.uploads.chunk_ttl_hours', 24) * 3600;
        $purgedChunks = $chunkedUploadService->purgeExpiredChunks($chunkTtlSeconds);

        if ($deletedCount > 0 || $purgedChunks > 0) {
            Log::info("audiolibros:purge-expired completado: {$deletedCount} libros expirados eliminados, {$purgedChunks} chunks temporales depurados.");
        }

        $this->info("Purgados: {$deletedCount} libros expirados, {$purgedChunks} chunks temporales.");

        return self::SUCCESS;
    }
}
