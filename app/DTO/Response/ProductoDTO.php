<?php
namespace App\DTO;

class ProductoDTO
{
    public function __construct(
        public string $nombre,
        public string $sector,
        public int $precio,
    ) {}
}

?>
