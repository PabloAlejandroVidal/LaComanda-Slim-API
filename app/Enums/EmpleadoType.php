<?php
namespace App\Enums;

enum EmpleadoType: int {
    
    case BARTENDER = 1;
    case CERVECERO = 2;
    case COCINERO = 3;
    case MOZO = 4;
    case SOCIO = 5;

}

enum Empleado: string {
    
    case BARTENDER = 'bartender';
    case CERVECERO = 'cervecero';
    case COCINERO = 'cocinero';
    case MOZO = 'mozo';
    case SOCIO = 'socio';

}
