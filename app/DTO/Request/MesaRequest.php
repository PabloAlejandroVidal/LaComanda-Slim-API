<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class MesaRequest extends AbstractRequestDTO
{
    public function __construct(
        public string $id,
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'id',
            v::stringType()
                ->regex('/^[A-Z0-9]{5}$/')
                ->setTemplate('El ID debe tener exactamente 5 caracteres alfanuméricos')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function normalize(array $data): array
    {
        if (is_string($data['id'] ?? null)) {
            $data['id'] = strtoupper(trim($data['id']));
        }

        return $data;
    }

    protected static function map(array $data): static
    {
        return new static($data['id']);
    }
}