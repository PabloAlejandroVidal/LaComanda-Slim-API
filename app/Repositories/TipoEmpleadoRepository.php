<?php
namespace App\Repositories;

use App\DTO\EmpleadoInput;
use App\DTO\EmpleadoToken;
use App\Entities\Empleado;
use App\Entities\TipoEmpleado;
use App\Services\Utils;
use Exception;
use PDO;

class TipoEmpleadoRepository {
    
    public function __construct(private PDO $pdo) {}

    public function getTipoByString(string $tipo): ?TipoEmpleado {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM tipos_empleado
            WHERE tipo = :tipo
        ");
        $stmt->execute(['email' => $tipo]);        
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        
        if (!$row) return null;
        return new TipoEmpleado(
            id: $row['id'],
            tipo: $row['tipo']
        );
    }
    public function getTipoById(int $id): ?TipoEmpleado {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM tipos_empleado
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        
        if (!$row) return null;
        return new TipoEmpleado(
            id: $row['id'],
            tipo: $row['tipo']
        );
    }
}
