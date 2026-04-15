<?php

namespace App\Repositories;

use Exception;
use PDO;

class PedidoRepository
{
    public function __construct(private PDO $pdo) {}

    public function getPedidoById(string $pedidoId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.mesa_id,
                p.nombre_cliente,
                p.empleado_inicio_id,
                p.empleado_entrega_id,
                p.empleado_cobro_id,
                p.empleado_cierre_id,
                p.empleado_cancelacion_id,
                p.hora_inicio,
                p.hora_entrega,
                p.hora_pago,
                p.hora_cierre,
                p.hora_cancelacion,
                p.motivo_cancelacion,
                p.importe
            FROM pedidos p
            WHERE p.id = :pedido
        ");

        $stmt->execute([':pedido' => $pedidoId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['estado_operativo'] = match (true) {
            $row['hora_cierre'] !== null => 'cerrado',
            $row['hora_cancelacion'] !== null => 'cancelado',
            $row['hora_pago'] !== null => 'pagando',
            $row['hora_entrega'] !== null => 'entregado',
            default => 'pendiente',
        };

        return $row;
    }

    public function existsById(string $pedidoId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT 1
            FROM pedidos
            WHERE id = :id
            LIMIT 1
        ');

        $stmt->execute([
            'id' => $pedidoId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function isPagado(string $pedidoId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT hora_pago
            FROM pedidos
            WHERE id = :id
            LIMIT 1
        ');

        $stmt->execute([
            'id' => $pedidoId,
        ]);

        $horaPago = $stmt->fetchColumn();

        return $horaPago !== false && $horaPago !== null;
    }
    
    public function crearPedido(
        string $pedidoId,
        int $empleadoId,
        string $mesaId,
        string $nombreCliente,
        string $hora
    ): string {
        $stmt = $this->pdo->prepare("
            INSERT INTO pedidos (
                id,
                mesa_id,
                nombre_cliente,
                empleado_inicio_id,
                hora_inicio
            )
            VALUES (
                :id,
                :mesa,
                :nombre,
                :empleado_inicio_id,
                :hora_inicio
            )
        ");

        $success = $stmt->execute([
            ':id'                 => $pedidoId,
            ':mesa'               => $mesaId,
            ':nombre'             => $nombreCliente,
            ':empleado_inicio_id' => $empleadoId,
            ':hora_inicio'        => $hora,
        ]);

        if (!$success) {
            throw new Exception("No se pudo agregar el pedido {$pedidoId}");
        }

        return $pedidoId;
    }

    public function listar(array $filtros = []): array
    {
        $sql = "
            SELECT
                p.id,
                p.mesa_id,
                p.nombre_cliente,
                p.empleado_inicio_id,
                p.empleado_entrega_id,
                p.empleado_cobro_id,
                p.empleado_cierre_id,
                p.empleado_cancelacion_id,
                p.hora_inicio,
                p.hora_entrega,
                p.hora_pago,
                p.hora_cierre,
                p.hora_cancelacion,
                p.motivo_cancelacion,
                p.importe,
                COUNT(dp.id) AS total_detalles,
                SUM(CASE WHEN dp.hora_asigno IS NOT NULL THEN 1 ELSE 0 END) AS detalles_asignados,
                SUM(CASE WHEN dp.hora_preparo IS NOT NULL THEN 1 ELSE 0 END) AS detalles_preparados
            FROM pedidos p
            LEFT JOIN detalles_pedido dp ON dp.pedido_id = p.id
            WHERE 1 = 1
        ";

        $params = [];

        if (!empty($filtros['id'])) {
            $sql .= " AND p.id = :id";
            $params[':id'] = $filtros['id'];
        }

        if (!empty($filtros['mesa_id'])) {
            $sql .= " AND p.mesa_id = :mesa_id";
            $params[':mesa_id'] = $filtros['mesa_id'];
        }

        if (!empty($filtros['empleado_inicio_id'])) {
            $sql .= " AND p.empleado_inicio_id = :empleado_inicio_id";
            $params[':empleado_inicio_id'] = (int) $filtros['empleado_inicio_id'];
        }

        if (!empty($filtros['empleado_entrega_id'])) {
            $sql .= " AND p.empleado_entrega_id = :empleado_entrega_id";
            $params[':empleado_entrega_id'] = (int) $filtros['empleado_entrega_id'];
        }

        if (!empty($filtros['empleado_cobro_id'])) {
            $sql .= " AND p.empleado_cobro_id = :empleado_cobro_id";
            $params[':empleado_cobro_id'] = (int) $filtros['empleado_cobro_id'];
        }

        if (!empty($filtros['empleado_cierre_id'])) {
            $sql .= " AND p.empleado_cierre_id = :empleado_cierre_id";
            $params[':empleado_cierre_id'] = (int) $filtros['empleado_cierre_id'];
        }

        if (!empty($filtros['empleado_cancelacion_id'])) {
            $sql .= " AND p.empleado_cancelacion_id = :empleado_cancelacion_id";
            $params[':empleado_cancelacion_id'] = (int) $filtros['empleado_cancelacion_id'];
        }

        if (!empty($filtros['from'])) {
            $sql .= " AND p.hora_inicio >= :from";
            $params[':from'] = $filtros['from'];
        }

        if (!empty($filtros['to'])) {
            $sql .= " AND p.hora_inicio <= :to";
            $params[':to'] = $filtros['to'];
        }

        if (array_key_exists('entregado', $filtros)) {
            $entregado = filter_var($filtros['entregado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($entregado === true) {
                $sql .= " AND p.hora_entrega IS NOT NULL";
            } elseif ($entregado === false) {
                $sql .= " AND p.hora_entrega IS NULL";
            }
        }

        if (array_key_exists('pagado', $filtros)) {
            $pagado = filter_var($filtros['pagado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($pagado === true) {
                $sql .= " AND p.hora_pago IS NOT NULL";
            } elseif ($pagado === false) {
                $sql .= " AND p.hora_pago IS NULL";
            }
        }

        if (array_key_exists('cerrado', $filtros)) {
            $cerrado = filter_var($filtros['cerrado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($cerrado === true) {
                $sql .= " AND p.hora_cierre IS NOT NULL";
            } elseif ($cerrado === false) {
                $sql .= " AND p.hora_cierre IS NULL";
            }
        }

        if (array_key_exists('cancelado', $filtros)) {
            $cancelado = filter_var($filtros['cancelado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($cancelado === true) {
                $sql .= " AND p.hora_cancelacion IS NOT NULL";
            } elseif ($cancelado === false) {
                $sql .= " AND p.hora_cancelacion IS NULL";
            }
        }

        $sql .= "
            GROUP BY
                p.id,
                p.mesa_id,
                p.nombre_cliente,
                p.empleado_inicio_id,
                p.empleado_entrega_id,
                p.empleado_cobro_id,
                p.empleado_cierre_id,
                p.empleado_cancelacion_id,
                p.hora_inicio,
                p.hora_entrega,
                p.hora_pago,
                p.hora_cierre,
                p.hora_cancelacion,
                p.motivo_cancelacion,
                p.importe
            ORDER BY p.hora_inicio DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $total = (int) $row['total_detalles'];
            $asignados = (int) $row['detalles_asignados'];
            $preparados = (int) $row['detalles_preparados'];

            $row['estado_operativo'] = match (true) {
                $row['hora_cierre'] !== null => 'cerrado',
                $row['hora_cancelacion'] !== null => 'cancelado',
                $row['hora_pago'] !== null => 'pagando',
                $row['hora_entrega'] !== null => 'entregado',
                $total > 0 && $preparados === $total => 'listo_para_entregar',
                $asignados > 0 => 'en_preparacion',
                default => 'pendiente',
            };

            unset(
                $row['total_detalles'],
                $row['detalles_asignados'],
                $row['detalles_preparados']
            );
        }

        return $rows;
    }

    public function getMesaId(string $pedidoId): string
    {
        $stmt = $this->pdo->prepare("
            SELECT mesa_id
            FROM pedidos
            WHERE id = :pedido
        ");

        $stmt->execute([':pedido' => $pedidoId]);

        $mesaId = $stmt->fetchColumn();

        if ($mesaId === false) {
            throw new Exception("Pedido {$pedidoId} no encontrado");
        }

        return (string) $mesaId;
    }

    public function getMonto(string $pedidoId): float
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(dp.cantidad * pr.precio) AS total
            FROM detalles_pedido dp
            JOIN productos pr ON pr.id = dp.producto_id
            WHERE dp.pedido_id = :pedido
        ");

        $stmt->execute([':pedido' => $pedidoId]);

        $valor = $stmt->fetchColumn();

        return $valor !== false ? (float) $valor : 0.0;
    }

    public function registrarEntrega(
        string $pedidoId,
        int $empleadoEntregaId,
        string $horaEntrega
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE pedidos
            SET empleado_entrega_id = :empleado_entrega_id,
                hora_entrega = :hora_entrega
            WHERE id = :id
              AND hora_entrega IS NULL
        ");

        $stmt->execute([
            ':id'                  => $pedidoId,
            ':empleado_entrega_id' => $empleadoEntregaId,
            ':hora_entrega'        => $horaEntrega,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("No se pudo registrar la entrega del pedido {$pedidoId}");
        }
    }

    public function registrarCobro(
        string $pedidoId,
        int $empleadoCobroId,
        string $horaPago,
        float $importe
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE pedidos
            SET empleado_cobro_id = :empleado_cobro_id,
                hora_pago = :hora_pago,
                importe = :importe
            WHERE id = :id
              AND hora_pago IS NULL
        ");

        $stmt->execute([
            ':id'                => $pedidoId,
            ':empleado_cobro_id' => $empleadoCobroId,
            ':hora_pago'         => $horaPago,
            ':importe'           => $importe,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("No se pudo registrar el cobro del pedido {$pedidoId}");
        }
    }

    public function cerrarPedido(
        string $pedidoId,
        int $empleadoCierreId,
        string $horaCierre
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE pedidos
            SET empleado_cierre_id = :empleado_cierre_id,
                hora_cierre = :hora_cierre
            WHERE id = :id
              AND hora_pago IS NOT NULL
              AND hora_cierre IS NULL
        ");

        $stmt->execute([
            ':id'                 => $pedidoId,
            ':empleado_cierre_id' => $empleadoCierreId,
            ':hora_cierre'        => $horaCierre,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("No se pudo cerrar el pedido {$pedidoId}");
        }
    }
    
    public function cancelarPedido(
        string $pedidoId,
        int $empleadoCancelacionId,
        string $horaCancelacion,
        ?string $motivoCancelacion = null
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE pedidos
            SET empleado_cancelacion_id = :empleado_cancelacion_id,
                hora_cancelacion = :hora_cancelacion,
                motivo_cancelacion = :motivo_cancelacion
            WHERE id = :id
            AND hora_entrega IS NULL
            AND hora_pago IS NULL
            AND hora_cierre IS NULL
            AND hora_cancelacion IS NULL
        ");

        $stmt->execute([
            ':id'                       => $pedidoId,
            ':empleado_cancelacion_id'  => $empleadoCancelacionId,
            ':hora_cancelacion'         => $horaCancelacion,
            ':motivo_cancelacion'       => $motivoCancelacion,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new \Exception("No se pudo cancelar el pedido {$pedidoId}");
        }
    }
    
    public function getMasVendido(string $from, string $to): ?array
    {
        $sql = "
            SELECT
                pr.id,
                pr.nombre,
                SUM(dp.cantidad) AS total_vendido
            FROM detalles_pedido dp
            JOIN productos pr ON pr.id = dp.producto_id
            JOIN pedidos p ON p.id = dp.pedido_id
            WHERE p.hora_inicio BETWEEN :from AND :to
              AND p.hora_pago IS NOT NULL
            GROUP BY pr.id, pr.nombre
            ORDER BY total_vendido DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMenosVendido(string $from, string $to): ?array
    {
        $sql = "
            SELECT
                pr.id,
                pr.nombre,
                SUM(dp.cantidad) AS total_vendido
            FROM detalles_pedido dp
            JOIN productos pr ON pr.id = dp.producto_id
            JOIN pedidos p ON p.id = dp.pedido_id
            WHERE p.hora_inicio BETWEEN :from AND :to
              AND p.hora_pago IS NOT NULL
            GROUP BY pr.id, pr.nombre
            ORDER BY total_vendido ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getPedidosFueraDeTiempo(string $from, string $to): array
    {
        $sql = "
            SELECT
                p.id,
                p.mesa_id,
                p.nombre_cliente,
                p.hora_inicio,
                p.hora_entrega AS hora_entrega_real,
                MAX(DATE_ADD(dp.hora_asigno, INTERVAL dp.tiempo_estimado_minutos MINUTE)) AS hora_estimada_pedido,
                TIMESTAMPDIFF(
                    MINUTE,
                    MAX(DATE_ADD(dp.hora_asigno, INTERVAL dp.tiempo_estimado_minutos MINUTE)),
                    p.hora_entrega
                ) AS minutos_retraso
            FROM pedidos p
            JOIN detalles_pedido dp ON dp.pedido_id = p.id
            WHERE p.hora_inicio BETWEEN :from AND :to
              AND dp.hora_asigno IS NOT NULL
              AND dp.tiempo_estimado_minutos IS NOT NULL
              AND p.hora_entrega IS NOT NULL
            GROUP BY
                p.id,
                p.mesa_id,
                p.nombre_cliente,
                p.hora_inicio,
                p.hora_entrega
            HAVING p.hora_entrega > MAX(DATE_ADD(dp.hora_asigno, INTERVAL dp.tiempo_estimado_minutos MINUTE))
            ORDER BY minutos_retraso DESC, p.hora_inicio DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCancelados(string $from, string $to): array
    {
        $sql = "
            SELECT
                p.id,
                p.mesa_id,
                p.nombre_cliente,
                p.hora_inicio,
                p.hora_cancelacion,
                p.motivo_cancelacion,
                e.nombre AS cancelado_por
            FROM pedidos p
            LEFT JOIN empleados e
                ON e.id = p.empleado_cancelacion_id
            WHERE p.hora_cancelacion IS NOT NULL
            AND p.hora_cancelacion BETWEEN :from AND :to
            ORDER BY p.hora_cancelacion DESC, p.hora_inicio DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['estado_final'] = 'cancelado';
        }

        return $rows;
    }

    public function getCerrados(string $from, string $to): array
    {
        $sql = "
            SELECT
                p.id,
                p.mesa_id,
                p.nombre_cliente,
                p.hora_inicio,
                p.hora_entrega,
                p.hora_pago,
                p.hora_cierre,
                p.importe,
                e.nombre AS cerrado_por,
                TIMESTAMPDIFF(MINUTE, p.hora_inicio, p.hora_cierre) AS duracion_total_minutos
            FROM pedidos p
            LEFT JOIN empleados e
                ON e.id = p.empleado_cierre_id
            WHERE p.hora_cierre IS NOT NULL
            AND p.hora_cierre BETWEEN :from AND :to
            ORDER BY p.hora_cierre DESC, p.hora_inicio DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['estado_final'] = 'cerrado';
        }

        return $rows;
    }

    public function getPedidoByIdAndMesaId(string $pedidoId, string $mesaId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.mesa_id,
                p.hora_inicio,
                p.hora_entrega,
                p.hora_pago,
                p.hora_cierre,
                p.hora_cancelacion,
                p.motivo_cancelacion,
                p.importe,
                CASE
                    WHEN p.hora_cancelacion IS NOT NULL THEN 'cancelado'
                    WHEN p.hora_cierre IS NOT NULL THEN 'cerrado'
                    WHEN p.hora_pago IS NOT NULL THEN 'pagado'
                    WHEN p.hora_entrega IS NOT NULL THEN 'entregado'
                    ELSE 'en_preparacion'
                END AS estado_operativo
            FROM pedidos p
            WHERE p.id = :pedido_id
            AND p.mesa_id = :mesa_id
            LIMIT 1
        ");

        $stmt->execute([
            'pedido_id' => $pedidoId,
            'mesa_id' => $mesaId,
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}