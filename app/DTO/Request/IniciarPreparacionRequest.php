<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class IniciarPreparacionRequest extends AbstractRequestDTO
{
    public function __construct(
        public int $sectorId,
        public int $tiempoEstimadoMinutos
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'sectorId',
            v::intType()
                ->positive()
                ->setTemplate('El sectorId debe ser un entero positivo')
        )->key(
            'tiempoEstimadoMinutos',
            v::intType()
                ->between(1, 240)
                ->setTemplate('El tiempo estimado debe ser un entero entre 1 y 240 minutos')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function map(array $data): static
    {
        return new static(
            $data['sectorId'],
            $data['tiempoEstimadoMinutos']
        );
    }
}