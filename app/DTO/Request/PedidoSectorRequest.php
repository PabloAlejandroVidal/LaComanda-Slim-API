<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class PedidoSectorRequest extends AbstractRequestDTO
{
    public function __construct(
        public int $sectorId
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'sectorId',
            v::intType()
                ->positive()
                ->setTemplate('El sectorId debe ser un entero positivo')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function map(array $data): static
    {
        return new static($data['sectorId']);
    }
}