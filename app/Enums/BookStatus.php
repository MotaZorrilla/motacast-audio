<?php

namespace App\Enums;

enum BookStatus: string
{
    case Pending = 'pending';
    case Extracting = 'extracting';
    case Synthesizing = 'synthesizing';
    case Ready = 'ready';
    case Failed = 'failed';

    /**
     * Retorna la etiqueta legible en español para la interfaz.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente de procesamiento',
            self::Extracting => 'Extrayendo texto...',
            self::Synthesizing => 'Generando audio...',
            self::Ready => 'Listo para escuchar',
            self::Failed => 'Inconveniente en procesamiento',
        };
    }

    /**
     * Determina si el audiolibro está actualmente en fases activas de procesamiento.
     */
    public function isProcessing(): bool
    {
        return match ($this) {
            self::Pending, self::Extracting, self::Synthesizing => true,
            self::Ready, self::Failed => false,
        };
    }

    /**
     * Determina si el audiolibro ha alcanzado un estado final.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Ready, self::Failed => true,
            default => false,
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
