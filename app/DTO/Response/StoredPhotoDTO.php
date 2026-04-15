<?php

namespace App\DTO\Response;

final class StoredPhotoDTO
{
    public function __construct(
        public string $ruta,
        public ?string $nombreOriginal,
        public ?string $mimeType,
        public ?int $tamanoBytes
    ) {}
}