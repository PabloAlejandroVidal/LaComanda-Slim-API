<?php

namespace App\Repositories;

use PDO;

final class IngresoRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function registrarIngreso(int $empleadoId, string $fechaHora): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO ingresos (empleado_id, hora_ingreso)
            VALUES (:empleado_id, :hora_ingreso)
        ");

        $stmt->execute([
            ':empleado_id' => $empleadoId,
            ':hora_ingreso' => $fechaHora,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function obtenerIngresosEntre(string $from, string $to): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                i.id,
                i.empleado_id,
                e.nombre AS empleado_nombre,
                e.email AS empleado_email,
                te.tipo AS rol,
                i.hora_ingreso
            FROM ingresos i
            INNER JOIN empleados e ON e.id = i.empleado_id
            LEFT JOIN tipos_empleado te ON te.id = e.tipo_empleado_id
            WHERE i.hora_ingreso BETWEEN :from AND :to
            ORDER BY i.hora_ingreso DESC, i.id DESC
        ");

        $stmt->execute([
            ':from' => $from,
            ':to' => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerResumenIngresosEntre(string $from, string $to): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                e.id AS empleado_id,
                e.nombre AS empleado_nombre,
                e.email AS empleado_email,
                te.tipo AS rol,
                COUNT(i.id) AS cantidad_ingresos,
                MIN(i.hora_ingreso) AS primer_ingreso,
                MAX(i.hora_ingreso) AS ultimo_ingreso
            FROM empleados e
            LEFT JOIN tipos_empleado te ON te.id = e.tipo_empleado_id
            LEFT JOIN ingresos i
                ON i.empleado_id = e.id
               AND i.hora_ingreso BETWEEN :from AND :to
            GROUP BY e.id, e.nombre, e.email, te.tipo
            ORDER BY cantidad_ingresos DESC, e.nombre ASC
        ");

        $stmt->execute([
            ':from' => $from,
            ':to' => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function contarIngresosEntre(string $from, string $to): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM ingresos
            WHERE hora_ingreso BETWEEN :from AND :to
        ");

        $stmt->execute([
            ':from' => $from,
            ':to' => $to,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function obtenerUltimoIngresoEntre(string $from, string $to): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                i.id,
                i.empleado_id,
                e.nombre AS empleado_nombre,
                e.email AS empleado_email,
                te.tipo AS rol,
                i.hora_ingreso
            FROM ingresos i
            INNER JOIN empleados e ON e.id = i.empleado_id
            LEFT JOIN tipos_empleado te ON te.id = e.tipo_empleado_id
            WHERE i.hora_ingreso BETWEEN :from AND :to
            ORDER BY i.hora_ingreso DESC, i.id DESC
            LIMIT 1
        ");

        $stmt->execute([
            ':from' => $from,
            ':to' => $to,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}