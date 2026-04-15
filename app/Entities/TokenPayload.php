<?php
namespace App\Entities;

class TokenPayload {
    public function __construct(
        public int $id,
        public string $nombre,
        public string $email,
        public string $rol
    ) {}
    
    public static function fromArray(array $data): self {

        return new self(
            $data['id'],
            $data['nombre'],
            $data['email'],
            $data['rol']
        );
    }
}

?>
