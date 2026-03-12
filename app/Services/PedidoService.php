<?php

namespace App\Services;

use App\Contracts\TransactionManager;
use App\Domain\Mesa\EstadoMesa;
use App\DTO\Request\PedidoRequest;
use App\Entities\TokenPayload;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\NotFoundException;
use App\Repositories\PedidoRepository;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\MesaRepository;
use App\Repositories\EmpleadoRepository;
use App\Services\Utils;
use PDO;


final class PedidoService
{
    public function __construct(
        private PedidoRepository        $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
        private MesaRepository          $mesaRepo,
        private EmpleadoRepository      $empleadoRepo,
        private PDO $pdo,
        private TransactionManager $tx
    ) {}

    // ===============================
    // CREAR PEDIDO
    // ===============================
    public function crearPedido(PedidoRequest $pedidoRequest, TokenPayload $token): array
    {
        $empleado = $this->empleadoRepo->getEmpleadoByEmail($token->email);
        if (!$empleado) {
            throw new NotFoundException("Empleado no encontrado");
        }

        $mesa = $this->mesaRepo->getMesa($pedidoRequest->mesa);
        if (!$mesa) {
            throw new NotFoundException("Mesa no encontrada");
        }

        if ($mesa['estado'] !== EstadoMesa::CERRADA) {
            throw new BusinessRuleException("La mesa no está disponible");
        }

        return $this->tx->transactional(function () use ($pedidoRequest, $empleado) {

            $codigo = Utils::generarCodigoAlfanumerico(5);
            $hora   = Utils::getHoraActual();

            $pedidoId = $this->pedidoRepo->crearPedido(
                $codigo,
                (int)$empleado->id,
                $pedidoRequest->mesa,
                $pedidoRequest->nombre,
                $hora
            );

            $this->mesaRepo->setEstado(
                $pedidoRequest->mesa,
                EstadoMesa::ESPERANDO_PEDIDO
            );

            if (!empty($pedidoRequest->detalles)) {
                $this->detalleRepo->insertarDetalles(
                    $pedidoId,
                    $pedidoRequest->detalles
                );
            }

            return [
                'id'   => $pedidoId,
                'mesa' => $pedidoRequest->mesa
            ];
        });
    }

    // ===============================
    // ASIGNAR PEDIDO A EMPLEADO
    // ===============================
    public function asignar(string $pedidoId, int $empleadoId): void
    {
        $empleado = $this->empleadoRepo->getEmpleadoById($empleadoId);
        if (!$empleado) {
            throw new NotFoundException("Empleado no encontrado");
        }

        $sectores = $this->empleadoRepo->getSectoresByEmpleado($empleadoId);
        if (empty($sectores)) {
            throw new BusinessRuleException("El empleado no tiene sectores asignados");
        }

        // Para el TP usamos el primero
        $sectorId = (int)$sectores[0]['id'];

        $hora = Utils::getHoraActual();

        $this->detalleRepo->declararPedidoDetallesAsignadoPorSector(
            $pedidoId,
            $empleadoId,
            $hora,
            $sectorId
        );
    }

    // ===============================
    // INICIAR PREPARACION
    // ===============================
    public function iniciarPreparacion(string $pedidoId, int $empleadoId): void
    {
        $sectores = $this->empleadoRepo->getSectoresByEmpleado($empleadoId);
        if (empty($sectores)) {
            throw new BusinessRuleException("El empleado no tiene sectores asignados");
        }

        $sectorId = (int)$sectores[0]['id'];
        $hora = Utils::getHoraActual();

        $this->detalleRepo->declararPedidoPreparadoPorSector(
            $pedidoId,
            $empleadoId,
            $hora,
            $sectorId
        );
    }

    // ===============================
    // MARCAR LISTO
    // ===============================
    public function marcarListo(string $pedidoId, int $empleadoId): void
    {
        $sectores = $this->empleadoRepo->getSectoresByEmpleado($empleadoId);
        if (empty($sectores)) {
            throw new BusinessRuleException("El empleado no tiene sectores asignados");
        }

        $sectorId = (int)$sectores[0]['id'];
        $hora = Utils::getHoraActual();

        $this->detalleRepo->declararPedidoPreparadoPorSector(
            $pedidoId,
            $empleadoId,
            $hora,
            $sectorId
        );
    }

    // ===============================
    // ENTREGAR PEDIDO
    // ===============================
    public function entregar(string $pedidoId, int $empleadoId): void
    {
        $sectores = $this->empleadoRepo->getSectoresByEmpleado($empleadoId);
        if (empty($sectores)) {
            throw new BusinessRuleException("El empleado no tiene sectores asignados");
        }

        $sectorId = (int)$sectores[0]['id'];
        $hora = Utils::getHoraActual();

        $this->detalleRepo->declararPedidoEntregadoPorSector(
            $pedidoId,
            $empleadoId,
            $hora,
            $sectorId
        );

        if ($this->detalleRepo->todosEntregados($pedidoId)) {
            $mesaId = $this->pedidoRepo->getMesaId($pedidoId);
            $this->mesaRepo->setEstado($mesaId, EstadoMesa::COMIENDO);
        }
    }

    // ===============================
    // CERRAR PEDIDO
    // ===============================
    public function cerrar(string $pedidoId, int $empleadoId): void
    {
        $this->tx->transactional(function () use ($pedidoId, $empleadoId) {

            if (!$this->detalleRepo->todosEntregados($pedidoId)) {
                throw new BusinessRuleException(
                    "No se puede cerrar un pedido con productos pendientes"
                );
            }

            $importe = $this->pedidoRepo->getMonto($pedidoId);
            $hora    = Utils::getHoraActual();

            $this->pedidoRepo->cerrarPedido(
                $pedidoId,
                $empleadoId,
                $hora,
                $importe
            );

            $mesaId = $this->pedidoRepo->getMesaId($pedidoId);

            $this->mesaRepo->setEstado(
                $mesaId,
                EstadoMesa::CERRADA
            );
        });
    }

}