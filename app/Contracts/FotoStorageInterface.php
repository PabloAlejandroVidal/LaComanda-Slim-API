<?php

namespace App\Contracts;

use App\DTO\Response\StoredPhotoDTO;
use Psr\Http\Message\UploadedFileInterface;

interface FotoStorageInterface
{
    public function store(UploadedFileInterface $file, string $pedidoId): StoredPhotoDTO;

    public function delete(string $rutaArchivo): void;
}