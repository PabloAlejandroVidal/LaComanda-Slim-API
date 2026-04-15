<?php
namespace App\DTO\Response;

class RolResponseDTO implements \JsonSerializable
{
    public function __construct(
        readonly public int $id,
        readonly public string $nombre
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
        ];
    }
}