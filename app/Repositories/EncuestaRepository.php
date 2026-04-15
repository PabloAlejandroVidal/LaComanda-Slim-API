<?php

namespace App\Repositories;

use App\DTO\Request\EncuestaRequest;
use PDO;

final class EncuestaRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function existsByPedidoId(string $pedidoId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT 1
            FROM encuestas
            WHERE pedido_id = :pedido_id
            LIMIT 1
        ');

        $stmt->execute([
            'pedido_id' => $pedidoId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(string $pedidoId, EncuestaRequest $request): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO encuestas (
                pedido_id,
                puntaje_mesa,
                puntaje_restaurante,
                puntaje_mozo,
                puntaje_cocinero,
                comentario
            ) VALUES (
                :pedido_id,
                :puntaje_mesa,
                :puntaje_restaurante,
                :puntaje_mozo,
                :puntaje_cocinero,
                :comentario
            )
        ');

        return $stmt->execute([
            'pedido_id' => $pedidoId,
            'puntaje_mesa' => $request->puntajeMesa,
            'puntaje_restaurante' => $request->puntajeRestaurante,
            'puntaje_mozo' => $request->puntajeMozo,
            'puntaje_cocinero' => $request->puntajeCocinero,
            'comentario' => $request->comentario,
        ]);
    }

    public function findByPedidoId(string $pedidoId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT
                pedido_id,
                puntaje_mesa,
                puntaje_restaurante,
                puntaje_mozo,
                puntaje_cocinero,
                comentario
            FROM encuestas
            WHERE pedido_id = :pedido_id
            LIMIT 1
        ');

        $stmt->execute([
            'pedido_id' => $pedidoId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'pedidoId' => $row['pedido_id'],
            'puntajeMesa' => (int) $row['puntaje_mesa'],
            'puntajeRestaurante' => (int) $row['puntaje_restaurante'],
            'puntajeMozo' => (int) $row['puntaje_mozo'],
            'puntajeCocinero' => (int) $row['puntaje_cocinero'],
            'comentario' => $row['comentario'],
        ];
    }
}