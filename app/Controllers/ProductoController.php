<?php

namespace App\Controllers;

use App\DTO\Request\ProductoRequest;
use App\Services\ProductoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController extends BaseController
{
    public function __construct(
        private ProductoService $productoService
    ) {}

    public function agregarProducto(Request $request, Response $response): Response
    {
        $productoRequest = $this->getJsonDtoOrFail($request, ProductoRequest::class);
        $producto = $this->productoService->crearProducto($productoRequest);

        return $this->created(
            $response,
            $producto,
            'Producto creado correctamente'
        );
    }

    public function importarCsv(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'] ?? null;

        $resultado = $this->productoService->importarCsv($file);

        return $this->ok(
            $response,
            $resultado,
            'CSV de productos importado correctamente'
        );
    }
}