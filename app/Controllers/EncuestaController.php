<?php

namespace App\Controllers;

use App\DTO\Request\EncuestaRequest;
use App\Services\EncuestaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EncuestaController extends BaseController
{
    public function __construct(
        private EncuestaService $encuestaService
    ) {}

    public function crear(Request $request, Response $response, array $args): Response
    {
        $pedidoId = (string) ($args['pedidoId'] ?? '');
        $dto = $this->getJsonDtoOrFail($request, EncuestaRequest::class);

        $encuesta = $this->encuestaService->crear($pedidoId, $dto);

        return $this->created(
            $response,
            $encuesta,
            'Encuesta creada correctamente'
        );
    }
}