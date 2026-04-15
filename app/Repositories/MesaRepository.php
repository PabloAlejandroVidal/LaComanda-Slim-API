<?php
namespace App\Repositories;

use App\Domain\Mesa\EstadoMesa;
use PDO;

class MesaRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function add(string $id): string
    {
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
    }

    public function exists(string $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM mesas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function setEstado(string $mesaId, EstadoMesa $estado): void
    {
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
    }

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

    public function getMesas(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                m.id,
                me.nombre AS estado,
                me.descripcion AS estado_descripcion
            FROM mesas m
            JOIN mesa_estados me ON me.id = m.estado
            ORDER BY m.id ASC
        ");

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['estado'] = EstadoMesa::from($row['estado']);
        }

        return $rows;
    }

    public function getMesaMasUsada(string $from, string $to): ?array
    {
        $sql = "
            SELECT 
                m.id,
                COUNT(p.id) AS total_usos
            FROM mesas m
            INNER JOIN pedidos p 
                ON p.mesa_id = m.id
            WHERE p.hora_inicio BETWEEN :from AND :to
            GROUP BY m.id
            ORDER BY total_usos DESC, m.id ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMesaMenosUsada(string $from, string $to): ?array
    {
        $sql = "
            SELECT 
                m.id,
                COUNT(p.id) AS total_usos
            FROM mesas m
            INNER JOIN pedidos p 
                ON p.mesa_id = m.id
            WHERE p.hora_inicio BETWEEN :from AND :to
            GROUP BY m.id
            ORDER BY total_usos ASC, m.id ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMesaMayorFacturacion(string $from, string $to): ?array
    {
        $sql = "
            SELECT m.id,
                SUM(p.importe) as total_facturado
            FROM mesas m
            JOIN pedidos p ON p.mesa_id = m.id
            WHERE p.hora_pago BETWEEN :from AND :to
            GROUP BY m.id
            ORDER BY total_facturado DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMesaMenorFacturacion(string $from, string $to): ?array
    {
        $sql = "
            SELECT m.id,
                SUM(p.importe) as total_facturado
            FROM mesas m
            JOIN pedidos p ON p.mesa_id = m.id
            WHERE p.hora_pago BETWEEN :from AND :to
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
            WHERE hora_pago BETWEEN :from AND :to
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
            WHERE hora_pago BETWEEN :from AND :to
            ORDER BY importe ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getFacturacionEntre(string $from, string $to): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_pedidos,
                COALESCE(SUM(importe), 0) as total_facturado,
                COALESCE(AVG(importe), 0) as promedio
            FROM pedidos
            WHERE hora_pago BETWEEN :from AND :to
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_pedidos' => 0,
            'total_facturado' => 0,
            'promedio' => 0,
        ];
    }

    public function getMejoresComentarios(string $from, string $to): array
    {
        $sql = "
            SELECT 
                p.mesa_id,
                e.comentario,
                (
                    (COALESCE(e.puntaje_mesa,0) +
                    COALESCE(e.puntaje_restaurante,0) +
                    COALESCE(e.puntaje_mozo,0) +
                    COALESCE(e.puntaje_cocinero,0)) / 4
                ) as promedio
            FROM encuestas e
            JOIN pedidos p ON p.id = e.pedido_id
            WHERE p.hora_pago BETWEEN :from AND :to
            AND e.comentario IS NOT NULL
            ORDER BY promedio DESC
            LIMIT 5
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeoresComentarios(string $from, string $to): array
    {
        $sql = "
            SELECT 
                p.mesa_id,
                e.comentario,
                (
                    (COALESCE(e.puntaje_mesa,0) +
                    COALESCE(e.puntaje_restaurante,0) +
                    COALESCE(e.puntaje_mozo,0) +
                    COALESCE(e.puntaje_cocinero,0)) / 4
                ) as promedio
            FROM encuestas e
            JOIN pedidos p ON p.id = e.pedido_id
            WHERE p.hora_pago BETWEEN :from AND :to
            AND e.comentario IS NOT NULL
            ORDER BY promedio ASC
            LIMIT 5
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}