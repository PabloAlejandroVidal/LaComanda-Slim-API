<?php
namespace App\Repositories;

use PDO;
use PDOException;
use Exception;

class PermisoRepository
{
    public function __construct(private PDO $pdo) {}

        public function puedeAcceder($empleadoId, $sectorId): bool
    {
        $sql = "SELECT 1
                FROM permisos
                WHERE tipo_empleado_id = :empleado_id
                AND sector_id = :sector_id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tipo_empleado_id' => $empleadoId,
            ':empleado_id' => $sectorId
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
