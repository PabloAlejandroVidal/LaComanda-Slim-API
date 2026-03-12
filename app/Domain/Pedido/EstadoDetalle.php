<?php
namespace App\Domain\Pedido;

enum EstadoDetalle: string
{
    case PENDIENTE = 'pendiente';
    case EN_PREPARACION = 'en_preparacion';
    case LISTO = 'listo';
    case ENTREGADO = 'entregado';
}