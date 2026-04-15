<?php

use App\Domain\Operacion\AmbitoOperacion;
use App\Domain\Operacion\TipoOperacion;
final class EmpleadoOperacion
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $empleadoId,
        public readonly TipoOperacion $tipoOperacion,
        public readonly AmbitoOperacion $ambito,
        public readonly ?int $sectorId,
        public readonly ?int $pedidoId,
        public readonly ?string $mesaId,
        public readonly string $fechaHora,
    ) {}
}