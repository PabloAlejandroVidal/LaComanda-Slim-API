<?php

namespace App\Services;

use App\Contracts\FotoStorageInterface;
use App\DTO\Response\PedidoFotoResponseDTO;
use App\Entities\TokenPayload;
use App\Exceptions\BadRequestException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Repositories\EmpleadoRepository;
use App\Repositories\PedidoFotoRepository;
use App\Repositories\PedidoRepository;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;

final class PedidoFotoService
{
    private const MAX_FILE_SIZE = 5_242_880; // 5 MB

    public function __construct(
        private PedidoRepository $pedidoRepo,
        private PedidoFotoRepository $pedidoFotoRepo,
        private EmpleadoRepository $empleadoRepo,
        private FotoStorageInterface $fotoStorage
    ) {}

    public function guardarFoto(
        string $pedidoId,
        UploadedFileInterface $foto,
        TokenPayload $token
    ): PedidoFotoResponseDTO {
        $this->getPedidoOrFail($pedidoId);
        $empleadoId = $this->getEmpleadoIdOrFail($token->email);

        if ($this->pedidoFotoRepo->existsByPedidoId($pedidoId)) {
            throw new ConflictException('El pedido ya tiene una foto asociada');
        }

        $this->validarFoto($foto);

        $storedPhoto = $this->fotoStorage->store($foto, $pedidoId);

        try {
            $this->pedidoFotoRepo->create(
                pedidoId: $pedidoId,
                rutaArchivo: $storedPhoto->ruta,
                nombreOriginal: $storedPhoto->nombreOriginal,
                mimeType: $storedPhoto->mimeType,
                tamanoBytes: $storedPhoto->tamanoBytes,
                empleadoId: $empleadoId,
                fechaCreacion: Utils::getHoraActual()
            );
        } catch (Throwable $e) {
            $this->fotoStorage->delete($storedPhoto->ruta);
            throw $e;
        }

        return new PedidoFotoResponseDTO(
            pedidoId: $pedidoId,
            fotoUrl: $storedPhoto->ruta,
            mimeType: $storedPhoto->mimeType,
            tamanoBytes: $storedPhoto->tamanoBytes
        );
    }

    private function validarFoto(UploadedFileInterface $foto): void
    {
        if ($foto->getError() !== UPLOAD_ERR_OK) {
            throw new BadRequestException('Error al subir la foto');
        }

        $mimeType = $foto->getClientMediaType();
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new BadRequestException('La foto debe ser JPG, PNG o WEBP');
        }

        $size = $foto->getSize();

        if ($size === null || $size <= 0) {
            throw new BadRequestException('La foto está vacía o es inválida');
        }

        if ($size > self::MAX_FILE_SIZE) {
            throw new BadRequestException('La foto supera el tamaño máximo permitido de 5 MB');
        }
    }

    private function getPedidoOrFail(string $pedidoId): array
    {
        $pedido = $this->pedidoRepo->getPedidoById($pedidoId);

        if (!$pedido) {
            throw new NotFoundException('Pedido no encontrado');
        }

        return $pedido;
    }

    private function getEmpleadoIdOrFail(string $email): int
    {
        $empleado = $this->empleadoRepo->getEmpleadoByEmail($email);

        if (!$empleado) {
            throw new NotFoundException('Empleado no encontrado');
        }

        if (is_array($empleado) && isset($empleado['id'])) {
            return (int) $empleado['id'];
        }

        if (is_object($empleado) && isset($empleado->id)) {
            return (int) $empleado->id;
        }

        throw new NotFoundException('No se pudo resolver el empleado autenticado');
    }
}