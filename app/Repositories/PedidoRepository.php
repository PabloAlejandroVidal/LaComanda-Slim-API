<?php
namespace App\Repositories;

use App\Services\Utils;
use DateTime;
use Exception;
use PDO;

class PedidoRepository
{
    public function __construct(private PDO $pdo) {}


    public function getPedidoById(int $pedidoId): mixed {
        $stmt = $this->pdo->prepare("SELECT * FROM pedidos WHERE id = :pedido");
        $stmt->execute([':pedido' => $pedidoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function crearPedido(int $pedidoId, int $empleadoId, int $mesaId, string $nombreCliente, string $hora): void {

        $stmt = $this->pdo->prepare("
            INSERT INTO pedidos (id, mesa_id, nombre_cliente, empleado_inicio_id, hora_inicio)
            VALUES (:id, :mesa, :nombre, :empleado_id, :hora)
        ");
        $success = $stmt->execute([
            ':id'         => $pedidoId,
            ':mesa'       => $mesaId,
            ':nombre'     => $nombreCliente,
            ':empleado_id'=> $empleadoId,
            ':hora'       => $hora
        ]);

        if (!$success) {
            throw new Exception("No se pudo agregar el pedido para la mesa {$mesaId}");
        }
    }

    public function isValid(int $pedidoId): bool {
        $stmt = $this->pdo->prepare("
            SELECT 1 FROM pedidos 
            WHERE id = :pedido 
            LIMIT 1
        ");
        $stmt->execute([':pedido' => $pedidoId]);
        return $stmt->fetchColumn() !== false;
    }
    public function getMesaId(int $pedidoId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT mesa_id FROM pedidos 
            WHERE id = :pedido 
            LIMIT 1
        ");
        $stmt->execute([':pedido' => $pedidoId]);
        $mesaId = $stmt->fetchColumn();

        if ($mesaId === false) {
            throw new Exception("No se encontró el pedido con ID: {$pedidoId}");
        }
        return (int) $mesaId;
    }
    public function getMonto(int $pedidoId): int
    {
        $stmt = $this->pdo->prepare("SELECT SUM(dp.cantidad * p.precio) AS total
        FROM detalles_pedido dp
		JOIN productos p ON p.id = dp.producto_id
		WHERE dp.pedido_id = :pedido 
        ");
        $stmt->execute([':pedido' => $pedidoId]);
        $monto = $stmt->fetchColumn();

        return (int) $monto;
    }

    public function cerrarPedido(int $pedidoId, int $empleadoId, string $horaPago, int $importe): void
    {
        $stmt = $this->pdo->prepare("UPDATE pedidos 
            SET empleado_cierre_id = :empleado_id,
            hora_pago = :hora_pago,
            importe = :importe
            WHERE id = :id

        ");
        $stmt->execute([
            ':id'          => $pedidoId,
            ':empleado_id'          => $empleadoId,
            ':hora_pago' => $horaPago,
            ':importe'          => $importe,
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("No se pudo cerrar el pedido con ID: {$pedidoId}");
        }
    }

}
