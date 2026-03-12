<?php
namespace App\Domain\Pedido;

final class EstadoDetalleMapper
{
    public static function fromRow(array $row): EstadoDetalle
    {
        return match (true) {
            !empty($row['hora_entrego']) => EstadoDetalle::ENTREGADO,
            !empty($row['hora_preparo']) => EstadoDetalle::LISTO,
            !empty($row['hora_asigno'])  => EstadoDetalle::EN_PREPARACION,
            default                     => EstadoDetalle::PENDIENTE,
        };
    }
}