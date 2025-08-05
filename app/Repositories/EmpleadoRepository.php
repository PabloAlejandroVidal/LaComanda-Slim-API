<?php
namespace App\Repositories;

use App\DTO\EmpleadoInput;
use App\DTO\EmpleadoToken;
use App\Entities\Empleado;
use App\Entities\EmpleadoEntity;
use App\Services\Utils;
use Exception;
use PDO;

class EmpleadoRepository {
    
    public function __construct(private PDO $pdo) {}

    public function emailExists(string $email): bool {
        $stmt = $this->pdo->prepare("
            SELECT 1 FROM empleados 
            WHERE email = :email 
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() !== false;
    }


    public function registrarIngreso(int $empleadoId): int {

        $horaActual = Utils::getHoraActual();

        $stmt = $this->pdo->prepare("
            INSERT INTO ingresos (empleado_id, hora_ingreso)
            VALUES (:empleado_id, :hora_ingreso)
        ");
        $stmt->bindValue(':empleado_id', $empleadoId);
        $stmt->bindValue(':hora_ingreso', $horaActual);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function registrarEmpleado(string $nombre, string $email, string $clave, int $tipoId): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO empleados (tipo_empleado_id, nombre, email, clave)
            VALUES (:tipo_empleado_id, :nombre, :email, :clave)
        ");
        $stmt->execute([
            'tipo_empleado_id' => $tipoId,
            'nombre'           => $nombre,
            'email'            => $email,
            'clave'            => $clave
        ]);

        return (int)$this->pdo->lastInsertId(); // ✅ Devuelve el ID generado
    }

    
    public function getSectoresByEmpleado(int $empleadoId): array
{
    $sql = "SELECT s.id, s.clave
        FROM sectores s
        JOIN permisos p ON p.sector_id = s.id
        JOIN empleados e ON e.tipo_empleado_id = p.tipo_empleado_id
        WHERE e.id = :id
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $empleadoId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getTipoEmpleadoFromId(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tipos_empleado 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getEmpleados(): array {
        $stmt = $this->pdo->prepare("
            SELECT empleados.id, nombre, email, tipo 
            FROM empleados 
            JOIN tipos_empleado ON empleados.tipo_empleado_id = tipos_empleado.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmpleadoByEmail(string $email): ?Empleado {
        $stmt = $this->pdo->prepare("
            SELECT * FROM empleados
            WHERE email = :email
        ");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$row) return null;
        return new Empleado(
            id: $row['id'],
            tipoEmpleadoId: $row['tipo_empleado_id'],
            nombre: $row['nombre'],
            email: $row['email'],
            clave: $row['clave'],
        );
    }
    public function getEmpleadoById(int $id): ?Empleado {
        $stmt = $this->pdo->prepare("
            SELECT * FROM empleados
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        
        if (!$row) return null;
        return new Empleado(
            id: $row['id'],
            tipoEmpleadoId: $row['tipo_empleado_id'],
            nombre: $row['nombre'],
            email: $row['email'],
            clave: $row['clave'],
        );
    }
}
