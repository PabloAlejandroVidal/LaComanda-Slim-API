<?php

namespace App\Infrastructure;

use App\Contracts\FotoStorageInterface;
use App\DTO\Response\StoredPhotoDTO;
use App\Exceptions\InternalServerException;
use Psr\Http\Message\UploadedFileInterface;

final class LocalFotoStorage implements FotoStorageInterface
{
    public function __construct(
        private string $storageDir,
        private string $publicBasePath
    ) {}

    public function store(UploadedFileInterface $file, string $pedidoId): StoredPhotoDTO
    {
        if (!is_dir($this->storageDir) && !mkdir($concurrentDirectory = $this->storageDir, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new InternalServerException('No se pudo crear el directorio de almacenamiento de fotos');
        }

        if (!is_writable($this->storageDir)) {
            throw new InternalServerException('El directorio de almacenamiento de fotos no tiene permisos de escritura');
        }

        $originalName = $file->getClientFilename();
        $mimeType = $file->getClientMediaType();
        $size = $file->getSize();

        $extension = $this->resolveExtension($originalName, $mimeType);
        $fileName = $this->buildFileName($pedidoId, $extension);
        $targetPath = rtrim($this->storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        $file->moveTo($targetPath);

        return new StoredPhotoDTO(
            ruta: rtrim($this->publicBasePath, '/') . '/' . $fileName,
            nombreOriginal: $originalName,
            mimeType: $mimeType,
            tamanoBytes: $size
        );
    }

    public function delete(string $rutaArchivo): void
    {
        $fileName = basename($rutaArchivo);
        $fullPath = rtrim($this->storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function buildFileName(string $pedidoId, string $extension): string
    {
        return sprintf(
            '%s_%s.%s',
            strtoupper($pedidoId),
            date('Ymd_His'),
            $extension
        );
    }

    private function resolveExtension(?string $originalName, ?string $mimeType): string
    {
        $fromName = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));

        if (in_array($fromName, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $fromName === 'jpeg' ? 'jpg' : $fromName;
        }

        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'bin',
        };
    }
}