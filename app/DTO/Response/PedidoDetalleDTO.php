<?php
namespace App\DTO\Response;

final class PedidoDetalleDTO
{
    public function __construct(
        public int $pedido,
        public array $detalles,
    ) {}
}