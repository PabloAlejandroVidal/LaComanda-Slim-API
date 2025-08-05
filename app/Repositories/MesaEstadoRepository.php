<?php
namespace App\Repositories;

use App\Entities\MesaEstado;
use PDO;
use PDOException;
use Exception;

class MesaEstadoRepository
{
    public function __construct(private PDO $pdo) {}

    public function getEstadoById(int $estadoId): ?MesaEstado
    {
        $stmt = $this->pdo->prepare(
            "SELECT nombre FROM mesa_estados WHERE id = :id");
        $stmt->execute([':id' => $estadoId]);
        $row = $stmt->fetchColumn() ?: null;
        
        if (!$row) return null;

        return new MesaEstado(
        $row['id'],
        $row['nombre'],
        $row['descripcion'],
        );
    }
    public function getEstadoByNombre(int $nombre): ?MesaEstado
    {
        $stmt = $this->pdo->prepare("SELECT * FROM mesa_estados WHERE nombre = :nombre");
        $stmt->execute([':nombre' => $nombre]);
        $row = $stmt->fetchColumn() ?: null;

        if (!$row) return null;

        return new MesaEstado(
        $row['id'],
        $row['nombre'],
        $row['descripcion'],
        );
    }
}
