<?php
namespace App\Entities;

class EmpleadoEntity {
    public function __construct(
        public int $id,
        public int $idTipoEmpleado,
        public string $nombre,
        public string $email,
        public string $clave,
    ) {}
}

?>
