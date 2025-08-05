<?php
namespace App\Repositories;

use App\DTO\EmpleadoInput;
use App\DTO\EmpleadoToken;
use App\Entities\Empleado;
use App\Entities\EmpleadoEntity;
use App\Services\Utils;
use Exception;
use PDO;

class ProductoRepository {
    
    public function __construct(private PDO $pdo) {}

    public function agregarProducto(string $nombre, string $sectorId, string $precio, int $cantidad): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO productos (nombre, sector_id, precio, cantidad)
            VALUES (:nombre, :sector, :precio, :cantidad)
        ");
        $stmt->execute([
            'nombre'   => $nombre,
            'sector'   => $sectorId,
            'precio'   => $precio,
            'cantidad' => $cantidad
        ]);

        return (int)$this->pdo->lastInsertId();
    }
    public function getProductoById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $producto ?: null;
    }
    public function getAllProductos(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM productos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function actualizarProducto(int $id, string $nombre, string $sectorId, string $precio, int $cantidad): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE productos
            SET nombre = :nombre, sector_id = :sector, precio = :precio, cantidad = :cantidad
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'       => $id,
            'nombre'   => $nombre,
            'sector'   => $sectorId,
            'precio'   => $precio,
            'cantidad' => $cantidad
        ]);
    }
    public function eliminarProducto(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    public function buscarProductosPorNombre(string $nombre): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE nombre LIKE :nombre");
        $stmt->execute(['nombre' => "%$nombre%"]);
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
            'id'       => $productoId,
            'cantidad' => $cantidad
        ]);
    }
    public function productoNombreExiste(string $nombre): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM productos WHERE nombre = :nombre");
        $stmt->execute(['nombre' => $nombre]);
        $count = (int)$stmt->fetchColumn();

        return $count > 0;
    }


}
