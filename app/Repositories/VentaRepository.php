<?php
namespace App\Repositories;

use Exception;
use PDO;

class VentaRepository {    
        public function __construct(private PDO $pdo) {
    }
    public function getVentas() {
        $stmt = $this->pdo->prepare("SELECT d.producto_id, d.cantidad
        FROM detalles_pedido d
        JOIN productos p ON d.producto_id = p.id
        WHERE d.estado = 'listo'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
