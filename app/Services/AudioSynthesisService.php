<?php

namespace App\Services;

use Exception;
use Symfony\Component\Process\Process;

class AudioSynthesisService
{
    protected string $pythonBinary;

    protected string $scriptPath;

    public function __construct()
    {
        $this->pythonBinary = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $this->scriptPath = base_path('scripts/synthesize_tts.py');
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
        $text = self::normalizeText($text);

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

            $process->setTimeout(600); // 10 minutes max for long chapters
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
        return [
            [
                'id' => 'es-VE-SebastianNeural',
                'name' => 'Sebastián (Venezuela - Masculina)',
                'locale' => 'es-VE',
                'gender' => 'Male',
                'recommended' => true,
            ],
            [
                'id' => 'es-VE-PaolaNeural',
                'name' => 'Paola (Venezuela - Femenina)',
                'locale' => 'es-VE',
                'gender' => 'Female',
                'recommended' => false,
            ],
            [
                'id' => 'es-MX-DaliaNeural',
                'name' => 'Dalia (México - Femenina)',
                'locale' => 'es-MX',
                'gender' => 'Female',
                'recommended' => false,
            ],
            [
                'id' => 'es-MX-JorgeNeural',
                'name' => 'Jorge (México - Masculina)',
                'locale' => 'es-MX',
                'gender' => 'Male',
                'recommended' => false,
            ],
            [
                'id' => 'es-ES-AlvaroNeural',
                'name' => 'Álvaro (España - Masculina)',
                'locale' => 'es-ES',
                'gender' => 'Male',
                'recommended' => false,
            ],
            [
                'id' => 'es-ES-ElviraNeural',
                'name' => 'Elvira (España - Femenina)',
                'locale' => 'es-ES',
                'gender' => 'Female',
                'recommended' => false,
            ],
            [
                'id' => 'es-AR-TomasNeural',
                'name' => 'Tomás (Argentina - Masculina)',
                'locale' => 'es-AR',
                'gender' => 'Male',
                'recommended' => false,
            ],
            [
                'id' => 'es-CO-GonzaloNeural',
                'name' => 'Gonzalo (Colombia - Masculina)',
                'locale' => 'es-CO',
                'gender' => 'Male',
                'recommended' => false,
            ],
        ];
    }

    /**
     * Bridges soft line-breaks inside sentences into continuous text with spaces,
     * maintaining natural audio pauses only where punctuation actually exists.
     */
    public static function normalizeText(string $text): string
    {
        if (empty(trim($text))) {
            return '';
        }

        // Standardize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Fix hyphenated word breaks at end of line (e.g. transfor-\nmación -> transformación)
        $text = preg_replace('/([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)-\n([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)/u', '$1$2', $text);

        // Standardize multiple line breaks to max 2 (\n\n) for real paragraphs
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Bridge any single line-break (not preceded or followed by \n) into a single space
        $text = preg_replace('/(?<!\n)\n(?!\n)/u', ' ', $text);

        // Collapse duplicate horizontal spaces
        $text = preg_replace('/[ \t]+/', ' ', $text);

        return trim($text);
    }
}
