<?php
namespace App\Entities;

class Empleado {
    public function __construct(
        public int $id,
        public int $tipoEmpleadoId,
        public string $nombre,
        public string $email,
        public string $clave,
    ) {}
}

?>
