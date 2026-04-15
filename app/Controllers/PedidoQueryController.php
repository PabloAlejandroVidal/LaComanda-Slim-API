<?php

namespace App\Controllers;

use App\Exceptions\BadRequestException;
use App\Services\PedidoQueryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class PedidoQueryController extends BaseController
{
    public function __construct(
        private PedidoQueryService $pedidoQueryService
    ) {}

    public function ver(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);
        $pedido = $this->pedidoQueryService->obtenerPorId($id);

        return $this->ok(
            $response,
            $pedido,
            'Pedido obtenido correctamente'
        );
    }

    public function listar(Request $request, Response $response): Response
    {
        $filtros = (array) $request->getQueryParams();

        if (isset($filtros['from']) || isset($filtros['to'])) {
            [$filtros['from'], $filtros['to']] = $this->getRequiredDateRange($request);
        }

        $items = $this->pedidoQueryService->listar($filtros);

        return $this->ok(
            $response,
            $items,
            'Pedidos obtenidos correctamente'
        );
    }

    public function verDetalles(Request $request, Response $response, array $args): Response
    {
        $id = $this->getRouteId($args);

        $agrupado = $this->pedidoQueryService->detallesAgrupadosPorSector($id);

        return $this->ok(
            $response,
            $agrupado,
            'Detalles del pedido obtenidos correctamente'
        );
    }

    public function masVendidos(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->pedidoQueryService->masVendidos($from, $to);

        return $this->ok(
            $response,
            $data,
            'Productos más vendidos obtenidos correctamente'
        );
    }

    public function menosVendidos(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->pedidoQueryService->menosVendidos($from, $to);

        return $this->ok(
            $response,
            $data,
            'Productos menos vendidos obtenidos correctamente'
        );
    }

    public function noEntregadosEnTiempo(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->pedidoQueryService->noEntregadosEnTiempo($from, $to);

        return $this->ok(
            $response,
            $data,
            'Pedidos fuera de tiempo obtenidos correctamente'
        );
    }

    public function cancelados(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->pedidoQueryService->cancelados($from, $to);

        return $this->ok(
            $response,
            $data,
            'Pedidos cancelados obtenidos correctamente'
        );
    }

    public function cerrados(Request $request, Response $response): Response
    {
        [$from, $to] = $this->getRequiredDateRange($request);

        $data = $this->pedidoQueryService->cerrados($from, $to);

        return $this->ok(
            $response,
            $data,
            'Pedidos cerrados obtenidos correctamente'
        );
    }

    public function pendientesDelSector(Request $request, Response $response): Response
    {
        $token = $this->getTokenPayload($request);

        $items = $this->pedidoQueryService->obtenerPendientesDelSector($token);

        return $this->ok(
            $response,
            $items,
            'Pedidos pendientes de los sectores asignados obtenidos correctamente'
        );
    }

    public function enPreparacionDelSector(Request $request, Response $response): Response
    {
        $token = $this->getTokenPayload($request);

        $items = $this->pedidoQueryService->obtenerEnPreparacionDelSector($token);

        return $this->ok(
            $response,
            $items,
            'Pedidos en preparación de los sectores asignados obtenidos correctamente'
        );
    }

    public function seguimiento(Request $request, Response $response, array $args): Response
    {
        $mesaId = $this->getRouteId($args, 'mesaId');
        $pedidoId = $this->getRouteId($args, 'pedidoId');

        if (!$mesaId || !$pedidoId) {
            throw new BadRequestException('Debe indicarse mesaId y pedidoId');
        }

        $result = $this->pedidoQueryService->getSeguimiento($mesaId, $pedidoId);

        return $this->ok(
            $response,
            ['pedido' => $result],
            'Seguimiento del pedido obtenido correctamente'
        );
    }
}