<?php
namespace App\DTO\Response;

final class PedidoDetalleAgrupadoDTO
{
    public function __construct(
        public string $pedidoId,
        public ?string $mesaId,
        public array $productosPorSector
    ) {}
}