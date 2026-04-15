<?php
namespace App\Domain\Operacion;

enum TipoOperacion: string
{
    case TOMA_PEDIDO = 'toma_pedido';
    case ASIGNACION_DETALLE = 'asignacion_detalle';
    case PREPARACION_DETALLE = 'preparacion_detalle';
    case ENTREGA_PEDIDO = 'entrega_pedido';
    case COBRO_MESA = 'cobro_mesa';
    case CIERRE_MESA = 'cierre_mesa';
    case CANCELACION_PEDIDO = 'cancelacion_pedido';

    public function ambito(): AmbitoOperacion
    {
        return match ($this) {
            self::TOMA_PEDIDO,
            self::ENTREGA_PEDIDO,
            self::CANCELACION_PEDIDO => AmbitoOperacion::PEDIDO,

            self::ASIGNACION_DETALLE,
            self::PREPARACION_DETALLE => AmbitoOperacion::SECTOR,

            self::COBRO_MESA,
            self::CIERRE_MESA => AmbitoOperacion::MESA,
        };
    }
}