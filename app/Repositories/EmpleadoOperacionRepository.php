<?php

namespace App\Repositories;

use App\DTO\Response\RegistrarOperacionDTO;
use PDO;

final class EmpleadoOperacionRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function registrar(RegistrarOperacionDTO $dto): int
    {
        $sql = "
            INSERT INTO empleado_operaciones (
                empleado_id,
                tipo_operacion,
                ambito,
                sector_id,
                pedido_id,
                mesa_id,
                observaciones,
                fecha_hora
            ) VALUES (
                :empleado_id,
                :tipo_operacion,
                :ambito,
                :sector_id,
                :pedido_id,
                :mesa_id,
                :observaciones,
                :fecha_hora
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':empleado_id'    => $dto->empleadoId,
            ':tipo_operacion' => $dto->tipoOperacion->value,
            ':ambito'         => $dto->ambito->value,
            ':sector_id'      => $dto->sectorId,
            ':pedido_id'      => $dto->pedidoId,
            ':mesa_id'        => $dto->mesaId,
            ':observaciones'  => $dto->observaciones,
            ':fecha_hora'     => $dto->fechaHora ?? date('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listarEntreFechas(string $from, string $to): array
    {
        $sql = "
            SELECT
                eo.id,
                eo.empleado_id,
                e.nombre AS empleado,
                eo.tipo_operacion,
                eo.ambito,
                eo.sector_id,
                s.nombre AS sector,
                eo.pedido_id,
                eo.mesa_id,
                eo.observaciones,
                eo.fecha_hora
            FROM empleado_operaciones eo
            JOIN empleados e
                ON e.id = eo.empleado_id
            LEFT JOIN sectores s
                ON s.id = eo.sector_id
            WHERE eo.fecha_hora BETWEEN :from AND :to
            ORDER BY eo.fecha_hora DESC, eo.id DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOperacionesPorSector(string $from, string $to): array
    {
        $sql = "
            SELECT
                eo.sector_id,
                s.nombre AS sector,
                COUNT(*) AS total_operaciones
            FROM empleado_operaciones eo
            JOIN sectores s
                ON s.id = eo.sector_id
            WHERE eo.fecha_hora BETWEEN :from AND :to
              AND eo.ambito = 'sector'
            GROUP BY eo.sector_id, s.nombre
            ORDER BY s.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOperacionesPorSectorYEmpleado(string $from, string $to): array
    {
        $sql = "
            SELECT
                eo.empleado_id,
                e.nombre AS empleado,
                eo.sector_id,
                s.nombre AS sector,
                COUNT(*) AS total_operaciones
            FROM empleado_operaciones eo
            JOIN empleados e
                ON e.id = eo.empleado_id
            JOIN sectores s
                ON s.id = eo.sector_id
            WHERE eo.fecha_hora BETWEEN :from AND :to
              AND eo.ambito = 'sector'
            GROUP BY eo.empleado_id, e.nombre, eo.sector_id, s.nombre
            ORDER BY s.nombre, e.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOperacionesPorEmpleado(string $from, string $to): array
    {
        $sql = "
            SELECT
                eo.empleado_id,
                e.nombre AS empleado,
                COUNT(*) AS total_operaciones
            FROM empleado_operaciones eo
            JOIN empleados e
                ON e.id = eo.empleado_id
            WHERE eo.fecha_hora BETWEEN :from AND :to
            GROUP BY eo.empleado_id, e.nombre
            ORDER BY e.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasPorEmpleado(string $from, string $to): array
    {
        $sql = "
            SELECT
                e.id,
                e.nombre,
                e.email,
                te.tipo,
                COALESCE(SUM(CASE WHEN eo.tipo_operacion = 'toma_pedido' THEN 1 ELSE 0 END), 0) AS tomas_pedido,
                COALESCE(SUM(CASE WHEN eo.tipo_operacion = 'asignacion_detalle' THEN 1 ELSE 0 END), 0) AS asignaciones,
                COALESCE(SUM(CASE WHEN eo.tipo_operacion = 'preparacion_detalle' THEN 1 ELSE 0 END), 0) AS preparaciones,
                COALESCE(SUM(CASE WHEN eo.tipo_operacion = 'entrega_pedido' THEN 1 ELSE 0 END), 0) AS entregas,
                COALESCE(SUM(CASE WHEN eo.tipo_operacion = 'cobro_mesa' THEN 1 ELSE 0 END), 0) AS cobros,
                COALESCE(SUM(CASE WHEN eo.tipo_operacion = 'cierre_mesa' THEN 1 ELSE 0 END), 0) AS cierres,
                COALESCE(SUM(CASE WHEN eo.tipo_operacion = 'cancelacion_pedido' THEN 1 ELSE 0 END), 0) AS cancelaciones,
                COALESCE(COUNT(eo.id), 0) AS total_operaciones
            FROM empleados e
            LEFT JOIN tipos_empleado te
                ON te.id = e.tipo_empleado_id
            LEFT JOIN empleado_operaciones eo
                ON eo.empleado_id = e.id
            AND eo.fecha_hora BETWEEN :from AND :to
            GROUP BY e.id, e.nombre, e.email, te.tipo
            ORDER BY e.nombre
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}