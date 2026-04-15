<?php

namespace App\DTO\Response;

use App\Domain\Operacion\AmbitoOperacion;
use App\Domain\Operacion\TipoOperacion;

final class RegistrarOperacionDTO
{
    public function __construct(
        public readonly int $empleadoId,
        public readonly TipoOperacion $tipoOperacion,
        public readonly AmbitoOperacion $ambito,
        public readonly ?int $sectorId = null,
        public readonly ?string $pedidoId = null,
        public readonly ?string $mesaId = null,
        public readonly ?string $observaciones = null,
        public readonly ?string $fechaHora = null,
    ) {}

    public static function create(
        int $empleadoId,
        TipoOperacion $tipoOperacion,
        AmbitoOperacion $ambito,
        ?int $sectorId = null,
        ?string $pedidoId = null,
        ?string $mesaId = null,
        ?string $observaciones = null,
        ?string $fechaHora = null,
    ): self {
        return new self(
            empleadoId: $empleadoId,
            tipoOperacion: $tipoOperacion,
            ambito: $ambito,
            sectorId: $sectorId,
            pedidoId: $pedidoId,
            mesaId: $mesaId,
            observaciones: $observaciones,
            fechaHora: $fechaHora,
        );
    }

    public function toArray(): array
    {
        return [
            'empleado_id'     => $this->empleadoId,
            'tipo_operacion'  => $this->tipoOperacion->value,
            'ambito'          => $this->ambito->value,
            'sector_id'       => $this->sectorId,
            'pedido_id'       => $this->pedidoId,
            'detalle_id'      => $this->detalleId,
            'mesa_id'         => $this->mesaId,
            'observaciones'   => $this->observaciones,
            'fecha_hora'      => $this->fechaHora,
        ];
    }
}