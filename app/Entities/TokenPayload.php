<?php
namespace App\DTO;

class TokenPayload {
    public function __construct(
        public int $id,
        public string $nombre,
        public string $email,
        public string $tipoEmpleadoId
    ) {}
}

?>
