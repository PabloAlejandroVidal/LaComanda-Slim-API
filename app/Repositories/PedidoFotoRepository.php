<?php

namespace App\Repositories;

use PDO;

final class PedidoFotoRepository
{
    public function __construct(private PDO $pdo) {}

    public function existsByPedidoId(string $pedidoId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM pedido_fotos
            WHERE pedido_id = :pedido_id
            LIMIT 1
        ");

        $stmt->execute([
            'pedido_id' => $pedidoId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(
        string $pedidoId,
        string $rutaArchivo,
        ?string $nombreOriginal,
        ?string $mimeType,
        ?int $tamanoBytes,
        int $empleadoId,
        string $fechaCreacion
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO pedido_fotos (
                pedido_id,
                ruta_archivo,
                nombre_original,
                mime_type,
                tamano_bytes,
                empleado_id,
                fecha_creacion
            ) VALUES (
                :pedido_id,
                :ruta_archivo,
                :nombre_original,
                :mime_type,
                :tamano_bytes,
                :empleado_id,
                :fecha_creacion
            )
        ");

        $stmt->execute([
            'pedido_id'       => $pedidoId,
            'ruta_archivo'    => $rutaArchivo,
            'nombre_original' => $nombreOriginal,
            'mime_type'       => $mimeType,
            'tamano_bytes'    => $tamanoBytes,
            'empleado_id'     => $empleadoId,
            'fecha_creacion'  => $fechaCreacion,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByPedidoId(string $pedidoId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                pedido_id,
                ruta_archivo,
                nombre_original,
                mime_type,
                tamano_bytes,
                empleado_id,
                fecha_creacion
            FROM pedido_fotos
            WHERE pedido_id = :pedido_id
            LIMIT 1
        ");

        $stmt->execute([
            'pedido_id' => $pedidoId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function deleteByPedidoId(string $pedidoId): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM pedido_fotos
            WHERE pedido_id = :pedido_id
        ");

        $stmt->execute([
            'pedido_id' => $pedidoId,
        ]);
    }
}