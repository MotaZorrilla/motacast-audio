<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioStreamingService
{
    /**
     * Stream an audio file with HTTP 206 Partial Content byte-range support.
     *
     * @param string $fullDiskPath Absolute system path to the audio file
     * @param Request|null $request Incoming HTTP request containing Range headers
     * @param array $extraHeaders Optional additional headers (e.g. Cache-Control)
     * @return StreamedResponse
     */
    public function stream(string $fullDiskPath, ?Request $request = null, array $extraHeaders = []): StreamedResponse
    {
        if (!file_exists($fullDiskPath)) {
            abort(404, 'Archivo de audio no encontrado.');
        }

        $size = filesize($fullDiskPath);
        $file = @fopen($fullDiskPath, 'rb');

        if (!$file) {
            abort(500, 'No se pudo abrir el archivo de audio para transmisión.');
        }

        $request = $request ?? request();
        $start = 0;
        $end = $size - 1;
        $status = 200;

        $headers = array_merge([
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
        ], $extraHeaders);

        $range = $request->header('Range') ?? $request->server('HTTP_RANGE');
        if ($range && preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $range, $matches)) {
            $start = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
            $status = 206;
            $headers['Content-Range'] = sprintf('bytes %d-%d/%d', $start, $end, $size);
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = $length;

        fseek($file, $start);

        return new StreamedResponse(function () use ($file, $length) {
            $buffer = 1024 * 64; // 64KB chunk buffer
            $remaining = $length;
            while (!feof($file) && $remaining > 0 && connection_status() === 0) {
                $read = min($buffer, $remaining);
                echo fread($file, $read);
                flush();
                $remaining -= $read;
            }
            fclose($file);
        }, $status, $headers);
    }
}
