<?php
namespace App\Domain\Empleado;

enum EmpleadoType: string {
    case BARTENDER = 'bartender';
    case CERVECERO = 'cervecero';
    case COCINERO = 'cocinero';
    case MOZO = 'mozo';
    case SOCIO = 'socio';
    
    public function label(): string
    {
        return match($this) {
            self::BARTENDER => 'Bartender',
            self::CERVECERO => 'Cervecero',
            self::COCINERO => 'Cocinero',
            self::MOZO => 'Mozo',
            self::SOCIO => 'Socio',
        };
    }
}