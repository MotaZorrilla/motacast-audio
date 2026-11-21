<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ChunkedUploadService
{
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'docx', 'doc', 'txt', 'md', 'markdown',
        'png', 'jpg', 'jpeg', 'webp', 'bmp',
        'mp3', 'wav', 'm4a', 'ogg', 'opus', 'aac', 'flac',
        'mp4', 'mkv', 'mov', 'avi', 'webm',
    ];

    public const AUDIO_VIDEO_EXTENSIONS = [
        'mp3', 'wav', 'm4a', 'ogg', 'opus', 'aac', 'flac',
        'mp4', 'mkv', 'mov', 'avi', 'webm',
    ];

    /**
     * Resolve base directory for chunked uploads.
     */
    protected function baseDirectory(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'chunks');
    }

    /**
     * Validate UUID format for a chunked upload session.
     *
     * @throws Exception
     */
    protected function assertValidUuid(string $uploadId): void
    {
        if (! Str::isUuid($uploadId)) {
            throw new Exception('Identificador de carga fragmentada inválido.');
        }
    }

    /**
     * Store a single chunk and assemble the complete file once the final chunk arrives.
     *
     * @return array{completed: bool, upload_id: string, filename: string, size?: int}
     *
     * @throws Exception
     */
    public function storeChunk(
        string $uploadId,
        int $chunkIndex,
        int $totalChunks,
        string $originalFilename,
        UploadedFile $chunkFile
    ): array {
        $this->assertValidUuid($uploadId);

        if ($chunkIndex < 0 || $chunkIndex >= $totalChunks) {
            throw new Exception('El índice del fragmento excede el total declarado.');
        }

        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new Exception("Extensión de archivo no permitida: .{$ext}");
        }

        $dir = $this->baseDirectory();
        File::ensureDirectoryExists($dir);

        if ($chunkIndex === 0) {
            $minFreeBytes = (int) config('audiolibros.uploads.min_free_disk_bytes', 512 * 1024 * 1024);
            $freeDisk = @disk_free_space($dir);
            if ($freeDisk !== false && $freeDisk < $minFreeBytes) {
                throw new Exception('Espacio insuficiente en el servidor para iniciar una subida grande.');
            }
            $this->purgeExpiredChunks((int) config('audiolibros.uploads.chunk_ttl_hours', 24) * 3600);
        }

        $chunkFile->move($dir, "{$uploadId}.part.{$chunkIndex}");

        if ($chunkIndex < $totalChunks - 1) {
            return [
                'completed' => false,
                'upload_id' => $uploadId,
                'filename' => basename($originalFilename),
            ];
        }

        $lock = Cache::lock("chunk_assemble_{$uploadId}", 120);
        if (! $lock->get()) {
            throw new Exception('El archivo ya se está ensamblando en este momento.');
        }

        try {
            for ($i = 0; $i < $totalChunks; $i++) {
                if (! file_exists($dir.DIRECTORY_SEPARATOR."{$uploadId}.part.{$i}")) {
                    throw new Exception("Falta el fragmento #{$i} para ensamblar el archivo.");
                }
            }

            $assembledFilename = "{$uploadId}.assembled.{$ext}";
            $assembledPath = $dir.DIRECTORY_SEPARATOR.$assembledFilename;
            $outHandle = fopen($assembledPath, 'wb');
            if ($outHandle === false) {
                throw new Exception('No se pudo crear el archivo ensamblado en almacenamiento temporal.');
            }

            try {
                for ($i = 0; $i < $totalChunks; $i++) {
                    $partPath = $dir.DIRECTORY_SEPARATOR."{$uploadId}.part.{$i}";
                    $inHandle = fopen($partPath, 'rb');
                    if ($inHandle === false) {
                        throw new Exception("No se pudo leer el fragmento #{$i}.");
                    }
                    stream_copy_to_stream($inHandle, $outHandle);
                    fclose($inHandle);
                    @unlink($partPath);
                }
            } finally {
                fclose($outHandle);
            }

            $safeName = basename($originalFilename);
            $size = filesize($assembledPath) ?: 0;

            file_put_contents($dir.DIRECTORY_SEPARATOR."{$uploadId}.meta.json", json_encode([
                'original_filename' => $safeName,
                'extension' => $ext,
                'assembled_path' => $assembledFilename,
                'size' => $size,
            ], JSON_UNESCAPED_UNICODE));

            return [
                'completed' => true,
                'upload_id' => $uploadId,
                'filename' => $safeName,
                'size' => $size,
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * Remove stale chunk files left behind by abandoned uploads.
     */
    public function purgeExpiredChunks(int $maxAgeSeconds = 86400): int
    {
        $dir = $this->baseDirectory();
        if (! is_dir($dir)) {
            return 0;
        }

        $removed = 0;
        $cutoff = time() - $maxAgeSeconds;
        foreach (File::files($dir) as $file) {
            $path = $file->getPathname();
            $mtime = @filemtime($path);
            if ($mtime !== false && $mtime < $cutoff) {
                if (@unlink($path)) {
                    $removed++;
                }
            }
        }

        return $removed;
    }

    /**
     * Resolve metadata and absolute path for a completed chunked upload.
     *
     * @return array{path: string, original_filename: string, extension: string, size: int}
     *
     * @throws Exception
     */
    public function resolveCompletedUpload(string $uploadId): array
    {
        $this->assertValidUuid($uploadId);

        $dir = $this->baseDirectory();
        $metaPath = $dir.DIRECTORY_SEPARATOR."{$uploadId}.meta.json";

        if (! file_exists($metaPath)) {
            throw new Exception('No se encontró la carga fragmentada solicitada o aún está incompleta.');
        }

        $meta = json_decode((string) file_get_contents($metaPath), true);
        if (! is_array($meta) || empty($meta['assembled_path'])) {
            throw new Exception('Metadatos de carga fragmentada corruptos.');
        }

        $assembledPath = $dir.DIRECTORY_SEPARATOR.basename((string) $meta['assembled_path']);
        if (! file_exists($assembledPath)) {
            throw new Exception('El archivo ensamblado ya no existe en el servidor.');
        }

        return [
            'path' => $assembledPath,
            'original_filename' => (string) ($meta['original_filename'] ?? basename($assembledPath)),
            'extension' => (string) ($meta['extension'] ?? pathinfo($assembledPath, PATHINFO_EXTENSION)),
            'size' => (int) ($meta['size'] ?? filesize($assembledPath)),
        ];
    }

    /**
     * Remove temporary chunk artifacts after consumption.
     */
    public function cleanup(string $uploadId): void
    {
        if (! Str::isUuid($uploadId)) {
            return;
        }

        $dir = $this->baseDirectory();
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.DIRECTORY_SEPARATOR."{$uploadId}.*") ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
