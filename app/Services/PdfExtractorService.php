<?php

namespace App\Services;

use Exception;
use Symfony\Component\Process\Process;

class PdfExtractorService
{
    protected string $pythonBinary;

    protected string $scriptPath;

    public function __construct()
    {
        $this->pythonBinary = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $this->scriptPath = base_path('scripts/extract_pdf.py');
    }

    /**
     * Extracts and segments text from a PDF file.
     *
     * @throws Exception
     */
    public function extract(string $pdfAbsolutePath): array
    {
        if (! file_exists($pdfAbsolutePath)) {
            throw new Exception("El archivo PDF no existe en la ruta: {$pdfAbsolutePath}");
        }

        $process = new Process([
            $this->pythonBinary,
            $this->scriptPath,
            $pdfAbsolutePath,
        ]);

        $process->setTimeout(300); // 5 minutes max for huge PDFs
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception('Error al ejecutar extractor de PDF: '.$process->getErrorOutput());
        }

        $output = trim($process->getOutput());
        $data = json_decode($output, true);

        if (! $data || ! is_array($data)) {
            throw new Exception('Respuesta inválida del extractor de PDF: '.$output);
        }

        if (empty($data['success'])) {
            throw new Exception($data['error'] ?? 'No se pudo extraer texto del documento PDF.');
        }

        return $data;
    }
}
