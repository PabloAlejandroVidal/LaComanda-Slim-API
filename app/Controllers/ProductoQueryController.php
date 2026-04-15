<?php

namespace App\Controllers;

use App\Services\ProductoQueryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoQueryController extends BaseController
{
    public function __construct(
        private ProductoQueryService $productoQueryService
    ) {}

    public function listarProductos(Request $request, Response $res): Response
    {
        $productos = $this->productoQueryService->obtenerProductos();

        return $this->ok(
            $res,
            $productos,
            'Productos obtenidos correctamente'
        );
    }
    
    public function exportarPdf(Request $request, Response $response): Response
    {
        $pdf = $this->productoQueryService->exportarPdf();

        $response->getBody()->write($pdf);

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'attachment; filename="productos.pdf"');
    }
    
    public function exportarCsv(Request $request, Response $response): Response
    {
        $csv = $this->productoQueryService->exportarCsv();

        $response->getBody()->write($csv);

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->withHeader('Content-Disposition', 'attachment; filename="productos.csv"');
    }
}