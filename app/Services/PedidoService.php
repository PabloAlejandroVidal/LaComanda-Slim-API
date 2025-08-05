<?php
namespace App\Services;

use App\Repositories\EmpleadoRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PermisoRepository;
use App\Services\AuthorizationService;
use App\Services\Utils;
use App\DTO\DetalleDTO;

class EstadoMesa {
    const CERRADA = 1;
    const ESPERANDO_PEDIDO = 2;
    const COMIENDO = 3;
    const PAGANDO = 4;
}

class PedidoService
{
    public function __construct(
        private PedidoRepository        $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
        private MesaRepository          $mesaRepo,
        private AuthorizationService    $authorizationService,
        private PermisoRepository       $permisoService,
        private EmpleadoRepository      $empleadoRepo,
    ) {}

    public function crearPedido(
        int    $mesaId,
        string    $mozoEmail,
        string $nombreCliente,
        array  $detalles
    ): string {

        $empleado = $this->empleadoRepo->getEmpleadoByEmail($mozoEmail);
        if (!$empleado) {
            throw new \DomainException("Empleado no encontrado");
        }

        if (!$this->mesaRepo->exists($mesaId)) {
            throw new \DomainException("Mesa inexistente");
        }

        $mesa = $this->mesaRepo->getMesa($mesaId);        
        if ($mesa['estado_id'] !== EstadoMesa::CERRADA) {
            throw new \DomainException("La mesa $mesaId no está disponible: {$mesa['descripcion']}");
        }

        $pedidoId = Utils::generarCodigoAlfanumerico(5);
        $hora = Utils::getHoraActual();
 
        $this->pedidoRepo->crearPedido($pedidoId, $empleado['id'],$mesaId, $nombreCliente, $hora);
        $this->mesaRepo->setEstado($mesaId, EstadoMesa::ESPERANDO_PEDIDO);
        $this->agregarDetalles($mesaId, $pedidoId, $detalles);
        return $pedidoId;
    }

    public function procesarPedido($pedidoId, $mesaId, $empleadoId, $sector, $accion) {
        switch($accion){
            case 'asignar':
                $this->asignarLote($pedidoId, $mesaId, $empleadoId, $sector);
                break;
            case 'preparar':
                $this->prepararLote($pedidoId, $mesaId, $empleadoId, $sector);
                break;
            case 'entregar':
                $this->entregarLote($pedidoId, $mesaId, $empleadoId, $sector);
                break;
        }
    }

    public function agregarDetalles(string $mesaId, int $pedidoId, array $detalles) {
        $mesa = $this->mesaRepo->getMesa($mesaId);
        if (in_array($mesa['estado_id'], [EstadoMesa::CERRADA])) {
            throw new \DomainException(message: "No se puede agregar más pedidos: {$mesa['descripcion']}");
        }
        $this->mesaRepo->setEstado($mesaId, EstadoMesa::ESPERANDO_PEDIDO);
        return $this->detalleRepo->insertarDetalles($pedidoId, $detalles);
    }

    public function asignarLote(int $pedidoId, string $mesaId, int $empleadoId, int $sectorId): void {
        $mesa = $this->mesaRepo->getMesa($mesaId);
        if (in_array($mesa['estado_id'], [EstadoMesa::CERRADA])) {
            throw new \DomainException(message: "No se pueden asignar pedidos: {$mesa['descripcion']}");
        }
        $hora = Utils::getHoraActual();
        $this->detalleRepo->declararPedidoDetallesAsignadoPorSector($pedidoId, $empleadoId, $hora, $sectorId);
    }

    public function prepararLote(int $pedidoId, string $mesaId, int $empleadoId, int $sectorId): void {
        $mesa = $this->mesaRepo->getMesa($mesaId);
        if (in_array($mesa['estado_id'], [EstadoMesa::CERRADA])) {
            throw new \DomainException(message: "No se pueden preparar pedidos: {$mesa['descripcion']}");
        }
        $hora = Utils::getHoraActual();
        $this->detalleRepo->declararPedidoPreparadoPorSector($pedidoId, $empleadoId, $hora, $sectorId);
    }
    public function entregarLote(int $pedidoId, string $mesaId, int $empleadoId, int $sectorId): void {
        $mesa = $this->mesaRepo->getMesa($mesaId);
        if (in_array($mesa['estado_id'], [EstadoMesa::CERRADA])) {
            throw new \DomainException(message: "No se pueden entregar pedidos: {$mesa['descripcion']}");
        }
        $hora = Utils::getHoraActual();
        $this->detalleRepo->declararPedidoEntregadoPorSector($pedidoId, $empleadoId, $hora, $sectorId);

        if ($this->detalleRepo->todosEntregados($pedidoId)) {
            $mesaId = $this->pedidoRepo->getMesaId($pedidoId);
            $this->mesaRepo->setEstado($mesaId, EstadoMesa::COMIENDO);
        }
    }

    public function solicitarCuenta(int $pedidoId): void {
        $mesaId = $this->pedidoRepo->getMesaId($pedidoId);
        $this->mesaRepo->setEstado($mesaId, EstadoMesa::PAGANDO);
    }

    public function cerrarComanda(int $pedidoId, int $empleadoId): void {
        $hora = Utils::getHoraActual();
        $importe = $this->pedidoRepo->getMonto($pedidoId);
        $this->pedidoRepo->cerrarPedido($pedidoId, $empleadoId,$hora, $importe);

        $mesaId = $this->pedidoRepo->getMesaId($pedidoId);
        $this->mesaRepo->setEstado($mesaId, EstadoMesa::CERRADA);
    }

    public function validarDetalles($pedidos): bool {
        if (!is_array($pedidos)) {
            return false;
        }

        foreach ($pedidos as $pedido) {
            if (!(isset($pedido['id'], $pedido['cantidad']))) {
                return false;
            }
        }

        return true;
    }

    public function transformDetallesToDTO($detallesPedidos): array {
        $detallesDTO = [];
        foreach ($detallesPedidos as $detalle) {
            $detallesDTO[] = new DetalleDTO(
                $detalle->id,
                $detalle->producto->nombre,
                $detalle->cantidad,
                $detalle->estado
            );
        }
        return $detallesDTO;
    }
}
