<?php

namespace App\Repositories;

use PDO;

final class EmpleadoProduccionRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function getProduccionPorEmpleado(string $from, string $to): array
    {
        $sql = "
            SELECT
                e.id AS empleado_id,
                e.nombre AS empleado,
                e.email,
                te.tipo,
                COUNT(dp.id) AS detalles_preparados,
                COALESCE(SUM(dp.cantidad), 0) AS unidades_preparadas,
                COUNT(DISTINCT dp.pedido_id) AS pedidos_intervenidos
            FROM detalles_pedido dp
            JOIN empleados e
                ON e.id = dp.empleado_preparo_id
            JOIN tipos_empleado te
                ON te.id = e.tipo_empleado_id
            WHERE dp.empleado_preparo_id IS NOT NULL
              AND dp.hora_preparo IS NOT NULL
              AND dp.hora_preparo BETWEEN :from AND :to
            GROUP BY e.id, e.nombre, e.email, te.tipo
            ORDER BY unidades_preparadas DESC, detalles_preparados DESC, e.nombre ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->normalizeRows($rows, [
            'empleado_id',
            'detalles_preparados',
            'unidades_preparadas',
            'pedidos_intervenidos',
        ]);
    }

    public function getProduccionPorSector(string $from, string $to): array
    {
        $sql = "
            SELECT
                s.id AS sector_id,
                s.clave AS sector_clave,
                s.nombre AS sector,
                COUNT(dp.id) AS detalles_preparados,
                COALESCE(SUM(dp.cantidad), 0) AS unidades_preparadas,
                COUNT(DISTINCT dp.pedido_id) AS pedidos_intervenidos,
                COUNT(DISTINCT dp.empleado_preparo_id) AS empleados_intervinientes
            FROM detalles_pedido dp
            JOIN productos pr
                ON pr.id = dp.producto_id
            JOIN sectores s
                ON s.id = pr.sector_id
            WHERE dp.empleado_preparo_id IS NOT NULL
              AND dp.hora_preparo IS NOT NULL
              AND dp.hora_preparo BETWEEN :from AND :to
            GROUP BY s.id, s.clave, s.nombre
            ORDER BY unidades_preparadas DESC, detalles_preparados DESC, s.nombre ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->normalizeRows($rows, [
            'sector_id',
            'detalles_preparados',
            'unidades_preparadas',
            'pedidos_intervenidos',
            'empleados_intervinientes',
        ]);
    }

    public function getProduccionPorSectorYEmpleado(string $from, string $to): array
    {
        $sql = "
            SELECT
                s.id AS sector_id,
                s.clave AS sector_clave,
                s.nombre AS sector,
                e.id AS empleado_id,
                e.nombre AS empleado,
                e.email,
                te.tipo,
                COUNT(dp.id) AS detalles_preparados,
                COALESCE(SUM(dp.cantidad), 0) AS unidades_preparadas,
                COUNT(DISTINCT dp.pedido_id) AS pedidos_intervenidos
            FROM detalles_pedido dp
            JOIN productos pr
                ON pr.id = dp.producto_id
            JOIN sectores s
                ON s.id = pr.sector_id
            JOIN empleados e
                ON e.id = dp.empleado_preparo_id
            JOIN tipos_empleado te
                ON te.id = e.tipo_empleado_id
            WHERE dp.empleado_preparo_id IS NOT NULL
              AND dp.hora_preparo IS NOT NULL
              AND dp.hora_preparo BETWEEN :from AND :to
            GROUP BY
                s.id,
                s.clave,
                s.nombre,
                e.id,
                e.nombre,
                e.email,
                te.tipo
            ORDER BY s.nombre ASC, unidades_preparadas DESC, detalles_preparados DESC, e.nombre ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->normalizeRows($rows, [
            'sector_id',
            'empleado_id',
            'detalles_preparados',
            'unidades_preparadas',
            'pedidos_intervenidos',
        ]);
    }

    public function getProduccionDetalladaPorEmpleado(string $from, string $to): array
    {
        $sql = "
            SELECT
                e.id AS empleado_id,
                e.nombre AS empleado,
                e.email,
                te.tipo,
                s.id AS sector_id,
                s.clave AS sector_clave,
                s.nombre AS sector,
                pr.id AS producto_id,
                pr.nombre AS producto,
                COUNT(dp.id) AS detalles_preparados,
                COALESCE(SUM(dp.cantidad), 0) AS unidades_preparadas,
                COUNT(DISTINCT dp.pedido_id) AS pedidos_intervenidos
            FROM detalles_pedido dp
            JOIN productos pr
                ON pr.id = dp.producto_id
            JOIN sectores s
                ON s.id = pr.sector_id
            JOIN empleados e
                ON e.id = dp.empleado_preparo_id
            JOIN tipos_empleado te
                ON te.id = e.tipo_empleado_id
            WHERE dp.empleado_preparo_id IS NOT NULL
              AND dp.hora_preparo IS NOT NULL
              AND dp.hora_preparo BETWEEN :from AND :to
            GROUP BY
                e.id,
                e.nombre,
                e.email,
                te.tipo,
                s.id,
                s.clave,
                s.nombre,
                pr.id,
                pr.nombre
            ORDER BY e.nombre ASC, s.nombre ASC, unidades_preparadas DESC, pr.nombre ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->normalizeRows($rows, [
            'empleado_id',
            'sector_id',
            'producto_id',
            'detalles_preparados',
            'unidades_preparadas',
            'pedidos_intervenidos',
        ]);
    }

    private function normalizeRows(array $rows, array $intFields): array
    {
        foreach ($rows as &$row) {
            foreach ($intFields as $field) {
                if (array_key_exists($field, $row) && $row[$field] !== null) {
                    $row[$field] = (int) $row[$field];
                }
            }
        }

        return $rows;
    }
}