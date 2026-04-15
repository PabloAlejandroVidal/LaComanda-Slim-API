<?php

namespace App\Services;

use App\Contracts\TransactionManager;
use App\Domain\Mesa\EstadoMesa;
use App\Domain\Operacion\TipoOperacion;
use App\DTO\Request\CancelarPedidoRequest;
use App\DTO\Request\DetalleRequest;
use App\DTO\Request\PedidoRequest;
use App\DTO\Response\RegistrarOperacionDTO;
use App\Entities\Empleado;
use App\Entities\TokenPayload;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\EmpleadoOperacionRepository;
use App\Repositories\EmpleadoRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PedidoRepository;

final class PedidoService
{
    public function __construct(
        private PedidoRepository $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
        private MesaRepository $mesaRepo,
        private EmpleadoRepository $empleadoRepo,
        private EmpleadoOperacionRepository $empleadoOperacionRepo,
        private TransactionManager $tx
    ) {}

    public function crearPedido(PedidoRequest $pedidoRequest, TokenPayload $token): array
    {
        $empleado = $this->getEmpleadoByEmailOrFail($token->email);
        $mesa = $this->getMesaOrFail($pedidoRequest->mesa);

        if ($mesa['estado'] !== EstadoMesa::CERRADA) {
            throw new BusinessRuleException('La mesa no está disponible');
        }

        return $this->tx->transactional(function () use ($pedidoRequest, $empleado) {
            $codigo = Utils::generarCodigoAlfanumerico(5);
            $hora = Utils::getHoraActual();

            $pedidoId = $this->pedidoRepo->crearPedido(
                $codigo,
                (int) $empleado->id,
                $pedidoRequest->mesa,
                $pedidoRequest->nombre,
                $hora
            );

            $this->mesaRepo->setEstado(
                $pedidoRequest->mesa,
                EstadoMesa::ESPERANDO_PEDIDO
            );

            if (!empty($pedidoRequest->detalles)) {
                $rows = $this->mapDetallesToRows($pedidoRequest->detalles);
                $this->detalleRepo->insertarDetalles($pedidoId, $rows);
            }

            $this->registrarOperacion(
                empleadoId: (int) $empleado->id,
                tipoOperacion: TipoOperacion::TOMA_PEDIDO,
                pedidoId: $pedidoId,
                mesaId: $pedidoRequest->mesa,
                observaciones: 'Alta inicial del pedido',
                fechaHora: $hora
            );

            return [
                'id' => $pedidoId,
                'mesa' => $pedidoRequest->mesa,
            ];
        });
    }

    public function iniciarPreparacion(
        string $pedidoId,
        int $empleadoId,
        int $sectorId,
        int $tiempoEstimadoMinutos
    ): void {
        $pedido = $this->getPedidoOrFail($pedidoId);
        $this->validarSectorDelEmpleadoOrFail($empleadoId, $sectorId);

        $this->assertPedidoOperableEnSectores($pedido, 'iniciar preparación');

        $this->tx->transactional(function () use ($pedidoId, $empleadoId, $sectorId, $tiempoEstimadoMinutos) {
            $hora = Utils::getHoraActual();

            $updated = $this->detalleRepo->declararPedidoEnPreparacionPorSector(
                $pedidoId,
                $empleadoId,
                $hora,
                $sectorId,
                $tiempoEstimadoMinutos
            );

            if ($updated === 0) {
                throw new BusinessRuleException(
                    'No hay detalles pendientes para iniciar preparación en ese sector'
                );
            }

            $this->registrarOperacion(
                empleadoId: $empleadoId,
                tipoOperacion: TipoOperacion::ASIGNACION_DETALLE,
                sectorId: $sectorId,
                pedidoId: $pedidoId,
                observaciones: "Inicio de preparación del sector. Tiempo estimado: {$tiempoEstimadoMinutos} minutos.",
                fechaHora: $hora
            );
        });
    }

    public function marcarListo(string $pedidoId, int $empleadoId, int $sectorId): void
    {
        $pedido = $this->getPedidoOrFail($pedidoId);
        $this->validarSectorDelEmpleadoOrFail($empleadoId, $sectorId);

        $this->assertPedidoOperableEnSectores($pedido, 'marcar como listo');

        $this->tx->transactional(function () use ($pedidoId, $empleadoId, $sectorId) {
            $hora = Utils::getHoraActual();

            $updated = $this->detalleRepo->declararPedidoListoPorSector(
                $pedidoId,
                $empleadoId,
                $hora,
                $sectorId
            );

            if ($updated === 0) {
                throw new BusinessRuleException(
                    'No hay detalles en preparación para marcar como listos en ese sector'
                );
            }

            $this->registrarOperacion(
                empleadoId: $empleadoId,
                tipoOperacion: TipoOperacion::PREPARACION_DETALLE,
                sectorId: $sectorId,
                pedidoId: $pedidoId,
                observaciones: 'Sector marcado como listo',
                fechaHora: $hora
            );
        });
    }

    public function entregar(string $pedidoId, int $empleadoId): void
    {
        $pedido = $this->getPedidoOrFail($pedidoId);
        $this->getEmpleadoByIdOrFail($empleadoId);

        $this->assertPedidoNoCancelado($pedido, 'entregar');
        $this->assertPedidoNoCerrado($pedido, 'entregar');

        if (($pedido['hora_pago'] ?? null) !== null) {
            throw new BusinessRuleException(
                'No se puede entregar un pedido que ya fue cobrado'
            );
        }

        $this->tx->transactional(function () use ($pedidoId, $pedido, $empleadoId) {
            if (!$this->detalleRepo->todosListosParaEntregar($pedidoId)) {
                throw new BusinessRuleException(
                    'No se puede entregar el pedido hasta que todos los productos estén listos'
                );
            }

            if (($pedido['hora_entrega'] ?? null) !== null) {
                throw new BusinessRuleException(
                    'El pedido ya fue entregado'
                );
            }

            $hora = Utils::getHoraActual();

            $this->pedidoRepo->registrarEntrega(
                $pedidoId,
                $empleadoId,
                $hora
            );

            $this->mesaRepo->setEstado(
                $pedido['mesa_id'],
                EstadoMesa::COMIENDO
            );

            $this->registrarOperacion(
                empleadoId: $empleadoId,
                tipoOperacion: TipoOperacion::ENTREGA_PEDIDO,
                pedidoId: $pedidoId,
                mesaId: $pedido['mesa_id'],
                observaciones: 'Entrega completa del pedido',
                fechaHora: $hora
            );
        });
    }

    public function cobrarMesa(string $pedidoId, TokenPayload $token): array
    {
        $empleado = $this->getEmpleadoByEmailOrFail($token->email);
        $pedido = $this->getPedidoOrFail($pedidoId);
        $mesa = $this->getMesaOrFail($pedido['mesa_id']);

        $this->assertPedidoNoCancelado($pedido, 'cobrar');
        $this->assertPedidoNoCerrado($pedido, 'cobrar');

        if (($pedido['hora_entrega'] ?? null) === null) {
            throw new BusinessRuleException(
                'No se puede cobrar un pedido que todavía no fue entregado'
            );
        }

        if (($pedido['hora_pago'] ?? null) !== null) {
            throw new BusinessRuleException('El pedido ya fue cobrado');
        }

        if ($mesa['estado'] !== EstadoMesa::COMIENDO) {
            throw new BusinessRuleException(
                'Solo se puede cobrar una mesa que esté en estado comiendo'
            );
        }

        $importe = $this->pedidoRepo->getMonto($pedidoId);
        $horaPago = Utils::getHoraActual();

        return $this->tx->transactional(function () use ($pedidoId, $pedido, $empleado, $horaPago, $importe) {
            $this->pedidoRepo->registrarCobro(
                $pedidoId,
                (int) $empleado->id,
                $horaPago,
                $importe
            );

            $this->mesaRepo->setEstado(
                $pedido['mesa_id'],
                EstadoMesa::PAGANDO
            );

            $this->registrarOperacion(
                empleadoId: (int) $empleado->id,
                tipoOperacion: TipoOperacion::COBRO_MESA,
                pedidoId: $pedidoId,
                mesaId: $pedido['mesa_id'],
                observaciones: 'Cobro registrado y mesa en estado pagando',
                fechaHora: $horaPago
            );

            return [
                'pedido_id' => $pedidoId,
                'mesa_id' => $pedido['mesa_id'],
                'hora_pago' => $horaPago,
                'importe' => $importe,
                'estado_mesa' => EstadoMesa::PAGANDO->value,
            ];
        });
    }

    public function cerrar(string $pedidoId, int $empleadoId): array
    {
        $pedido = $this->getPedidoOrFail($pedidoId);
        $this->getEmpleadoByIdOrFail($empleadoId);
        $mesa = $this->getMesaOrFail($pedido['mesa_id']);

        $this->assertPedidoNoCancelado($pedido, 'cerrar');

        if (($pedido['hora_pago'] ?? null) === null) {
            throw new BusinessRuleException(
                'No se puede cerrar una mesa sin registrar el cobro'
            );
        }

        if (($pedido['hora_cierre'] ?? null) !== null) {
            throw new BusinessRuleException(
                'La mesa ya fue cerrada'
            );
        }

        if ($mesa['estado'] !== EstadoMesa::PAGANDO) {
            throw new BusinessRuleException(
                'Solo se puede cerrar una mesa que esté en estado pagando'
            );
        }

        $horaCierre = Utils::getHoraActual();

        return $this->tx->transactional(function () use ($pedidoId, $pedido, $empleadoId, $horaCierre) {
            $this->pedidoRepo->cerrarPedido(
                $pedidoId,
                $empleadoId,
                $horaCierre
            );

            $this->mesaRepo->setEstado(
                $pedido['mesa_id'],
                EstadoMesa::CERRADA
            );

            $this->registrarOperacion(
                empleadoId: $empleadoId,
                tipoOperacion: TipoOperacion::CIERRE_MESA,
                pedidoId: $pedidoId,
                mesaId: $pedido['mesa_id'],
                observaciones: 'Cierre final de mesa',
                fechaHora: $horaCierre
            );

            return [
                'pedido_id' => $pedidoId,
                'mesa_id' => $pedido['mesa_id'],
                'hora_cierre' => $horaCierre,
                'estado_mesa' => EstadoMesa::CERRADA->value,
            ];
        });
    }

    public function cancelar(string $pedidoId, CancelarPedidoRequest $request, TokenPayload $token): array
    {    
        $empleado = $this->getEmpleadoByEmailOrFail($token->email);
        $pedido = $this->getPedidoOrFail($pedidoId);

        if (($pedido['hora_cancelacion'] ?? null) !== null) {
            throw new BusinessRuleException(
                'El pedido ya fue cancelado'
            );
        }

        if (($pedido['hora_entrega'] ?? null) !== null) {
            throw new BusinessRuleException(
                'No se puede cancelar un pedido que ya fue entregado'
            );
        }

        if (($pedido['hora_pago'] ?? null) !== null) {
            throw new BusinessRuleException(
                'No se puede cancelar un pedido que ya fue cobrado'
            );
        }

        if (($pedido['hora_cierre'] ?? null) !== null) {
            throw new BusinessRuleException(
                'No se puede cancelar un pedido que ya fue cerrado'
            );
        }

        $horaCancelacion = Utils::getHoraActual();
        $motivoCancelacion = $request->motivoCancelacion;

        return $this->tx->transactional(function () use ($pedidoId, $pedido, $empleado, $horaCancelacion, $motivoCancelacion) {
            $this->pedidoRepo->cancelarPedido(
                $pedidoId,
                (int) $empleado->id,
                $horaCancelacion,
                $motivoCancelacion
            );

            $this->mesaRepo->setEstado(
                $pedido['mesa_id'],
                EstadoMesa::CERRADA
            );

            $this->registrarOperacion(
                empleadoId: (int) $empleado->id,
                tipoOperacion: TipoOperacion::CANCELACION_PEDIDO,
                pedidoId: $pedidoId,
                mesaId: $pedido['mesa_id'],
                observaciones: $motivoCancelacion ?? 'Pedido cancelado',
                fechaHora: $horaCancelacion
            );

            return [
                'pedido_id' => $pedidoId,
                'mesa_id' => $pedido['mesa_id'],
                'hora_cancelacion' => $horaCancelacion,
                'motivo_cancelacion' => $motivoCancelacion,
                'estado_mesa' => EstadoMesa::CERRADA->value,
            ];
        });
    }

    /**
     * @param DetalleRequest[] $detalles
     */
    private function mapDetallesToRows(array $detalles): array
    {
        $rows = [];

        foreach ($detalles as $detalle) {
            $rows[] = [
                'producto_id' => $detalle->id,
                'cantidad' => $detalle->cantidad,
            ];
        }

        return $rows;
    }

    private function registrarOperacion(
        int $empleadoId,
        TipoOperacion $tipoOperacion,
        ?int $sectorId = null,
        ?string $pedidoId = null,
        ?string $mesaId = null,
        ?string $observaciones = null,
        ?string $fechaHora = null,
    ): int {
        $dto = RegistrarOperacionDTO::create(
            empleadoId: $empleadoId,
            tipoOperacion: $tipoOperacion,
            ambito: $tipoOperacion->ambito(),
            sectorId: $sectorId,
            pedidoId: $pedidoId,
            mesaId: $mesaId,
            observaciones: $observaciones,
            fechaHora: $fechaHora ?? Utils::getHoraActual(),
        );

        return $this->empleadoOperacionRepo->registrar($dto);
    }

    private function assertPedidoNoCancelado(array $pedido, string $accion): void
    {
        if (($pedido['hora_cancelacion'] ?? null) !== null) {
            throw new BusinessRuleException(
                "No se puede {$accion} un pedido cancelado"
            );
        }
    }

    private function assertPedidoNoCerrado(array $pedido, string $accion): void
    {
        if (($pedido['hora_cierre'] ?? null) !== null) {
            throw new BusinessRuleException(
                "No se puede {$accion} un pedido cerrado"
            );
        }
    }

    private function assertPedidoOperableEnSectores(array $pedido, string $accion): void
    {
        $this->assertPedidoNoCancelado($pedido, $accion);
        $this->assertPedidoNoCerrado($pedido, $accion);

        if (($pedido['hora_entrega'] ?? null) !== null) {
            throw new BusinessRuleException(
                "No se puede {$accion} un pedido ya entregado"
            );
        }

        if (($pedido['hora_pago'] ?? null) !== null) {
            throw new BusinessRuleException(
                "No se puede {$accion} un pedido ya cobrado"
            );
        }
    }

    private function getEmpleadoByEmailOrFail(string $email): Empleado
    {
        $empleado = $this->empleadoRepo->getEmpleadoByEmail($email);

        if (!$empleado) {
            throw new NotFoundException('Empleado no encontrado');
        }

        return $empleado;
    }

    private function getEmpleadoByIdOrFail(int $empleadoId): Empleado
    {
        $empleado = $this->empleadoRepo->getEmpleadoById($empleadoId);

        if (!$empleado) {
            throw new NotFoundException('Empleado no encontrado');
        }

        return $empleado;
    }

    private function validarSectorDelEmpleadoOrFail(int $empleadoId, int $sectorId): void
    {
        $this->getEmpleadoByIdOrFail($empleadoId);

        $sectores = $this->empleadoRepo->getSectoresByEmpleado($empleadoId);

        if (empty($sectores)) {
            throw new ForbiddenException('El empleado no tiene sectores asignados');
        }

        $sectorIds = array_map(
            fn(array $sector) => (int) $sector['id'],
            $sectores
        );

        if (!in_array($sectorId, $sectorIds, true)) {
            throw new ForbiddenException(
                'El empleado no puede operar sobre el sector indicado'
            );
        }
    }

    private function getMesaOrFail(string $mesaId): array
    {
        $mesa = $this->mesaRepo->getMesa($mesaId);

        if (!$mesa) {
            throw new NotFoundException('Mesa no encontrada');
        }

        return $mesa;
    }

    private function getPedidoOrFail(string $pedidoId): array
    {
        $pedido = $this->pedidoRepo->getPedidoById($pedidoId);

        if (!$pedido) {
            throw new NotFoundException('Pedido no encontrado');
        }

        return $pedido;
    }
}