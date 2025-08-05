<?php
namespace App\Services;

use App\DTO\EmpleadoInput;
use App\DTO\EmpleadoRequest;
use App\Enums\EmpleadoType;
use App\Exceptions\ResourceNotCreatedException;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PermisoRepository;
use App\Repositories\TipoEmpleadoRepository;
use App\Services\AuthorizationService;
use App\Services\Utils;
use App\DTO\DetalleDTO;


class EmpleadoService
{
    public function __construct(
        private PedidoRepository        $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
        private MesaRepository          $mesaRepo,
        private PermisoRepository       $permisoService,
        private EmpleadoRepository      $empleadoRepo,
        private TipoEmpleadoRepository  $tipoEmpleadoRepository,
    ) {}

public function crearEmpleado(EmpleadoRequest $empleadoRequest): array
{
    if ($this->empleadoRepo->emailExists($empleadoRequest->email)) {
        throw new ResourceNotCreatedException("No se pudo crear el empleado - Email inválido: $empleadoRequest->email");
    }

    $tipo = $this->tipoEmpleadoRepository->getTipoByString($empleadoRequest->tipo);
    if (!$tipo) {
        throw new ResourceNotCreatedException("No se pudo crear el empleado - Tipo de empleado inválido: $empleadoRequest->tipo");
    }

    $empleadoId = $this->empleadoRepo->registrarEmpleado(
        $empleadoRequest->nombre,
        $empleadoRequest->email,
        $empleadoRequest->clave,
        $tipo->tipo
    );

    return [
        'id' => $empleadoId,
        'nombre' => $empleadoRequest->nombre,
        'email' => $empleadoRequest->email,
        'tipo' => $tipo->tipo
    ];
}

    public function obtenerEmpleados(): array {
        return $this->empleadoRepo->getEmpleados();
    }
}
