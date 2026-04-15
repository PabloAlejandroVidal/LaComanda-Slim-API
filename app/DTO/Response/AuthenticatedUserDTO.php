<?php
namespace App\DTO\Response;

class AuthenticatedUserDTO implements \JsonSerializable
{
    public function __construct(
        readonly public int $id,
        readonly public string $nombre,
        readonly public string $email,
        readonly public RolResponseDTO $rol,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
        ];
    }
}