<?php
namespace App\Repositories;

use PDO;

class ProductoRepository
{
    public function __construct(private PDO $pdo) {}

    public function agregarProducto(
        string $nombre,
        int $sectorId,
        float $precio,
        int $cantidad
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO productos (nombre, sector_id, precio, cantidad)
            VALUES (:nombre, :sector_id, :precio, :cantidad)
        ");

        $stmt->execute([
            ':nombre' => $nombre,
            ':sector_id' => $sectorId,
            ':precio' => $precio,
            ':cantidad' => $cantidad,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getProductoById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                nombre,
                sector_id,
                precio,
                cantidad
            FROM productos
            WHERE id = :id
        ");

        $stmt->execute([':id' => $id]);

        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        return $producto ?: null;
    }

    public function getAllProductos(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                id,
                nombre,
                sector_id,
                precio,
                cantidad
            FROM productos
            ORDER BY id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarProducto(
        int $id,
        string $nombre,
        int $sectorId,
        float $precio,
        int $cantidad
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE productos
            SET nombre = :nombre,
                sector_id = :sector_id,
                precio = :precio,
                cantidad = :cantidad
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':sector_id' => $sectorId,
            ':precio' => $precio,
            ':cantidad' => $cantidad,
        ]);
    }

    public function eliminarProducto(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM productos
            WHERE id = :id
        ");

        return $stmt->execute([':id' => $id]);
    }

    public function buscarProductosPorNombre(string $nombre): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                nombre,
                sector_id,
                precio,
                cantidad
            FROM productos
            WHERE nombre LIKE :nombre
            ORDER BY nombre ASC
        ");

        $stmt->execute([
            ':nombre' => "%{$nombre}%",
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function modificarStock(int $productoId, int $cantidad): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE productos
            SET cantidad = cantidad + :cantidad
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $productoId,
            ':cantidad' => $cantidad,
        ]);
    }

    public function productoNombreExiste(string $nombre): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM productos
            WHERE nombre = :nombre
        ");

        $stmt->execute([':nombre' => $nombre]);

        return (int) $stmt->fetchColumn() > 0;
    }

        public function getAllForCsv(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                p.nombre,
                LOWER(s.clave) AS sector,
                p.precio,
                p.cantidad
            FROM productos p
            INNER JOIN sectores s ON s.id = p.sector_id
            ORDER BY p.nombre ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsByNombreAndSector(string $nombre, int $sectorId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM productos
            WHERE nombre = :nombre
              AND sector_id = :sector_id
            LIMIT 1
        ");

        $stmt->execute([
            'nombre' => $nombre,
            'sector_id' => $sectorId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function createFromCsv(
        string $nombre,
        int $sectorId,
        float $precio,
        int $cantidad
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO productos (nombre, sector_id, precio, cantidad)
            VALUES (:nombre, :sector_id, :precio, :cantidad)
        ");

        return $stmt->execute([
            'nombre' => $nombre,
            'sector_id' => $sectorId,
            'precio' => $precio,
            'cantidad' => $cantidad,
        ]);
    }

    public function getAllForPdf(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                p.nombre,
                s.nombre AS sector,
                p.precio,
                p.cantidad
            FROM productos p
            INNER JOIN sectores s ON s.id = p.sector_id
            ORDER BY p.nombre ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}