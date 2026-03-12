<?php
namespace App\DTO\Response;
class PedidoListItemDTO
{
    public function __construct(
        public string $pedidoId,
        public string $mesaId,
        public string $productosPorSector,
    ) {}
}
?>
