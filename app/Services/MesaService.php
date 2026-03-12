<?php
namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Repositories\MesaRepository;
use App\DTO\Request\MesaRequest;
use App\DTO\Response\MesaDTO;
use App\Domain\Mesa\EstadoMesa;

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
            throw new ConflictException("El id ya está en uso, no se puede crear la mesa");
        }

        $mesaId = $this->mesaRepo->add($mesaRequest->id);

        return [
            'id'     => $mesaId,
            'estado' => EstadoMesa::CERRADA->value
        ];
    }

    /*-------------------------------------------------
    | Listar mesas agrupadas por estado
    -------------------------------------------------*/
    public function getMesas(): array
    {
        $mesas = $this->mesaRepo->getMesas();

        $mesasLibres   = [];
        $mesasOcupadas = [];

        foreach ($mesas as $mesa) {

            // ✅ Ahora correctamente comparando enum
            $esLibre = $mesa['estado'] === EstadoMesa::CERRADA;

            $dto = new MesaDTO(
                id: $mesa['id'],
                libre: $esLibre
            );

            if ($esLibre) {
                $mesasLibres[] = $dto;
            } else {
                $mesasOcupadas[] = $dto;
            }
        }

        $output = [];

        if (!empty($mesasLibres)) {
            $output[] = [
                "detalle" => "Mesas Libres",
                "mesas"   => $mesasLibres
            ];
        }

        if (!empty($mesasOcupadas)) {
            $output[] = [
                "detalle" => "Mesas Ocupadas",
                "mesas"   => $mesasOcupadas
            ];
        }

        return $output;
    }

    /*-------------------------------------------------
    | Cambiar estado manualmente
    | (por ejemplo: mozo o socio)
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