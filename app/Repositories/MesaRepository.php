<?php
namespace App\Repositories;

use App\Domain\Mesa\EstadoMesa;
use PDO;
use PDOException;
use Exception;

class MesaRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    /*-------------------------------------------------
    | Obtener mesas (todas o una)
    -------------------------------------------------*/
    public function get(?string $id = null): array
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
    public function add(string $id): string
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO mesas (id, estado) 
                 VALUES (
                    :id,
                    (SELECT id FROM mesa_estados WHERE nombre = :nombre)
                 )"
            );

            $stmt->execute([
                ':id'     => $id,
                ':nombre' => EstadoMesa::CERRADA->value
            ]);

            return $id;

        } catch (PDOException $e) {
            throw new Exception("No se pudo agregar la mesa $id: " . $e->getMessage());
        }
    }

    /*-------------------------------------------------
    | ¿Existe la mesa?
    -------------------------------------------------*/
    public function exists(string $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM mesas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    /*-------------------------------------------------
    | Cambiar estado
    -------------------------------------------------*/
    public function setEstado(string $mesaId, EstadoMesa $estado): void
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE mesas 
                 SET estado = (
                    SELECT id FROM mesa_estados WHERE nombre = :nombre
                 )
                 WHERE id = :id"
            );

            $stmt->execute([
                ':nombre' => $estado->value,
                ':id'     => $mesaId
            ]);

        } catch (PDOException $e) {
            throw new Exception("No se pudo actualizar el estado de la mesa $mesaId: " . $e->getMessage());
        }
    }

    /*-------------------------------------------------
    | Obtener mesa individual
    -------------------------------------------------*/
    public function getMesa(string $mesaId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.id, me.nombre as estado
            FROM mesas m
            JOIN mesa_estados me ON me.id = m.estado
            WHERE m.id = :id
        ");

        $stmt->execute([':id' => $mesaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'id'     => $row['id'],
            'estado' => EstadoMesa::from($row['estado'])
        ];
    }

    /*-------------------------------------------------
    | Obtener todas con estado convertido a Enum
    -------------------------------------------------*/
    public function getMesas(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.id, me.nombre as estado
            FROM mesas m
            JOIN mesa_estados me ON me.id = m.estado
        ");

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['estado'] = EstadoMesa::from($row['estado']);
        }

        return $rows;
    }

    /*-------------------------------------------------
    | ESTADÍSTICAS
    -------------------------------------------------*/

    public function getMesaMasUsada(string $from, string $to): ?array
    {
        $sql = "
            SELECT m.id, COUNT(p.id) as total_usos
            FROM mesas m
            JOIN pedidos p ON p.mesa_id = m.id
            WHERE p.hora_inicio BETWEEN :from AND :to
            GROUP BY m.id
            ORDER BY total_usos DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMesaMenosUsada(string $from, string $to): ?array
    {
        $sql = "
            SELECT m.id, COUNT(p.id) as total_usos
            FROM mesas m
            JOIN pedidos p ON p.mesa_id = m.id
            WHERE p.hora_inicio BETWEEN :from AND :to
            GROUP BY m.id
            ORDER BY total_usos ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMesaQueMasFacturo(string $from, string $to): ?array
    {
        $sql = "
            SELECT m.id, SUM(p.importe) as total_facturado
            FROM mesas m
            JOIN pedidos p ON p.mesa_id = m.id
            WHERE p.hora_pago IS NOT NULL
              AND p.hora_inicio BETWEEN :from AND :to
            GROUP BY m.id
            ORDER BY total_facturado DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMesaQueMenosFacturo(string $from, string $to): ?array
    {
        $sql = "
            SELECT m.id, SUM(p.importe) as total_facturado
            FROM mesas m
            JOIN pedidos p ON p.mesa_id = m.id
            WHERE p.hora_pago IS NOT NULL
              AND p.hora_inicio BETWEEN :from AND :to
            GROUP BY m.id
            ORDER BY total_facturado ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMayorFactura(string $from, string $to): ?array
    {
        $sql = "
            SELECT mesa_id, importe
            FROM pedidos
            WHERE hora_pago IS NOT NULL
              AND hora_inicio BETWEEN :from AND :to
            ORDER BY importe DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMenorFactura(string $from, string $to): ?array
    {
        $sql = "
            SELECT mesa_id, importe
            FROM pedidos
            WHERE hora_pago IS NOT NULL
              AND hora_inicio BETWEEN :from AND :to
            ORDER BY importe ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getFacturacionTotal(string $from, string $to): float
    {
        $sql = "
            SELECT SUM(importe) as total
            FROM pedidos
            WHERE hora_pago IS NOT NULL
              AND hora_inicio BETWEEN :from AND :to
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return (float) $stmt->fetchColumn();
    }
}