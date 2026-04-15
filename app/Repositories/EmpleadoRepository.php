<?php
namespace App\Repositories;

use App\Entities\Empleado;
use PDO;

class EmpleadoRepository
{
    public function __construct(private PDO $pdo) {}

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1 FROM empleados
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() !== false;
    }

    public function registrarIngreso(int $empleadoId, string $horaIngreso): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO ingresos (empleado_id, hora_ingreso)
            VALUES (:empleado_id, :hora_ingreso)
        ");

        $stmt->execute([
            ':empleado_id' => $empleadoId,
            ':hora_ingreso' => $horaIngreso
        ]);

        return $stmt->rowCount();
    }

    public function registrarEmpleado(string $nombre, string $email, string $clave, int $tipoId): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO empleados (tipo_empleado_id, nombre, email, clave, estado)
            VALUES (:tipo_empleado_id, :nombre, :email, :clave, :estado)
        ");

        $stmt->execute([
            'tipo_empleado_id' => $tipoId,
            'nombre'           => $nombre,
            'email'            => $email,
            'clave'            => $clave,
            'estado'           => 'activo'
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getSectoresByEmpleado(int $empleadoId): array
    {
        $sql = "
            SELECT s.id, s.clave
            FROM sectores s
            JOIN permisos p ON p.sector_id = s.id
            JOIN empleados e ON e.tipo_empleado_id = p.tipo_empleado_id
            WHERE e.id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $empleadoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTipoEmpleadoFromId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tipos_empleado
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getEmpleados(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                e.id,
                e.nombre,
                e.email,
                te.tipo,
                e.estado
            FROM empleados e
            JOIN tipos_empleado te
                ON e.tipo_empleado_id = te.id
            ORDER BY e.nombre
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmpleadoByEmail(string $email): ?Empleado
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM empleados
            WHERE email = :email
        ");
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$row) {
            return null;
        }

        return new Empleado(
            id: $row['id'],
            tipoEmpleadoId: $row['tipo_empleado_id'],
            nombre: $row['nombre'],
            email: $row['email'],
            clave: $row['clave'],
        );
    }

    public function getEmpleadoById(int $id): ?Empleado
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM empleados
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$row) {
            return null;
        }

        return new Empleado(
            id: $row['id'],
            tipoEmpleadoId: $row['tipo_empleado_id'],
            nombre: $row['nombre'],
            email: $row['email'],
            clave: $row['clave'],
        );
    }

    public function getEmpleadoEstadoById(int $id): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT estado
            FROM empleados
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $estado = $stmt->fetchColumn();

        return $estado !== false ? (string) $estado : null;
    }

    public function updateEstado(int $id, string $estado): int
    {
        $stmt = $this->pdo->prepare("
            UPDATE empleados
            SET estado = :estado
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'estado' => $estado
        ]);

        return $stmt->rowCount();
    }

    public function getIngresosPorPeriodo(string $from, string $to): array
    {
        $sql = "
            SELECT e.id, e.nombre, i.hora_ingreso
            FROM ingresos i
            JOIN empleados e ON e.id = i.empleado_id
            WHERE i.hora_ingreso BETWEEN :from AND :to
            ORDER BY i.hora_ingreso ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumenIngresosPorEmpleado(string $from, string $to): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                e.id AS empleado_id,
                COUNT(i.id) AS cantidad_ingresos,
                MIN(i.hora_ingreso) AS primer_ingreso,
                MAX(i.hora_ingreso) AS ultimo_ingreso
            FROM empleados e
            LEFT JOIN ingresos i
                ON i.empleado_id = e.id
               AND i.hora_ingreso BETWEEN :from AND :to
            GROUP BY e.id
        ");

        $stmt->execute([
            ':from' => $from,
            ':to' => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getOperacionesPorSector(string $from, string $to): array
    {
        $sql = "
            SELECT
                s.id AS sector_id,
                s.nombre AS sector,
                COUNT(*) AS total_operaciones
            FROM (
                SELECT
                    dp.empleado_asigno_id AS empleado_id,
                    pr.sector_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                JOIN productos pr ON pr.id = dp.producto_id
                WHERE dp.empleado_asigno_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_preparo_id AS empleado_id,
                    pr.sector_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                JOIN productos pr ON pr.id = dp.producto_id
                WHERE dp.empleado_preparo_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_entrego_id AS empleado_id,
                    pr.sector_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                JOIN productos pr ON pr.id = dp.producto_id
                WHERE dp.empleado_entrego_id IS NOT NULL
            ) op
            JOIN sectores s ON s.id = op.sector_id
            WHERE op.fecha BETWEEN :from AND :to
            GROUP BY s.id, s.nombre
            ORDER BY s.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOperacionesPorEmpleadoYSector(string $from, string $to): array
    {
        $sql = "
            SELECT
                e.id AS empleado_id,
                e.nombre AS empleado,
                s.id AS sector_id,
                s.nombre AS sector,
                COUNT(*) AS total_operaciones
            FROM (
                SELECT
                    dp.empleado_asigno_id AS empleado_id,
                    pr.sector_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                JOIN productos pr ON pr.id = dp.producto_id
                WHERE dp.empleado_asigno_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_preparo_id AS empleado_id,
                    pr.sector_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                JOIN productos pr ON pr.id = dp.producto_id
                WHERE dp.empleado_preparo_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_entrego_id AS empleado_id,
                    pr.sector_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                JOIN productos pr ON pr.id = dp.producto_id
                WHERE dp.empleado_entrego_id IS NOT NULL
            ) op
            JOIN empleados e ON e.id = op.empleado_id
            JOIN sectores s ON s.id = op.sector_id
            WHERE op.fecha BETWEEN :from AND :to
            GROUP BY e.id, e.nombre, s.id, s.nombre
            ORDER BY s.nombre, e.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOperacionesPorEmpleado(string $from, string $to): array
    {
        $sql = "
            SELECT
                e.id AS empleado_id,
                e.nombre AS empleado,
                COUNT(*) AS total_operaciones
            FROM (
                SELECT
                    dp.empleado_asigno_id AS empleado_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                WHERE dp.empleado_asigno_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_preparo_id AS empleado_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                WHERE dp.empleado_preparo_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_entrego_id AS empleado_id,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                WHERE dp.empleado_entrego_id IS NOT NULL
            ) op
            JOIN empleados e ON e.id = op.empleado_id
            WHERE op.fecha BETWEEN :from AND :to
            GROUP BY e.id, e.nombre
            ORDER BY e.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasEmpleados(string $from, string $to): array
    {
        $sql = "
            SELECT
                e.id,
                e.nombre,
                e.email,
                te.tipo,
                e.estado,
                COALESCE(SUM(CASE WHEN op.tipo_operacion = 'asignacion' THEN 1 ELSE 0 END), 0) AS asignaciones,
                COALESCE(SUM(CASE WHEN op.tipo_operacion = 'preparacion' THEN 1 ELSE 0 END), 0) AS preparaciones,
                COALESCE(SUM(CASE WHEN op.tipo_operacion = 'entrega' THEN 1 ELSE 0 END), 0) AS entregas
            FROM empleados e
            LEFT JOIN tipos_empleado te ON te.id = e.tipo_empleado_id
            LEFT JOIN (
                SELECT
                    dp.empleado_asigno_id AS empleado_id,
                    'asignacion' AS tipo_operacion,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                WHERE dp.empleado_asigno_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_preparo_id AS empleado_id,
                    'preparacion' AS tipo_operacion,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                WHERE dp.empleado_preparo_id IS NOT NULL

                UNION ALL

                SELECT
                    dp.empleado_entrego_id AS empleado_id,
                    'entrega' AS tipo_operacion,
                    p.hora_inicio AS fecha
                FROM detalles_pedido dp
                JOIN pedidos p ON p.id = dp.pedido_id
                WHERE dp.empleado_entrego_id IS NOT NULL
            ) op
                ON op.empleado_id = e.id
               AND op.fecha BETWEEN :from AND :to
            GROUP BY e.id, e.nombre, e.email, te.tipo, e.estado
            ORDER BY e.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumenIngresosParaPdf(): array
    {
        $sql = "
            SELECT
                e.id,
                e.nombre,
                e.email,
                te.tipo,
                e.estado,
                COALESCE(i.cantidad_ingresos, 0) AS cantidad_ingresos,
                i.primer_ingreso,
                i.ultimo_ingreso,
                COALESCE(o.total_operaciones, 0) AS total_operaciones
            FROM empleados e
            INNER JOIN tipos_empleado te
                ON te.id = e.tipo_empleado_id
            LEFT JOIN (
                SELECT
                    empleado_id,
                    COUNT(*) AS cantidad_ingresos,
                    MIN(hora_ingreso) AS primer_ingreso,
                    MAX(hora_ingreso) AS ultimo_ingreso
                FROM ingresos
                GROUP BY empleado_id
            ) i
                ON i.empleado_id = e.id
            LEFT JOIN (
                SELECT
                    empleado_id,
                    COUNT(*) AS total_operaciones
                FROM empleado_operaciones
                GROUP BY empleado_id
            ) o
                ON o.empleado_id = e.id
            ORDER BY e.nombre ASC
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmpleadoResumenById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                e.id,
                e.nombre,
                e.email,
                te.tipo,
                e.estado
            FROM empleados e
            JOIN tipos_empleado te
                ON te.id = e.tipo_empleado_id
            WHERE e.id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function getEmpleadoAuthDataByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                tipo_empleado_id,
                nombre,
                email,
                clave,
                estado
            FROM empleados
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            'email' => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}