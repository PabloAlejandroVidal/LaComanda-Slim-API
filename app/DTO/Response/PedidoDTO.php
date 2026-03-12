<?php
namespace App\DTO\Response;
class PedidoDTO
{
    public function __construct(
        public string $id,
        public string $mesaId,
        public string $nombreCliente,
        public string $horaInicio,
        public ?string $horaCierre,
        public ?int $empleadoInicioId = null,
        public ?int $empleadoCierreId = null,
        /** @var DetalleDTO[] */
        public array $detalles = [],
    ) {}
}
?>
