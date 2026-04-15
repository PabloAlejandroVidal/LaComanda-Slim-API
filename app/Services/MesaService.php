<?php

namespace App\Services;

use App\DTO\Request\MesaRequest;
use App\Domain\Mesa\EstadoMesa;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Repositories\MesaRepository;

final class MesaService
{
    public function __construct(
        private MesaRepository $mesaRepo
    ) {}

    /*-------------------------------------------------
    | Crear mesa
    -------------------------------------------------*/
    public function crearMesa(MesaRequest $mesaRequest): array
    {
        if ($this->mesaRepo->exists($mesaRequest->id)) {
            throw new ConflictException('El id ya está en uso, no se puede crear la mesa');
        }

        $mesaId = $this->mesaRepo->add($mesaRequest->id);

        return [
            'id' => $mesaId,
            'estado' => EstadoMesa::CERRADA->value,
        ];
    }

    /*-------------------------------------------------
    | Cambiar estado manualmente
    -------------------------------------------------*/
    public function cambiarEstado(string $mesaId, EstadoMesa $nuevoEstado): void
    {
        $mesa = $this->mesaRepo->getMesa($mesaId);

        if (!$mesa) {
            throw new NotFoundException("Mesa {$mesaId} no encontrada");
        }

        $this->mesaRepo->setEstado($mesaId, $nuevoEstado);
    }
}