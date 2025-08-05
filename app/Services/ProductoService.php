<?php
namespace App\Services;

use App\DTO\ProductoRequest;
use App\Exceptions\ConflictException;
use App\Repositories\ProductoRepository;


class ProductoService
{
    public function __construct(
        private ProductoRepository $productoRepository,
    ) {}

public function crearProducto(ProductoRequest $productoRequest): array
{
    if ($this->productoRepository->productoNombreExiste($productoRequest->nombre)) {
        throw new ConflictException("No se pudo crear el producto - El nombre ya se encuentra en uso: {$productoRequest->nombre}");
    }

    $productoId = $this->productoRepository->agregarProducto(
        $productoRequest->nombre,
        $productoRequest->sector,
        $productoRequest->precio,
        100
    );

    return [
        'id' => $productoId,
        'nombre' => $productoRequest->nombre,        
        'sector' => $productoRequest->sector,
        'precio' => $productoRequest->precio,
        'cantidad' => 100
    ];
}

    public function obtenerProductos(): array {
        return $this->productoRepository->getAllProductos();
    }
}
