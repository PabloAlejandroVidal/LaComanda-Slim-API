<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class CancelarPedidoRequest extends AbstractRequestDTO
{
    public function __construct(
        public string $motivoCancelacion
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'motivoCancelacion',
            v::stringType()
                ->notEmpty()
                ->length(3, 255)
                ->setTemplate('El motivo de cancelación debe tener entre 3 y 255 caracteres')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function normalize(array $data): array
    {
        if (is_string($data['motivoCancelacion'] ?? null)) {
            $data['motivoCancelacion'] = trim($data['motivoCancelacion']);
        }

        return $data;
    }

    protected static function map(array $data): static
    {
        return new static($data['motivoCancelacion']);
    }
}