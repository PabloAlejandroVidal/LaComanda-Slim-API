<?php
namespace App\Services;

use App\Domain\Pedido\EstadoDetalle;
use App\Exceptions\NotFoundException;
use App\Repositories\PedidoRepository;
use App\Repositories\DetallePedidoRepository;
use App\Repositories\EmpleadoRepository;

/**
 * Solo LECTURAS. Sin transacciones. Sin efectos secundarios.
 * Ideal para agrupar, filtrar, paginar y proyectar datos para la API.
 */
final class PedidoQueryService
{
    public function __construct(
        private PedidoRepository $pedidoRepo,
        private DetallePedidoRepository $detalleRepo,
    ) {}

    public function obtenerPorId(string $pedidoId): array
    {
        $pedido = $this->pedidoRepo->getPedidoById($pedidoId);

        if (!$pedido) {
            throw new NotFoundException("Pedido no encontrado");
        }

        return [
            'pedido'   => $pedido,
            'detalles' => $this->detalleRepo->getDetallesDelPedido($pedidoId)
        ];
    }

    public function masVendidos(string $from, string $to): array
    {
        return $this->pedidoRepo->getMasVendido($from, $to);
    }

    public function menosVendidos(string $from, string $to): array
    {
        return $this->pedidoRepo->getMenosVendido($from, $to);
    }

    public function noEntregadosEnTiempo(string $from, string $to): array
    {
        return $this->pedidoRepo->getPedidosFueraDeTiempo($from, $to);
    }

    public function cancelados(string $from, string $to): array
    {
        return $this->pedidoRepo->getCancelados($from, $to);
    }
}
