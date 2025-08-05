<?php
namespace App\Controllers;

use App\DTO\MesaInput;
use App\DTO\MesaRequest;
use App\Http\JsonApiResponseHelper;
use App\Repositories\MesaRepository;
use App\Repositories\PedidoRepository;
use App\DTO\MesaDTO;
use App\Services\MesaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class MesaController extends Controller
{
    public function __construct(
        private MesaRepository $mesaRepository,
        private PedidoRepository $pedidoRepository,
        private MesaService $mesaService
    ) {}

    public function agregarMesa(Request $request, Response $res): Response
    {
        $data = $request->getParsedBody();
        $mesaRequest = MesaRequest::fromArray($data);
        $mesa = $this->mesaService->crearMesa($mesaRequest);        
        return JsonApiResponseHelper::respondWithCreatedResource($res, 'mesas', $mesa, '/mesas/' . $mesa['id']);
    }

    public function listarMesas(Request $request, Response $res): Response
    {
        $mesas = $this->mesaService->getMesas();
        return JsonApiResponseHelper::respondWithCollection($res, 'mesas', $mesas);
    }

}
