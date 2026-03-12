<?php
namespace App\Services;

use App\Repositories\EmpleadoRepository;
use App\DTO\Response\EmpleadoStatsDTO;

final class EmpleadoQueryService
{
    public function __construct(
        private EmpleadoRepository $empleadoRepo
    ) {}

    public function estadisticas(string $from, string $to): array
    {
        $rows = $this->empleadoRepo->getEstadisticasEmpleados($from, $to);

        $result = [];

        foreach ($rows as $row) {

            $asignaciones  = (int)$row['asignaciones'];
            $preparaciones = (int)$row['preparaciones'];
            $entregas      = (int)$row['entregas'];

            $total = $asignaciones + $preparaciones + $entregas;

            $result[] = new EmpleadoStatsDTO(
                empleadoId: (int)$row['id'],
                nombre: $row['nombre'],
                email: $row['email'],
                tipo: $row['tipo'],
                asignaciones: $asignaciones,
                preparaciones: $preparaciones,
                entregas: $entregas,
                totalOperaciones: $total
            );
        }

        return $result;
    }
}