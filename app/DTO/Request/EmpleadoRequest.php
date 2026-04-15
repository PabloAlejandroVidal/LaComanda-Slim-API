<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class EmpleadoRequest extends AbstractRequestDTO
{
    public function __construct(
        public string $nombre,
        public string $clave,
        public string $email,
        public string $tipo,
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'nombre',
            v::stringType()
                ->length(2, 40)
                ->regex('/^[\p{L}\s]+$/u')
                ->setTemplate('El nombre debe tener entre 2 y 40 letras')
        )->key(
            'email',
            v::stringType()
                ->notEmpty()
                ->email()
                ->setTemplate('Debe ser un email válido')
        )->key(
            'tipo',
            v::stringType()
                ->in(['bartender', 'cervecero', 'cocinero', 'mozo'])
                ->setTemplate('Tipo inválido')
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
        if (is_string($data['nombre'] ?? null)) {
            $data['nombre'] = trim($data['nombre']);
        }

        if (is_string($data['email'] ?? null)) {
            $data['email'] = trim($data['email']);
        }

        if (is_string($data['tipo'] ?? null)) {
            $data['tipo'] = strtolower(trim($data['tipo']));
        }

        return $data;
    }

    protected static function map(array $data): static
    {
        return new static(
            $data['nombre'],
            $data['clave'],
            $data['email'],
            $data['tipo']
        );
    }
}