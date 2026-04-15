<?php

namespace App\DTO\Response;

final class PedidoSeguimientoDTO
{
    public function __construct(
        public string $id,
        public string $mesaId,
        public string $estadoOperativo,
        public bool $todosLosDetallesAsignados,
        public ?int $minutosRestantes,
        public ?string $horaEstimadaFinalizacion,
        public string $mensaje
    ) {}
}