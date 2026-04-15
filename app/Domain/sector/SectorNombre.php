<?php

namespace App\Domain\Sector;

enum SectorNombre: string
{
    case ENTRADA = 'entrada';
    case PATIO_TRASERO = 'patio_trasero';
    case COCINA = 'cocina';
    case CANDY_BAR = 'candy_bar';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}