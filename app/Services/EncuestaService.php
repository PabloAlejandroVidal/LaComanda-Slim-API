<?php

namespace App\Services;

use App\DTO\Request\EncuestaRequest;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ResourceNotCreatedException;
use App\Repositories\EncuestaRepository;
use App\Repositories\PedidoRepository;

final class EncuestaService
{
    public function __construct(
        private EncuestaRepository $encuestaRepo,
        private PedidoRepository $pedidoRepo
    ) {}

    public function crear(string $pedidoId, EncuestaRequest $request): array
    {
        $pedidoId = strtoupper(trim($pedidoId));

        if ($pedidoId === '') {
            throw new NotFoundException('Pedido no encontrado');
        }

        if (!$this->pedidoRepo->existsById($pedidoId)) {
            throw new NotFoundException('Pedido no encontrado');
        }

        if (!$this->pedidoRepo->isPagado($pedidoId)) {
            throw new ConflictException(
                'Solo se puede completar la encuesta de un pedido pagado'
            );
        }

        if ($this->encuestaRepo->existsByPedidoId($pedidoId)) {
            throw new ConflictException(
                'Ya existe una encuesta para este pedido'
            );
        }

        $creada = $this->encuestaRepo->create($pedidoId, $request);

        if (!$creada) {
            throw new ResourceNotCreatedException(
                'No se pudo crear la encuesta'
            );
        }

        $encuesta = $this->encuestaRepo->findByPedidoId($pedidoId);

        if ($encuesta === null) {
            throw new ResourceNotCreatedException(
                'La encuesta se creó pero no pudo recuperarse'
            );
        }

        return $encuesta;
    }
}