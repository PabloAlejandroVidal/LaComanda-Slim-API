<?php

namespace App\Controllers;

use App\Exceptions\BadRequestException;
use App\Services\MesaQueryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaQueryController extends BaseController
{
    public function __construct(
        private MesaQueryService $mesaQueryService
    ) {}

    public function listarMesas(Request $request, Response $res): Response
    {
        $mesas = $this->mesaQueryService->listarMesas();

        return $this->ok(
            $res,
            $mesas,
            'Mesas obtenidas correctamente'
        );
    }

    public function listarMesasAgrupadasPorEstado(Request $request, Response $res): Response
    {
        $mesas = $this->mesaQueryService->listarMesasAgrupadasPorEstado();

        return $this->ok(
            $res,
            $mesas,
            'Mesas agrupadas por estado obtenidas correctamente'
        );
    }


    public function masUsada(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->masUsada($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Mesa más usada obtenida correctamente'
            : 'No hay datos para calcular la mesa más usada'
        );
    }

    public function menosUsada(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->menosUsada($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Mesa menos usada obtenida correctamente'
            : 'No hay datos para calcular la mesa menos usada'
        );
    }

    public function mayorFacturacion(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->mayorFacturacion($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Mesa con mayor facturación obtenida correctamente'
            : 'No hay datos para calcular la mayor facturacion'
        );
    }

    public function menorFacturacion(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->menorFacturacion($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Mesa con menor facturación obtenida correctamente'
            : 'No hay datos para calcular la menor facturacion'
        );
    }

    public function mayorImporte(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->mayorImporte($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Mesa con mayor importe obtenido correctamente'
            : 'No hay datos para calcular el mayor importe'
        );
    }

    public function menorImporte(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->menorImporte($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Mesa con menor importe obtenido correctamente'
            : 'No hay datos para calcular el menor importe'
        );
    }

    public function facturacionEntre(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->facturacionEntre($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Facturación entre fechas obtenida correctamente'
            : 'No hay datos para calcular Facturación entre fechas'
        );
    }

    public function mejoresComentarios(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->mejoresComentarios($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Mejores comentarios obtenidos correctamente'
            : 'No hay comentarios'
        );
    }

    public function peoresComentarios(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->mesaQueryService->peoresComentarios($from, $to);

        return $this->ok(
            $res,
            $data,
            $data
            ? 'Peores comentarios obtenidos correctamente'
            : 'No hay comentarios'
        );
    }
}