<?php
declare(strict_types=1);

namespace App\DTO\Request;

use App\Domain\Sector\SectorNombre;
use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class ProductoRequest extends AbstractRequestDTO
{
    public function __construct(
        public string $nombre,
        public SectorNombre $sector,
        public float $precio,
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'nombre',
            v::stringType()
                ->length(2, 50)
                ->regex('/^[\p{L}\p{N}\s.\-]+$/u')
                ->setTemplate('El nombre debe tener entre 2 y 50 caracteres válidos')
        )->key(
            'sector',
            v::stringType()
                ->in(SectorNombre::values())
                ->setTemplate('Debe ser un sector válido')
        )->key(
            'precio',
            v::numericVal()
                ->between(1, 100000)
                ->setTemplate('Debe ser un precio válido')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function normalize(array $data): array
    {
        if (is_string($data['nombre'] ?? null)) {
            $data['nombre'] = trim($data['nombre']);
        }

        if (is_string($data['sector'] ?? null)) {
            $data['sector'] = strtolower(trim($data['sector']));
        }

        return $data;
    }

    protected static function map(array $data): static
    {
        return new static(
            $data['nombre'],
            SectorNombre::from($data['sector']),
            (float) $data['precio'],
        );
    }
}