<?php
namespace App\DTO\Response;

final class PedidoResponseDTO
{
    public function __construct(
        public string $id,
        public string $mesaId,
        public string $nombreCliente,
        public string $estado
    ) {}
}