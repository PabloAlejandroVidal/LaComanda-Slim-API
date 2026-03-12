<?php
namespace App\Controllers;

use App\Services\EmpleadoQueryService;
use App\Http\JsonApiResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmpleadoQueryController extends Controller
{
    public function __construct(
        private EmpleadoQueryService $empleadoQueryService
    ) {}

    /*-------------------------------------------------
    | GET /empleados/estadisticas?from=...&to=...
    -------------------------------------------------*/
    public function estadisticas(Request $request, Response $res): Response
    {
        $query = $request->getQueryParams();

        $from = $query['from'] ?? null;
        $to   = $query['to'] ?? null;

        if (!$from || !$to) {
            return JsonApiResponseHelper::respondWithError(
                $res,
                '400',
                'Bad Request',
                'Debe enviar parámetros from y to',
                'Debe enviar parámetros from y to',
            );
        }

        $stats = $this->empleadoQueryService->estadisticas($from, $to);

        return JsonApiResponseHelper::respondWithCollection(
            $res,
            'empleado-estadisticas',
            $stats
        );
    }
}