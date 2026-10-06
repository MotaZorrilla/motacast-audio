<?php

namespace App\Services;

class MimeTypeResolver
{
    /**
     * Matriz de mapeo de extensiones a tipos MIME estándar.
     *
     * @var array<string, string>
     */
    protected static array $mimeTypes = [
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

    /**
     * Resuelve el tipo de contenido MIME a partir de la ruta o nombre de archivo.
     */
    public static function resolve(?string $filename, string $default = 'application/octet-stream'): string
    {
        if (empty($filename)) {
            return $default;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return self::$mimeTypes[$ext] ?? $default;
    }

    /**
     * Retorna todas las extensiones soportadas y registradas.
     *
     * @return array<string>
     */
    public static function supportedExtensions(): array
    {
        return array_keys(self::$mimeTypes);
    }
}
