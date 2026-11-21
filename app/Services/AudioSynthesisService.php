<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
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
     * Synthesizes text into an MP3 audio file using edge-tts with automatic retries on transient failures.
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

        $maxAttempts = max(1, (int) config('audiolibros.tts.max_attempts', 3));
        $baseDelayMs = max(0, (int) config('audiolibros.tts.retry_delay_ms', 1500));

        try {
            $lastException = null;

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                try {
                    return $this->attemptSynthesis(
                        tempTextFile: $tempTextFile,
                        outputAbsolutePath: $outputAbsolutePath,
                        voice: $voice,
                        rate: $rate,
                        pitch: $pitch
                    );
                } catch (Exception $e) {
                    $lastException = $e;

                    if (file_exists($outputAbsolutePath) && (int) @filesize($outputAbsolutePath) === 0) {
                        @unlink($outputAbsolutePath);
                    }

                    if ($attempt < $maxAttempts) {
                        $delayMs = $baseDelayMs * $attempt;
                        Log::warning("AudioSynthesisService: intento {$attempt}/{$maxAttempts} fallido ({$e->getMessage()}). Reintentando en {$delayMs}ms.");
                        if ($delayMs > 0) {
                            $this->sleepMilliseconds($delayMs);
                        }
                    }
                }
            }

            throw $lastException ?? new Exception('Fallo desconocido en la síntesis de audio.');
        } finally {
            if (file_exists($tempTextFile)) {
                @unlink($tempTextFile);
            }
        }
    }

    /**
     * Executes a single TTS synthesis attempt and parses both stdout JSON errors and stderr.
     *
     * @throws Exception
     */
    protected function attemptSynthesis(
        string $tempTextFile,
        string $outputAbsolutePath,
        string $voice,
        string $rate,
        string $pitch
    ): array {
        $result = $this->executeProcess([
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

        $stdout = trim((string) ($result['stdout'] ?? ''));
        $stderr = trim((string) ($result['stderr'] ?? ''));
        $decoded = $stdout !== '' ? json_decode($stdout, true) : null;

        if (! ($result['successful'] ?? false)) {
            $errorDetail = $this->resolveProcessErrorDetail($decoded, $stderr, $stdout, $result['exit_code'] ?? null);
            throw new Exception('Error en proceso TTS: '.$errorDetail);
        }

        if (! is_array($decoded)) {
            throw new Exception('Respuesta inválida de síntesis TTS: '.$stdout);
        }

        if (empty($decoded['success'])) {
            throw new Exception((string) ($decoded['error'] ?? 'Fallo desconocido en la síntesis de audio.'));
        }

        return $decoded;
    }

    /**
     * Runs the external Python process. Extracted for deterministic unit testing.
     *
     * @param  array<int, string>  $command
     * @return array{successful: bool, stdout: string, stderr: string, exit_code: int|null}
     */
    protected function executeProcess(array $command): array
    {
        $process = new Process($command);
        $process->setTimeout((int) config('audiolibros.timeouts.tts', 600));
        $process->run();

        return [
            'successful' => $process->isSuccessful(),
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
            'exit_code' => $process->getExitCode(),
        ];
    }

    /**
     * Sleeps between retries. Extracted for instant unit tests.
     */
    protected function sleepMilliseconds(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }

    /**
     * Extracts the most informative error message from stdout JSON, stderr, raw stdout, or exit code.
     */
    protected function resolveProcessErrorDetail(mixed $decodedStdout, string $stderr, string $stdout, ?int $exitCode): string
    {
        if (is_array($decodedStdout) && ! empty($decodedStdout['error'])) {
            return trim((string) $decodedStdout['error']);
        }

        $parts = array_values(array_filter([$stderr, $stdout], fn (string $part) => $part !== ''));
        if (! empty($parts)) {
            return implode(' | ', $parts);
        }

        return $exitCode !== null
            ? "Proceso terminado con código {$exitCode} sin salida de error."
            : 'Fallo sin detalle de salida.';
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
