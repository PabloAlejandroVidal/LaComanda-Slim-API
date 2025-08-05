<?php
namespace App\DTO;

class DetalleDTO
{
    public function __construct(
        public int $id,
        public string $nombreProducto,
        public int $cantidad,
        public string $estado,
    ) {}
}

?>
