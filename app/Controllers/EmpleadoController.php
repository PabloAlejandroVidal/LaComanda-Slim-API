<?php
namespace App\Controllers;

use App\DTO\EmpleadoRequest;
use App\Http\JsonApiResponseHelper;
use App\Repositories\EmpleadoRepository;
use App\DTO\EmpleadoDTO;
use App\Services\EmpleadoService;
use App\Utils\EmpleadoMapper;
use App\Validation\ValidationHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class EmpleadoController extends Controller
{
    public function __construct(
        private EmpleadoRepository $empleadoRepo,
        private EmpleadoService $empleadoService,
    ) {}

    public function agregarEmpleado(Request $request, Response $res): Response
    {
        $data = $request->getParsedBody();
        $empleadoRequest = EmpleadoRequest::fromArray($data);
        $empleado = $this->empleadoService->crearEmpleado($empleadoRequest);
        return JsonApiResponseHelper::respondWithCreatedResource($res, 'empleados', $empleado, '/empleados/' . $empleado['id']);
    }
    
    public function listarEmpleados(Request $request, Response $res): Response
    {
        $empleados = $this->empleadoService->obtenerEmpleados();
        return JsonApiResponseHelper::respondWithCollection($res, 'empleados',$empleados);
    }
}
