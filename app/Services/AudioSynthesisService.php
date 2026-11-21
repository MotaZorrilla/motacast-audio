<?php

namespace App\Services;

use Exception;
use Symfony\Component\Process\Process;

class AudioSynthesisService
{
    protected string $pythonBinary;

    protected string $scriptPath;

    protected TtsTextNormalizerService $normalizer;

    public function __construct(?TtsTextNormalizerService $normalizer = null)
    {
        $this->pythonBinary = (string) config('audiolibros.python_binary', PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3');
        $this->scriptPath = base_path('scripts/synthesize_tts.py');
        $this->normalizer = $normalizer ?? new TtsTextNormalizerService;
    }

    /**
     * Synthesizes text into an MP3 audio file using edge-tts.
     *
     * @throws Exception
     */
    public function synthesize(
        string $text,
        string $outputAbsolutePath,
        string $voice = 'es-VE-SebastianNeural',
        string $rate = '+0%',
        string $pitch = '+0Hz'
    ): array {
        if (empty(trim($text))) {
            throw new Exception('El texto a sintetizar no puede estar vacío.');
        }

        // Ensure target directory exists
        $dir = dirname($outputAbsolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Normalize text with NLP sentence bridging to ensure natural pauses only at punctuation
        $text = $this->normalizer->normalize($text);

        // Write text to a temporary file to avoid shell length constraints
        $tempTextFile = tempnam(sys_get_temp_dir(), 'mz_tts_');
        file_put_contents($tempTextFile, $text);

        try {
            $process = new Process([
                $this->pythonBinary,
                $this->scriptPath,
                '--text-file',
                $tempTextFile,
                '--output',
                $outputAbsolutePath,
                '--voice',
                $voice,
                '--rate',
                $rate,
                '--pitch',
                $pitch,
            ]);

            $process->setTimeout((int) config('audiolibros.timeouts.tts', 600));
            $process->run();

            if (! $process->isSuccessful()) {
                throw new Exception('Error en proceso TTS: '.$process->getErrorOutput());
            }

            $output = trim($process->getOutput());
            $data = json_decode($output, true);

            if (! $data || ! is_array($data)) {
                throw new Exception('Respuesta inválida de síntesis TTS: '.$output);
            }

            if (empty($data['success'])) {
                throw new Exception($data['error'] ?? 'Fallo desconocido en la síntesis de audio.');
            }

            return $data;

        } finally {
            if (file_exists($tempTextFile)) {
                @unlink($tempTextFile);
            }
        }
    }

    /**
     * Returns the curated list of recommended natural neural voices.
     */
    public static function getAvailableVoices(): array
    {
        return (array) config('audiolibros.voices', []);
    }

    /**
     * Bridges soft line-breaks and normalizes symbols, abbreviations and Masonic ritual formulas for natural audio playback.
     */
    public static function normalizeText(string $text, ?bool $forceMasonic = null): string
    {
        return (new TtsTextNormalizerService)->normalize($text, $forceMasonic);
    }
}
