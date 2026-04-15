<?php

namespace App\Controllers;

use App\DTO\Request\MesaRequest;
use App\Services\MesaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController extends BaseController
{
    public function __construct(
        private MesaService $mesaService
    ) {}

    public function agregarMesa(Request $request, Response $response): Response
    {
        $mesaRequest = $this->getJsonDtoOrFail($request, MesaRequest::class);
        $mesa = $this->mesaService->crearMesa($mesaRequest);

        return $this->created(
            $response,
            $mesa,
            'Mesa creada correctamente'
        );
    }
}