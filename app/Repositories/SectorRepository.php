<?php
namespace App\Repositories;

use App\Domain\Sector\Sector;
use App\Domain\Sector\SectorNombre;
use Exception;
use PDO;

class SectorRepository {    
        public function __construct(private PDO $pdo) {
    }
    public function getByClave(SectorNombre $clave): ?Sector
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, nombre, descripcion, clave
            FROM sectores
            WHERE clave = :clave"
        );

        $stmt->execute(['clave' => $clave->value]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Sector(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            descripcion: $row['descripcion'],
            clave: SectorNombre::from($row['clave'])
        );
    }

    public function findIdByNombre(string $nombre): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM sectores
            WHERE LOWER(nombre) = LOWER(:nombre)
            LIMIT 1
        ");

        $stmt->execute([
            'nombre' => $nombre,
        ]);

        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }
    public function findIdByClave(string $clave): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM sectores
            WHERE LOWER(clave) = LOWER(:clave)
            LIMIT 1
        ");

        $stmt->execute([
            'clave' => $clave,
        ]);

        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }
}
