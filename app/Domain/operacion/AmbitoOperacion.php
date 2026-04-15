<?php
namespace App\Domain\Operacion;
enum AmbitoOperacion: string
{
    case PEDIDO = 'pedido';
    case SECTOR = 'sector';
    case MESA = 'mesa';
}