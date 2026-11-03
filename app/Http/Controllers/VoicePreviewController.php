<?php

namespace App\Http\Controllers;

use App\Services\AudioSynthesisService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VoicePreviewController extends Controller
{
    /**
     * Map of natural sample phrases per neural voice.
     */
    protected const SAMPLE_PHRASES = [
        'es-VE-SebastianNeural' => 'Hola, soy Sebastián de Venezuela. Con mi voz puedes escuchar tus libros y documentos con entonación natural.',
        'es-VE-PaolaNeural' => 'Hola, soy Paola de Venezuela. Convertiré tus lecturas en un audiolibro claro y fluido para estudiar.',
        'es-MX-DaliaNeural' => 'Hola, soy Dalia de México. Escucha tus artículos y temas favoritos con una narración profesional y nítida.',
        'es-MX-JorgeNeural' => 'Hola, soy Jorge de México. Transforma cualquier texto en audio para aprender y avanzar dondequiera que vayas.',
        'es-ES-AlvaroNeural' => 'Hola, soy Álvaro de España. Narraré tus documentos técnicos y obras con precisión y claridad castellana.',
        'es-ES-ElviraNeural' => 'Hola, soy Elvira de España. Convierte tus textos en una experiencia sonora envolvente y agradable.',
        'es-AR-TomasNeural' => 'Hola, soy Tomás de Argentina. Podés convertir tus lecturas y apuntes para escucharlos en cualquier momento.',
        'es-CO-GonzaloNeural' => 'Hola, soy Gonzalo de Colombia. Te acompañaré leyendo tus libros con un tono sereno y natural.',
    ];

    /**
     * Stream a quick audio preview of the chosen voice, speed rate, and pitch combination.
     */
    public function preview(Request $request, AudioSynthesisService $synthesisService): BinaryFileResponse
    {
        $voice = (string) $request->query('voice', 'es-VE-SebastianNeural');
        $speedRate = (string) $request->query('speed_rate', '+0%');
        $pitch = (string) $request->query('pitch', '+0Hz');

        $allowedRates = ['-20%', '-10%', '+0%', '+10%', '+20%', '+30%'];
        $allowedPitches = ['-20Hz', '-10Hz', '+0Hz', '+10Hz', '+20Hz'];

        if (! in_array($speedRate, $allowedRates, true)) {
            $speedRate = '+0%';
        }
        if (! in_array($pitch, $allowedPitches, true)) {
            $pitch = '+0Hz';
        }

        $availableVoices = collect(AudioSynthesisService::getAvailableVoices())->pluck('id')->all();
        if (! in_array($voice, $availableVoices, true)) {
            $voice = 'es-VE-SebastianNeural';
        }

        $previewDir = storage_path('app/public/voice_previews');
        if (! is_dir($previewDir)) {
            mkdir($previewDir, 0755, true);
        }

        // Standard default rate & pitch uses canonical single filename if available
        if ($speedRate === '+0%' && $pitch === '+0Hz') {
            $filePath = $previewDir.DIRECTORY_SEPARATOR.$voice.'.mp3';
        } else {
            $safeRate = str_replace(['+', '%', '-'], ['p', 'pct', 'm'], $speedRate);
            $safePitch = str_replace(['+', 'Hz', 'hz', '-'], ['p', 'hz', 'hz', 'm'], $pitch);
            $filePath = $previewDir.DIRECTORY_SEPARATOR."{$voice}_{$safeRate}_{$safePitch}.mp3";
        }

        if (! file_exists($filePath)) {
            $text = self::SAMPLE_PHRASES[$voice] ?? 'Hola, esta es una muestra de voz natural en MotaCastAudio.';
            $synthesisService->synthesize($text, $filePath, $voice, $speedRate, $pitch);
        }

        return response()->file($filePath, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=86400',
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
