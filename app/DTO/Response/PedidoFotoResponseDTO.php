<?php

namespace App\DTO\Response;

final class PedidoFotoResponseDTO
{
    public function __construct(
        public string $pedidoId,
        public string $fotoUrl,
        public ?string $mimeType,
        public ?int $tamanoBytes
    ) {}
}