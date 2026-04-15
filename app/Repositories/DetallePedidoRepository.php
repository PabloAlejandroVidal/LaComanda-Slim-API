<?php
namespace App\Repositories;

use App\Domain\Pedido\EstadoDetalle;
use PDO;
use InvalidArgumentException;

class DetallePedidoRepository
{
    public function __construct(private PDO $pdo) {}

    private function buildEstadoCondicion(EstadoDetalle $estado): string
    {
        $pedidoActivo = 'p.hora_cancelacion IS NULL';

        return match ($estado) {
            EstadoDetalle::PENDIENTE =>
                "{$pedidoActivo} AND dp.hora_asigno IS NULL",

            EstadoDetalle::EN_PREPARACION =>
                "{$pedidoActivo} AND dp.hora_asigno IS NOT NULL AND dp.hora_preparo IS NULL",

            EstadoDetalle::LISTO =>
                "{$pedidoActivo} AND dp.hora_preparo IS NOT NULL AND p.hora_entrega IS NULL",

            EstadoDetalle::ENTREGADO =>
                "{$pedidoActivo} AND p.hora_entrega IS NOT NULL",
        };
    }

    public function insertarDetalles(string $pedidoId, array $detalles): int
    {
        $sql = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad)
                VALUES (:pedido_id, :producto_id, :cantidad)";
        $stmt = $this->pdo->prepare($sql);

        $contador = 0;

        foreach ($detalles as $detalle) {
            $productoId = $detalle['producto_id'] ?? $detalle['id'] ?? null;
            $cantidad   = $detalle['cantidad'] ?? null;

            if ($productoId === null || $cantidad === null) {
                throw new InvalidArgumentException('Formato de detalle inválido (se espera producto_id/id y cantidad)');
            }

            $stmt->execute([
                ':pedido_id'   => $pedidoId,
                ':producto_id' => (int)$productoId,
                ':cantidad'    => (int)$cantidad,
            ]);

            $contador += $stmt->rowCount();
        }

        return $contador;
    }

    public function getDetallesDelPedido(string $pedidoId): array
    {
        $sql = "
            SELECT
                s.id AS sector_id,
                s.nombre AS sector_nombre,
                dp.producto_id,
                dp.cantidad,
                pr.nombre AS producto_nombre,
                dp.hora_asigno,
                dp.hora_preparo
            FROM detalles_pedido dp
            JOIN productos pr ON pr.id = dp.producto_id
            JOIN sectores s ON s.id = pr.sector_id
            WHERE dp.pedido_id = :pedido
            ORDER BY s.nombre ASC, pr.nombre ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pedido' => $pedidoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidosDetalles(int $sectorId, EstadoDetalle $estadoDetalle): array
    {
        $condicion = $this->buildEstadoCondicion($estadoDetalle);

        $sql = "SELECT
                    s.id AS sector_id,
                    s.clave AS sector_clave,
                    s.nombre AS sector_nombre,
                    p.id AS pedido_id,
                    p.mesa_id,
                    me.nombre AS estado_mesa,
                    p.nombre_cliente,
                    dp.producto_id,
                    dp.cantidad,
                    pr.nombre AS producto_nombre,
                    dp.empleado_asigno_id,
                    ea.nombre AS empleado_asigno_nombre,
                    dp.hora_asigno,
                    dp.empleado_preparo_id,
                    ep.nombre AS empleado_preparo_nombre,
                    dp.hora_preparo
                FROM pedidos p
                JOIN detalles_pedido dp ON dp.pedido_id = p.id
                JOIN productos pr ON pr.id = dp.producto_id
                JOIN mesas m ON m.id = p.mesa_id
                JOIN mesa_estados me ON me.id = m.estado
                JOIN sectores s ON s.id = pr.sector_id
                LEFT JOIN empleados ea ON ea.id = dp.empleado_asigno_id
                LEFT JOIN empleados ep ON ep.id = dp.empleado_preparo_id
                WHERE pr.sector_id = :sector
                AND {$condicion}
                ORDER BY p.hora_inicio ASC, p.id ASC, s.id ASC, pr.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sector' => $sectorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function declararEstadoPorSector(
        string $campoEmpleado,
        string $campoHora,
        string $precondicionSql,
        string $pedidoId,
        int $empleadoId,
        string $hora,
        int $sectorId
    ): int {
        $sql = "UPDATE detalles_pedido dp
                JOIN productos p ON p.id = dp.producto_id
                SET dp.{$campoEmpleado} = :emp, dp.{$campoHora} = :hora
                WHERE dp.pedido_id = :pedido
                  AND p.sector_id = :sector
                  AND {$precondicionSql}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':emp'    => $empleadoId,
            ':hora'   => $hora,
            ':pedido' => $pedidoId,
            ':sector' => $sectorId,
        ]);

        return $stmt->rowCount();
    }

    public function declararPedidoEnPreparacionPorSector(
        string $pedidoId,
        int $empleadoId,
        string $hora,
        int $sectorId,
        int $tiempoEstimadoMinutos
    ): int {
        $sql = "UPDATE detalles_pedido dp
                JOIN productos p ON p.id = dp.producto_id
                SET dp.empleado_asigno_id = :emp,
                    dp.hora_asigno = :hora,
                    dp.tiempo_estimado_minutos = :tiempo_estimado
                WHERE dp.pedido_id = :pedido
                AND p.sector_id = :sector
                AND dp.hora_asigno IS NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':emp'              => $empleadoId,
            ':hora'             => $hora,
            ':tiempo_estimado'  => $tiempoEstimadoMinutos,
            ':pedido'           => $pedidoId,
            ':sector'           => $sectorId,
        ]);

        return $stmt->rowCount();
    }

    public function declararPedidoListoPorSector(string $pedidoId, int $empleadoId, string $hora, int $sectorId): int
    {
        return $this->declararEstadoPorSector(
            'empleado_preparo_id',
            'hora_preparo',
            'dp.hora_asigno IS NOT NULL AND dp.hora_preparo IS NULL',
            $pedidoId,
            $empleadoId,
            $hora,
            $sectorId
        );
    }


    public function todosListosParaEntregar(string $pedidoId): bool
    {
        $condicionListo = $this->buildEstadoCondicion(EstadoDetalle::LISTO);

        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN {$condicionListo} THEN 1 ELSE 0 END) AS listos
            FROM detalles_pedido dp
            JOIN pedidos p ON p.id = dp.pedido_id
            WHERE dp.pedido_id = :pedido
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pedido' => $pedidoId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int) ($row['total'] ?? 0);
        $listos = (int) ($row['listos'] ?? 0);

        return $total > 0 && $total === $listos;
    }
    

    public function promedioPorPedido(string $from, string $to): float
    {
        $sql = "SELECT AVG(cnt) FROM (
                    SELECT COUNT(*) AS cnt
                    FROM detalles_pedido dp
                    JOIN pedidos p ON p.id = dp.pedido_id
                    WHERE p.hora_inicio BETWEEN :from AND :to
                    GROUP BY p.id
                ) sub";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        $val = $stmt->fetchColumn();
        return $val !== false ? (float)$val : 0.0;
    }
    public function getDetallesByPedidoId(string $pedidoId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                dp.hora_asigno,
                dp.hora_preparo,
                dp.tiempo_estimado_minutos
            FROM detalles_pedido dp
            WHERE dp.pedido_id = :pedido_id
        ");

        $stmt->execute([
            'pedido_id' => $pedidoId,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}