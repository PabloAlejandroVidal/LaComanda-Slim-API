<?php
namespace App\DTO\Response;

final class DetalleDTO
{
    public function __construct(
        public int $productoId,
        public string $productoNombre,
        public int $cantidad,
        public string $estado,
    ) {}
}