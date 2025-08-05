<?php
namespace App\Repositories;

use PDO;

class DetallePedidoRepository
{
    public const SIN_ASIGNAR = 'sin_asignar';
    public const EN_PREPARACION = 'en_preparacion';
    public const SIN_ENTREGAR = 'sin_entregar';
    
    public function __construct(private PDO $pdo) {}

    public function getDetallePedido(int $pedidoId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM detalles_pedido WHERE pedido_id = :pedido");
        $stmt->execute([':pedido' => $pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertarDetalles(string $pedidoId, array $detalles): int
    {
        $sql = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad)
                VALUES (:pedido_id, :producto_id, :cantidad)";
        $stmt = $this->pdo->prepare($sql);

        $contador = 0;
        foreach ($detalles as $detalle) {
            if (!isset($detalle['id'], $detalle['cantidad'])) {
                throw new \InvalidArgumentException('Formato de detalle inválido');
            }
            $stmt->execute([
                'pedido_id' => $pedidoId,
                'producto_id' => $detalle['id'],
                'cantidad' => $detalle['cantidad'],
            ]);
            $contador += $stmt->rowCount();
        }

        return $contador;
    }

        public function getDetallesDelPedido(int $pedidoId): array
    {
        $sql = "SELECT
                    s.nombre AS sector_nombre,
                    s.id AS sector_id,
                    p.id            AS pedido_id,
                    p.mesa_id,
                    me.nombre        AS estado_mesa,
                    p.nombre_cliente,
                    dp.producto_id AS producto,
                    dp.cantidad,
                    pr.nombre AS producto_nombre
                FROM pedidos p
                JOIN detalles_pedido dp ON dp.pedido_id = p.id
                JOIN productos        pr ON pr.id = dp.producto_id
                JOIN mesas            m  ON m.id  = p.mesa_id
                JOIN mesa_estados me ON me.id = m.estado
                JOIN sectores s ON s.id = pr.sector_id
                WHERE p.id = :pedido";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pedido' => $pedidoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPedidosDetalles(int $sectorId, DetallePedidoRepository $estado = DetallePedidoRepository::SIN_ASIGNAR): array
{
    // Validamos estado permitido y asignamos la condición correspondiente
    $estadosPermitidos = [
        DetallePedidoRepository::SIN_ASIGNAR     => 'dp.hora_asigno IS NULL AND dp.hora_preparo IS NULL AND dp.hora_entrego IS NULL',
        DetallePedidoRepository::EN_PREPARACION => 'dp.hora_preparo IS NULL AND dp.hora_entrego IS NULL',
        DetallePedidoRepository::SIN_ENTREGAR   => 'dp.hora_entrego IS NULL'
    ];

    $condicion = $estadosPermitidos[(string)$estado] ?? $estadosPermitidos[DetallePedidoRepository::SIN_ASIGNAR];

    $sql = "SELECT
            s.nombre AS sector_nombre,
            s.id     AS sector_id,
            p.id     AS pedido_id,
            p.mesa_id,
            me.nombre AS estado_mesa,
            p.nombre_cliente,
            dp.producto_id AS producto,
            dp.cantidad,
            pr.nombre AS producto_nombre
        FROM pedidos p
        JOIN detalles_pedido dp ON dp.pedido_id = p.id
        JOIN productos        pr ON pr.id = dp.producto_id
        JOIN mesas            m  ON m.id  = p.mesa_id
        JOIN mesa_estados    me ON me.id = m.estado
        JOIN sectores        s  ON s.id = pr.sector_id
        WHERE pr.sector_id = :sector
          AND $condicion
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['sector' => $sectorId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function declararEstadoPorSector(string $campoEmpleado, string $campoHora, int $pedidoId, int $empleadoId, string $hora, int $sectorId): int
    {
        $sql = "UPDATE detalles_pedido dp
                JOIN productos p ON p.id = dp.producto_id
                SET dp.{$campoEmpleado} = :emp, dp.{$campoHora} = :hora
                WHERE dp.pedido_id = :pedido
                  AND p.sector_id = :sector
                  AND dp.{$campoEmpleado} IS NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'emp' => $empleadoId,
            'hora' => $hora,
            'pedido' => $pedidoId,
            'sector' => $sectorId,
        ]);

        return $stmt->rowCount();
    }

    public function declararPedidoDetallesAsignadoPorSector(int $pedidoId, int $empleadoId, string $hora, int $sectorId): int
    {
        return $this->declararEstadoPorSector('empleado_asigno_id', 'hora_asigno', $pedidoId, $empleadoId, $hora, $sectorId);
    }

    public function declararPedidoPreparadoPorSector(int $pedidoId, int $empleadoId, string $hora, int $sectorId): int
    {
        return $this->declararEstadoPorSector('empleado_preparo_id', 'hora_preparo', $pedidoId, $empleadoId, $hora, $sectorId);
    }

    public function declararPedidoEntregadoPorSector(int $pedidoId, int $empleadoId, string $hora, int $sectorId): int
    {
        return $this->declararEstadoPorSector('empleado_entrego_id', 'hora_entrego', $pedidoId, $empleadoId, $hora, $sectorId);
    }

    public function todosEntregados(int $pedidoId): bool
    {
        $sql = "SELECT COUNT(*)
                FROM detalles_pedido
                WHERE pedido_id = :pedido
                  AND empleado_entrego_id IS NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pedido' => $pedidoId]);
        return (int)$stmt->fetchColumn() === 0;
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
        $stmt->execute(['from' => $from, 'to' => $to]);
        return (float)$stmt->fetchColumn();
    }
}
