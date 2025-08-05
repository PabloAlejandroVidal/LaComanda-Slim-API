<?php
namespace App\Entities;

class MesaEstado {
    public function __construct(
        public int $id,
        public string $nombre,
        public string $descripcion,
    ) {}
}

?>
