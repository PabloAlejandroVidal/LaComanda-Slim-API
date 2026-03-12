<?php
namespace App\Domain\Mesa;

enum EstadoMesa: string
{
    case CERRADA = 'cerrada';
    case ESPERANDO_PEDIDO = 'esperando_pedido';
    case COMIENDO = 'comiendo';
    case PAGANDO = 'pagando';
}