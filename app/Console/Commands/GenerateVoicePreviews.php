<?php

namespace App\Console\Commands;

use App\Http\Controllers\VoicePreviewController;
use App\Services\AudioSynthesisService;
use Illuminate\Console\Command;

class GenerateVoicePreviews extends Command
{
    protected $signature = 'voices:generate-previews {--force : Sobrescribir archivos existentes}';

    protected $description = 'Pre-genera todas las muestras de audio para previsualización instantánea de voces neuronales';

    public function handle(AudioSynthesisService $synthesisService): int
    {
        $voices = AudioSynthesisService::getAvailableVoices();
        $previewDir = storage_path('app/public/voice_previews');

        if (! is_dir($previewDir)) {
            mkdir($previewDir, 0755, true);
        }

        $this->info('Iniciando pre-generación de ' . count($voices) . ' muestras de voz...');

        $phrases = [
            'es-VE-SebastianNeural' => 'Hola, soy Sebastián de Venezuela. Con mi voz puedes escuchar tus libros y documentos con entonación natural.',
            'es-VE-PaolaNeural' => 'Hola, soy Paola de Venezuela. Convertiré tus lecturas en un audiolibro claro y fluido para estudiar.',
            'es-MX-DaliaNeural' => 'Hola, soy Dalia de México. Escucha tus artículos y temas favoritos con una narración profesional y nítida.',
            'es-MX-JorgeNeural' => 'Hola, soy Jorge de México. Transforma cualquier texto en audio para aprender y avanzar dondequiera que vayas.',
            'es-ES-AlvaroNeural' => 'Hola, soy Álvaro de España. Narraré tus documentos técnicos y obras con precisión y claridad castellana.',
            'es-ES-ElviraNeural' => 'Hola, soy Elvira de España. Convierte tus textos en una experiencia sonora envolvente y agradable.',
            'es-AR-TomasNeural' => 'Hola, soy Tomás de Argentina. Podés convertir tus lecturas y apuntes para escucharlos en cualquier momento.',
            'es-CO-GonzaloNeural' => 'Hola, soy Gonzalo de Colombia. Te acompañaré leyendo tus libros con un tono sereno y natural.',
        ];

        foreach ($voices as $v) {
            $voiceId = $v['id'];
            $filePath = $previewDir . DIRECTORY_SEPARATOR . $voiceId . '.mp3';

            if (file_exists($filePath) && ! $this->option('force')) {
                $this->line("  [OK] {$v['name']} (ya existe en caché)");
                continue;
            }

            $phrase = $phrases[$voiceId] ?? 'Hola, esta es una muestra de voz neuronal en MotaCastAudio.';
            $this->output->write("  [SYNTH] Generando muestra para {$v['name']}... ");

            try {
                $res = $synthesisService->synthesize($phrase, $filePath, $voiceId, '+0%', '+0Hz');
                $this->info("Listo ({$res['duration_seconds']}s, {$res['file_size']} bytes)");
            } catch (\Throwable $e) {
                $this->error('Error: ' . $e->getMessage());
            }
        }

        $this->info('Pre-generación completada exitosamente.');
        return Command::SUCCESS;
    }
}
