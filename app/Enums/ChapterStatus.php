<?php

namespace App\Enums;

enum ChapterStatus: string
{
    case Pending = 'pending';
    case Synthesizing = 'synthesizing';
    case Ready = 'ready';
    case Failed = 'failed';

    /**
     * Retorna la etiqueta legible en español para la interfaz.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Synthesizing => 'Sintetizando voz',
            self::Ready => 'Listo',
            self::Failed => 'Fallo',
        };
    }

    /**
     * Retorna todas las claves de estado como un array de strings.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
