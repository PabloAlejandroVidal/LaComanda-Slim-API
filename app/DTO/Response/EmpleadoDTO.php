<?php
namespace App\DTO;

class EmpleadoDTO {
    public function __construct(
        public string $nombre,
        public string $email,
        public string $tipo
    ) {}
}

?>
