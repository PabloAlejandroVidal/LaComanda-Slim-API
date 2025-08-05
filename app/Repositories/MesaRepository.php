<?php
namespace App\Repositories;

use PDO;
use PDOException;
use Exception;
use App\Repositories\MesaEstadoRepository;

class MesaRepository
{
    public function __construct(
        private PDO $pdo,
        private MesaEstadoRepository $mesaEstadoRepo) {}

    /*-------------------------------------------------
    | Obtener mesas (todas o una)
    -------------------------------------------------*/
    public function get(?int $id = null): array
    {
        try {
            if ($id === null) {
                $stmt = $this->pdo->query("SELECT * FROM mesas");
            } else {
                $stmt = $this->pdo->prepare("SELECT * FROM mesas WHERE id = :id");
                $stmt->execute([':id' => $id]);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al consultar mesas: " . $e->getMessage());
        }
    }

    /*-------------------------------------------------
    | Alta de mesa
    -------------------------------------------------*/
  public function add(int $id): int
{
    try {
        $stmt = $this->pdo->prepare("INSERT INTO mesas (id, estado) VALUES (:id, :estado)");
        $stmt->execute([':id' => $id, ':estado' => 'libre']);
        return $id;
    } catch (PDOException $e) {
        throw new Exception("No se pudo agregar la mesa $id: " . $e->getMessage());
    }
}


    /*-------------------------------------------------
    | ¿Existe la mesa?
    -------------------------------------------------*/
    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM mesas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    /*-------------------------------------------------
    | ¿La mesa está libre? (sin pedidos abiertos)
    -------------------------------------------------*/
    public function isFree($mesaId): bool
    {
        $sql = "SELECT 1
                FROM mesas
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $mesaId]);

        // Si no hay filas, la mesa está libre
        return $stmt->fetchColumn() === false;
    }

    public function setEstado(int $mesaId, int $estadoId): void
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE mesas SET estado = :estado WHERE id = :id");
            $stmt->execute([
                ':estado' => $estadoId,
                ':id'     => $mesaId
            ]);
        } catch (PDOException $e) {
            throw new Exception("No se pudo actualizar el estado de la mesa $mesaId: " . $e->getMessage());
        }
    }

    public function getMesa(int $mesaId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT m.id, m.estado as estado_id, me.nombre as estado, m.descripcion FROM mesas m
        JOIN mesa_estados me ON me.id = m.estado WHERE id = :id");
        $stmt->execute([':id' => $mesaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMesas(): ?array
    {
        $stmt = $this->pdo->prepare("SELECT m.id, me.nombre as estado, p.id as pedido
        FROM mesas m
        JOIN mesa_estados me ON me.id = m.estado 
        LEFT JOIN pedidos p ON p.mesa_id = m.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }
}
