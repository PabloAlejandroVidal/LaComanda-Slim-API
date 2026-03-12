<?php
namespace App\Repositories;

use Exception;
use PDO;

class PedidoRepository
{
    public function __construct(private PDO $pdo) {}

    /* ===============================
       BASICOS
    =============================== */

    public function getPedidoById(string $pedidoId): mixed
    {
        $stmt = $this->pdo->prepare("SELECT * FROM pedidos WHERE id = :pedido");
        $stmt->execute([':pedido' => $pedidoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearPedido(
        string $pedidoId,
        int $empleadoId,
        int $mesaId,
        string $nombreCliente,
        string $hora
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO pedidos (id, mesa_id, nombre_cliente, empleado_inicio_id, hora_inicio)
            VALUES (:id, :mesa, :nombre, :empleado_id, :hora)
        ");

        $success = $stmt->execute([
            ':id'          => $pedidoId,
            ':mesa'        => $mesaId,
            ':nombre'      => $nombreCliente,
            ':empleado_id' => $empleadoId,
            ':hora'        => $hora
        ]);

        if (!$success) {
            throw new Exception("No se pudo agregar el pedido {$pedidoId}");
        }

        return (int) $this->pdo->lastInsertId();
    }

    public function getMesaId(string $pedidoId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT mesa_id FROM pedidos 
            WHERE id = :pedido
        ");
        $stmt->execute([':pedido' => $pedidoId]);

        $mesaId = $stmt->fetchColumn();

        if ($mesaId === false) {
            throw new Exception("Pedido {$pedidoId} no encontrado");
        }

        return (int) $mesaId;
    }

    public function getMonto(string $pedidoId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(dp.cantidad * pr.precio) AS total
            FROM detalles_pedido dp
            JOIN productos pr ON pr.id = dp.producto_id
            WHERE dp.pedido_id = :pedido
        ");

        $stmt->execute([':pedido' => $pedidoId]);

        return (int) $stmt->fetchColumn();
    }

    public function cerrarPedido(
        string $pedidoId,
        int $empleadoId,
        string $horaPago,
        int $importe
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE pedidos 
            SET empleado_cierre_id = :empleado_id,
                hora_pago = :hora_pago,
                importe = :importe
            WHERE id = :id
        ");

        $stmt->execute([
            ':id'          => $pedidoId,
            ':empleado_id' => $empleadoId,
            ':hora_pago'   => $horaPago,
            ':importe'     => $importe,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("No se pudo cerrar el pedido {$pedidoId}");
        }
    }

    /* ===============================
       ESTADISTICAS PEDIDOS
    =============================== */

    public function getMasVendido(string $from, string $to): ?array
    {
        $sql = "
            SELECT 
                pr.id,
                pr.nombre,
                SUM(dp.cantidad) AS total_vendido
            FROM detalles_pedido dp
            JOIN productos pr ON pr.id = dp.producto_id
            JOIN pedidos p ON p.id = dp.pedido_id
            WHERE p.hora_inicio BETWEEN :from AND :to
              AND p.hora_pago IS NOT NULL
            GROUP BY pr.id, pr.nombre
            ORDER BY total_vendido DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getMenosVendido(string $from, string $to): ?array
    {
        $sql = "
            SELECT 
                pr.id,
                pr.nombre,
                SUM(dp.cantidad) AS total_vendido
            FROM detalles_pedido dp
            JOIN productos pr ON pr.id = dp.producto_id
            JOIN pedidos p ON p.id = dp.pedido_id
            WHERE p.hora_inicio BETWEEN :from AND :to
              AND p.hora_pago IS NOT NULL
            GROUP BY pr.id, pr.nombre
            ORDER BY total_vendido ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getPedidosFueraDeTiempo(string $from, string $to): array
    {
        // Asume que existe tiempo_estimado y hora_entrega_real
        $sql = "
            SELECT *
            FROM pedidos
            WHERE hora_inicio BETWEEN :from AND :to
              AND hora_pago IS NOT NULL
              AND hora_entrega_real > tiempo_estimado
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCancelados(string $from, string $to): array
    {
        // Ajustar si usás otro criterio de cancelación
        $sql = "
            SELECT *
            FROM pedidos
            WHERE hora_inicio BETWEEN :from AND :to
              AND hora_pago IS NULL
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':from' => $from,
            ':to'   => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}