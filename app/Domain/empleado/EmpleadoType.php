<?php
namespace App\Domain\Empleado;

enum EmpleadoType: string {
    case BARTENDER = 'bartender';
    case CERVECERO = 'cervecero';
    case COCINERO = 'cocinero';
    case MOZO = 'mozo';
    case SOCIO = 'socio';
    public static function fromId(int $id): self
    {
        return match ($id) {
            1 => self::BARTENDER,
            2 => self::CERVECERO,
            3 => self::COCINERO,
            4 => self::MOZO,
            5 => self::SOCIO,
            default => throw new \ValueError("Rol de empleado inválido: $id"),
        };
    }
    
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