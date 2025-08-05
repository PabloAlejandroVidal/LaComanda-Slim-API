<?php
namespace App\Services;

use App\DTO\EmpleadoInput;
use App\DTO\EmpleadoRequest;
use App\Enums\EmpleadoType;
use App\Exceptions\EmpleadoNoCreadoException;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PermisoRepository;
use App\Repositories\TipoEmpleadoRepository;
use App\Services\AuthorizationService;
use App\Services\Utils;
use App\DTO\DetalleDTO;


class InformeService
{
    public function __construct(
        private PedidoRepository        $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
        private MesaRepository          $mesaRepo,
        private PermisoRepository       $permisoService,
        private EmpleadoRepository      $empleadoRepo,
        private TipoEmpleadoRepository  $tipoEmpleadoRepository,
    ) {}

    public function ingresosDeEmpleados(): array{
        return [];
    }

    public function obtenerEmpleados(): array {
        return $this->obtenerEmpleados();
    }
}
