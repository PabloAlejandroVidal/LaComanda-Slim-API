<?php

namespace App\Services;

use App\Domain\Pedido\EstadoDetalle;
use App\DTO\Response\PedidoSeguimientoDTO;
use App\Entities\TokenPayload;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PedidoFotoRepository;
use App\Repositories\PedidoRepository;

final class PedidoQueryService
{
    public function __construct(
        private PedidoRepository $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
        private EmpleadoRepository $empleadoRepo,
        private PedidoFotoRepository $pedidoFotoRepo,
        private DetallePedidoRepository $detallePedidoRepo
    ) {}

    public function obtenerPorId(string $pedidoId): array
    {
        $pedido = $this->pedidoRepo->getPedidoById($pedidoId);
        
        if (!$pedido) {
            throw new NotFoundException('Pedido no encontrado');
        }
            
        $foto = $this->pedidoFotoRepo->findByPedidoId($pedidoId);
        $pedido['foto_url'] = $foto['ruta_archivo'] ?? null;

        return [
            'pedido' => $pedido,
        ];
    }

    public function listar(array $filtros = []): array
    {
        return $this->pedidoRepo->listar($filtros);
    }

    public function detallesAgrupadosPorSector(string $pedidoId): array
    {
        $pedido = $this->pedidoRepo->getPedidoById($pedidoId);

        if (!$pedido) {
            throw new NotFoundException('Pedido no encontrado');
        }
        
        $foto = $this->pedidoFotoRepo->findByPedidoId($pedidoId);
        $detalles = $this->detalleRepo->getDetallesDelPedido($pedidoId);

        return [
            'pedido' => [
                'id' => $pedido['id'],
                'mesa_id' => $pedido['mesa_id'],
                'estado_operativo' => $pedido['estado_operativo'] ?? null,
                'nombre_cliente' => $pedido['nombre_cliente'],
                'hora_inicio' => $pedido['hora_inicio'] ?? null,
                'hora_entrega' => $pedido['hora_entrega'] ?? null,
                'hora_pago' => $pedido['hora_pago'] ?? null,
                'hora_cierre' => $pedido['hora_cierre'] ?? null,
                'hora_cancelacion' => $pedido['hora_cancelacion'] ?? null,
                'motivo_cancelacion' => $pedido['motivo_cancelacion'] ?? null,
                'importe' => $pedido['importe'] ?? null,
                'foto_url' => $foto['ruta_archivo'] ?? null,
            ],
            'sectores' => $this->agruparDetallesPorSector($detalles),
        ];
    }

    public function obtenerPendientesDelSector(TokenPayload $token): array
    {
        return $this->obtenerDetallesDeMisSectoresPorEstado(
            $token,
            EstadoDetalle::PENDIENTE
        );
    }

    public function obtenerEnPreparacionDelSector(TokenPayload $token): array
    {
        return $this->obtenerDetallesDeMisSectoresPorEstado(
            $token,
            EstadoDetalle::EN_PREPARACION
        );
    }

    public function masVendidos(string $from, string $to): array
    {
        return $this->pedidoRepo->getMasVendido($from, $to) ?? [];
    }

    public function menosVendidos(string $from, string $to): array
    {
        return $this->pedidoRepo->getMenosVendido($from, $to) ?? [];
    }

    public function noEntregadosEnTiempo(string $from, string $to): array
    {
        return $this->pedidoRepo->getPedidosFueraDeTiempo($from, $to);
    }

    public function cancelados(string $from, string $to): array
    {
        return $this->pedidoRepo->getCancelados($from, $to);
    }

    public function cerrados(string $from, string $to): array
    {
        return $this->pedidoRepo->getCerrados($from, $to) ?? [];
    }

    private function obtenerDetallesDeMisSectoresPorEstado(
        TokenPayload $token,
        EstadoDetalle $estado
    ): array {
        $sectores = $this->resolverSectoresDesdeToken($token);

        $rows = [];

        foreach ($sectores as $sector) {
            $sectorId = (int) $sector['id'];

            $sectorRows = $this->detalleRepo->getPedidosDetalles(
                $sectorId,
                $estado
            );

            foreach ($sectorRows as $row) {
                $rows[] = $row;
            }
        }

        return $this->agruparDetallesOperativosPorPedidoYSector($rows, $estado);
    }

    private function agruparDetallesOperativosPorPedidoYSector(
        array $rows,
        EstadoDetalle $estado
    ): array {
        $resultado = [];

        foreach ($rows as $row) {
            $pedidoId = $row['pedido_id'];
            $sectorId = (int) $row['sector_id'];

            if (!isset($resultado[$pedidoId])) {
                $resultado[$pedidoId] = [
                    'pedido_id' => $row['pedido_id'],
                    'mesa_id' => $row['mesa_id'],
                    'estado_mesa' => $row['estado_mesa'],
                    'nombre_cliente' => $row['nombre_cliente'],
                    'sectores' => [],
                ];
            }

            if (!isset($resultado[$pedidoId]['sectores'][$sectorId])) {
                $sectorData = [
                    'sector_id' => $sectorId,
                    'sector_clave' => $row['sector_clave'],
                    'sector_nombre' => $row['sector_nombre'],
                    'estado_sector' => $estado->value,
                    'detalles' => [],
                ];

                if ($estado === EstadoDetalle::EN_PREPARACION) {
                    $sectorData['responsable_actual'] = [
                        'id' => $row['empleado_asigno_id'],
                        'nombre' => $row['empleado_asigno_nombre'],
                        'hora_asigno' => $row['hora_asigno'],
                    ];
                }

                $resultado[$pedidoId]['sectores'][$sectorId] = $sectorData;
            }

            $resultado[$pedidoId]['sectores'][$sectorId]['detalles'][] = [
                'producto_id' => $row['producto_id'],
                'producto_nombre' => $row['producto_nombre'],
                'cantidad' => (int) $row['cantidad'],
                'hora_asigno' => $row['hora_asigno'],
                'hora_preparo' => $row['hora_preparo'],
            ];
        }

        foreach ($resultado as &$pedido) {
            $pedido['sectores'] = array_values($pedido['sectores']);
        }

        return array_values($resultado);
    }
    private function resolverSectoresDesdeToken(TokenPayload $token): array
    {
        $empleado = $this->empleadoRepo->getEmpleadoByEmail($token->email);

        if (!$empleado) {
            throw new NotFoundException('Empleado no encontrado');
        }

        $sectores = $this->empleadoRepo->getSectoresByEmpleado((int) $empleado->id);

        if (empty($sectores)) {
            throw new ForbiddenException('El empleado no tiene sectores asignados');
        }

        return $sectores;
    }


    private function agruparDetallesPorSector(array $detalles): array
    {
        $agrupado = [];

        foreach ($detalles as $detalle) {
            $sector = (string) (
                $detalle['sector']
                ?? $detalle['sector_nombre']
                ?? $detalle['sector_clave']
                ?? 'sin_sector'
            );

            if (!isset($agrupado[$sector])) {
                $agrupado[$sector] = [];
            }

            $agrupado[$sector][] = $detalle;
        }

        return $agrupado;
    }
    public function getSeguimiento(string $mesaId, string $pedidoId): PedidoSeguimientoDTO
    {
        $pedido = $this->pedidoRepo->getPedidoByIdAndMesaId($pedidoId, $mesaId);

        if (!$pedido) {
            throw new NotFoundException('Pedido no encontrado para la mesa indicada');
        }

        $detalles = $this->detallePedidoRepo->getDetallesByPedidoId($pedidoId);

        if (empty($detalles)) {
            return new PedidoSeguimientoDTO(
                id: $pedido['id'],
                mesaId: $pedido['mesa_id'],
                estadoOperativo: $pedido['estado_operativo'],
                todosLosDetallesAsignados: false,
                minutosRestantes: null,
                horaEstimadaFinalizacion: null,
                mensaje: 'El pedido aún no tiene detalles cargados'
            );
        }

        if ($this->hayDetallesSinAsignar($detalles)) {
            return new PedidoSeguimientoDTO(
                id: $pedido['id'],
                mesaId: $pedido['mesa_id'],
                estadoOperativo: $pedido['estado_operativo'],
                todosLosDetallesAsignados: false,
                minutosRestantes: null,
                horaEstimadaFinalizacion: null,
                mensaje: 'Todavía no se asignaron todos los productos del pedido'
            );
        }

        $estimacion = $this->calcularTiempoRestantePedido($detalles);

        if ($estimacion === null) {
            return new PedidoSeguimientoDTO(
                id: $pedido['id'],
                mesaId: $pedido['mesa_id'],
                estadoOperativo: $pedido['estado_operativo'],
                todosLosDetallesAsignados: true,
                minutosRestantes: null,
                horaEstimadaFinalizacion: null,
                mensaje: 'No se pudo calcular el tiempo restante del pedido'
            );
        }

        return new PedidoSeguimientoDTO(
            id: $pedido['id'],
            mesaId: $pedido['mesa_id'],
            estadoOperativo: $pedido['estado_operativo'],
            todosLosDetallesAsignados: true,
            minutosRestantes: $estimacion['minutos_restantes'],
            horaEstimadaFinalizacion: $estimacion['hora_estimada_finalizacion'],
            mensaje: $this->resolverMensajeSeguimiento(
                $pedido['estado_operativo'],
                $estimacion['minutos_restantes']
            )
        );
    }
    private function hayDetallesSinAsignar(array $detalles): bool
    {
        foreach ($detalles as $detalle) {
            if (($detalle['hora_asigno'] ?? null) === null) {
                return true;
            }
        }

        return false;
    }

    private function calcularTiempoRestantePedido(array $detalles): ?array
    {
        $ahora = time();
        $maxRestanteSegundos = 0;
        $maxFinEstimada = null;
        $hayPendientes = false;

        foreach ($detalles as $detalle) {
            $horaAsigno = $detalle['hora_asigno'] ?? null;
            $horaPreparo = $detalle['hora_preparo'] ?? null;
            $tiempoEstimado = $detalle['tiempo_estimado_minutos'] ?? null;

            if ($horaPreparo !== null) {
                continue;
            }

            $hayPendientes = true;

            if ($horaAsigno === null || $tiempoEstimado === null) {
                return null;
            }

            $finEstimada = strtotime($horaAsigno) + ((int) $tiempoEstimado * 60);
            $restanteSegundos = max(0, $finEstimada - $ahora);

            if ($restanteSegundos > $maxRestanteSegundos) {
                $maxRestanteSegundos = $restanteSegundos;
            }

            if ($maxFinEstimada === null || $finEstimada > $maxFinEstimada) {
                $maxFinEstimada = $finEstimada;
            }
        }

        if (!$hayPendientes) {
            return [
                'minutos_restantes' => 0,
                'hora_estimada_finalizacion' => null,
            ];
        }

        return [
            'minutos_restantes' => (int) ceil($maxRestanteSegundos / 60),
            'hora_estimada_finalizacion' => date('Y-m-d H:i:s', $maxFinEstimada),
        ];
    }

    private function resolverMensajeSeguimiento(string $estadoOperativo, ?int $minutosRestantes): string
    {
        if ($estadoOperativo === 'cancelado') {
            return 'El pedido fue cancelado';
        }

        if ($estadoOperativo === 'cerrado') {
            return 'El pedido fue finalizado';
        }

        if ($minutosRestantes === 0) {
            return 'El pedido está listo para servir';
        }

        return 'El pedido está en preparación';
    }
}