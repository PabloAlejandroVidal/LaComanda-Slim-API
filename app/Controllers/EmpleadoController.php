<?php

namespace App\Controllers;

use App\DTO\Request\EmpleadoRequest;
use App\Services\EmpleadoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmpleadoController extends BaseController
{
    public function __construct(
        private EmpleadoService $empleadoService,
    ) {}

    public function agregarEmpleado(Request $request, Response $response): Response
    {
        $empleadoRequest = $this->getJsonDtoOrFail($request, EmpleadoRequest::class);
        $empleado = $this->empleadoService->crearEmpleado($empleadoRequest);

        return $this->created(
            $response,
            $empleado,
            'Empleado creado correctamente'
        );
    }

    public function listarEmpleados(Request $request, Response $response): Response
    {
        $empleados = $this->empleadoService->obtenerEmpleados();

        return $this->ok(
            $response,
            $empleados,
            'Empleados obtenidos correctamente'
        );
    }

    public function suspenderEmpleado(Request $request, Response $response, array $args): Response
    {
        $empleadoId = (int) $this->getRouteId($args, 'id');
        $empleado = $this->empleadoService->suspenderEmpleado($empleadoId);

        return $this->ok(
            $response,
            $empleado,
            'Empleado suspendido correctamente'
        );
    }

    public function reactivarEmpleado(Request $request, Response $response, array $args): Response
    {
        $empleadoId = (int) $this->getRouteId($args, 'id');
        $empleado = $this->empleadoService->reactivarEmpleado($empleadoId);

        return $this->ok(
            $response,
            $empleado,
            'Empleado reactivado correctamente'
        );
    }

    public function borrarEmpleado(Request $request, Response $response, array $args): Response
    {
        $empleadoId = (int) $this->getRouteId($args, 'id');
        $empleado = $this->empleadoService->borrarEmpleado($empleadoId);

        return $this->ok(
            $response,
            $empleado,
            'Empleado dado de baja correctamente'
        );
    }
}