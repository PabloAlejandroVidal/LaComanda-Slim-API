<?php

namespace App\Controllers;

use App\Exceptions\BadRequestException;
use App\Services\EmpleadoQueryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmpleadoQueryController extends BaseController
{
    public function __construct(
        private EmpleadoQueryService $empleadoQueryService
    ) {}

    /*-------------------------------------------------
    | GET /empleados/estadisticas?from=...&to=...
    -------------------------------------------------*/
    public function estadisticas(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $stats = $this->empleadoQueryService->estadisticas($from, $to);

        return $this->ok(
            $res,
            $stats,
            'Estadísticas de empleados obtenidas correctamente'
        );
    }

    /*-------------------------------------------------
    | GET /empleados/operaciones/sector?from=...&to=...
    -------------------------------------------------*/
    public function getOperacionesSector(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerOperacionesSector($from, $to);

        return $this->ok(
            $res,
            $data,
            'Operaciones por sector obtenidas correctamente'
        );
    }

    /*-------------------------------------------------
    | GET /empleados/operaciones/sector-empleado?from=...&to=...
    -------------------------------------------------*/
    public function getOperacionesSectorEmpleado(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerOperacionesSectorEmpleado($from, $to);

        return $this->ok(
            $res,
            $data,
            'Operaciones por sector y empleado obtenidas correctamente'
        );
    }

    /*-------------------------------------------------
    | GET /empleados/operaciones?from=...&to=...
    -------------------------------------------------*/
    public function getOperacionesEmpleado(Request $request, Response $res): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerOperacionesEmpleado($from, $to);

        return $this->ok(
            $res,
            $data,
            'Operaciones por empleado obtenidas correctamente'
        );
    }
    public function produccion(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerProduccion($from, $to);

        return $this->ok(
            $response,
            $data,
            'Producción de empleados obtenida correctamente'
        );
    }

    public function produccionPorSector(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerProduccionPorSector($from, $to);

        return $this->ok(
            $response,
            $data,
            'Producción por sector obtenida correctamente'
        );
    }

    public function produccionPorSectorEmpleado(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerProduccionPorSectorEmpleado($from, $to);

        return $this->ok(
            $response,
            $data,
            'Producción por sector y empleado obtenida correctamente'
        );
    }
    
    public function produccionDetallada(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerProduccionDetallada($from, $to);

        return $this->ok(
            $response,
            $data,
            'Producción detallada de empleados obtenida correctamente'
        );
    }

    public function ingresos(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->empleadoQueryService->obtenerIngresos($from, $to);

        return $this->ok(
            $response,
            $data,
            'Ingresos de empleados obtenidos correctamente'
        );
    }
    public function exportarPdfIngresos(Request $request, Response $response): Response
    {
        $pdf = $this->empleadoQueryService->exportarPdfIngresos();

        $response->getBody()->write($pdf);

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Disposition', 'attachment; filename="empleados_ingresos.pdf"');
    }
}