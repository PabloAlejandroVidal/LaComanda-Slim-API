<?php

namespace App\Domain\Sector;

final class Sector
{
    public function __construct(
        public int $id,
        public string $nombre,
        public string $descripcion,
        public SectorNombre $clave,
    ) {}
}