<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class UsuarioRequest extends AbstractRequestDTO
{
    public function __construct(
        public string $email,
        public string $clave,
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'email',
            v::stringType()
                ->notEmpty()
                ->email()
                ->setTemplate('Debe ser un email válido')
        )->key(
            'clave',
            v::stringType()
                ->length(4, 24)
                ->regex('/\S/')
                ->setTemplate('La clave debe tener entre 4 y 24 caracteres válidos')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function normalize(array $data): array
    {
        if (is_string($data['email'] ?? null)) {
            $data['email'] = trim($data['email']);
        }

        return $data;
    }

    protected static function map(array $data): static
    {
        return new static(
            $data['email'],
            $data['clave']
        );
    }
}