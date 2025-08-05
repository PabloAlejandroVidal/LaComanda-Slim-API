<?php
namespace App\Entities;

class TipoEmpleado {
    public function __construct(
        public int $id,
        public string $tipo,
    ) {}
}

?>
