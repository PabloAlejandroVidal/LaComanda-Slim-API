<?php

namespace App\Controllers;

use App\DTO\Request\CancelarPedidoRequest;
use App\DTO\Request\IniciarPreparacionRequest;
use App\DTO\Request\PedidoRequest;
use App\DTO\Request\PedidoSectorRequest;
use App\Services\PedidoFotoService;
use App\Services\PedidoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class PedidoController extends BaseController
{
    public function __construct(
        private PedidoService $pedidoService,
        private PedidoFotoService $pedidoFotoService
    ) {}

    public function crearPedido(Request $request, Response $response): Response
    {
        $pedidoRequest = $this->getJsonDtoOrFail($request, PedidoRequest::class);
        $token = $this->getTokenPayload($request);

        $pedido = $this->pedidoService->crearPedido($pedidoRequest, $token);

        return $this->created(
            $response,
            $pedido,
            'Pedido creado correctamente'
        );
    }

    public function iniciarPreparacion(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);
        $preparacionRequest = $this->getJsonDtoOrFail($request, IniciarPreparacionRequest::class);
        $token = $this->getTokenPayload($request);

        $this->pedidoService->iniciarPreparacion(
            $id,
            $token->id,
            $preparacionRequest->sectorId,
            $preparacionRequest->tiempoEstimadoMinutos
        );

        return $this->ok(
            $response,
            [
                'id' => $id,
                'sector_id' => $preparacionRequest->sectorId,
                'tiempo_estimado_minutos' => $preparacionRequest->tiempoEstimadoMinutos,
                'accion' => 'en_preparacion',
            ],
            'Preparación iniciada correctamente'
        );
    }

    public function marcarListo(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);
        $token = $this->getTokenPayload($request);
        $sectorRequest = $this->getJsonDtoOrFail($request, PedidoSectorRequest::class);

        $this->pedidoService->marcarListo(
            $id,
            $token->id,
            $sectorRequest->sectorId
        );

        return $this->ok(
            $response,
            [
                'id' => $id,
                'sector_id' => $sectorRequest->sectorId,
                'accion' => 'listo',
            ],
            'Pedido marcado como listo correctamente'
        );
    }

    public function subirFoto(Request $request, Response $response, array $args): Response
    {
        $pedidoId = $this->getRouteId($args);
        $token = $this->getTokenPayload($request);

        $uploadedFiles = $request->getUploadedFiles();
        $foto = $uploadedFiles['foto'] ?? null;

        $result = $this->pedidoFotoService->guardarFoto($pedidoId, $foto, $token);

        return $this->created($response, $result, 'Foto del pedido cargada correctamente');
    }

    public function entregar(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);
        $token = $this->getTokenPayload($request);

        $this->pedidoService->entregar($id, $token->id);

        return $this->ok(
            $response,
            [
                'id' => $id,
                'accion' => 'entregado',
            ],
            'Pedido entregado correctamente'
        );
    }

    public function cobrarMesa(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);
        $token = $this->getTokenPayload($request);

        $resultado = $this->pedidoService->cobrarMesa($id, $token);

        return $this->ok(
            $response,
            $resultado,
            'Cobro registrado correctamente'
        );
    }

    public function cerrar(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);
        $token = $this->getTokenPayload($request);

        $resultado = $this->pedidoService->cerrar($id, $token->id);

        return $this->ok(
            $response,
            $resultado,
            'Mesa cerrada correctamente'
        );
    }

    public function cancelar(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);
        $cancelRequest = $this->getJsonDtoOrFail($request, CancelarPedidoRequest::class);
        $token = $this->getTokenPayload($request);

        $resultado = $this->pedidoService->cancelar($id, $cancelRequest, $token);

        return $this->ok(
            $response,
            $resultado,
            'Pedido cancelado correctamente'
        );
    }
}