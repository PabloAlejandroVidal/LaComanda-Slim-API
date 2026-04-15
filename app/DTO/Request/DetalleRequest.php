<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class DetalleRequest extends AbstractRequestDTO
{
    public function __construct(
        public int $id,
        public int $cantidad,
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'id',
            v::intType()
                ->positive()
                ->setTemplate('El ID debe ser un entero positivo')
        )->key(
            'cantidad',
            v::intType()
                ->positive()
                ->setTemplate('La cantidad debe ser mayor a 0')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function map(array $data): static
    {
        return new static(
            $data['id'],
            $data['cantidad']
        );
    }
}