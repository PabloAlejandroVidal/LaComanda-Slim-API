<?php
namespace App\DTO\Response;

class ProductoDTO
{
    public function __construct(
        public string $nombre,
        public string $sector,
        public int $precio,
    ) {}
}

?>
