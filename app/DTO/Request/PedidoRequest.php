<?php
declare(strict_types=1);

namespace App\DTO\Request;

use Respect\Validation\Validator as v;
use Respect\Validation\Validatable;

final class PedidoRequest extends AbstractRequestDTO
{
    /**
     * @param DetalleRequest[] $detalles
     */
    public function __construct(
        public string $nombre,
        public string $mesa,
        public array $detalles
    ) {}

    protected static function rules(): Validatable
    {
        return v::key(
            'nombre',
            v::stringType()
                ->length(2, 40)
                ->regex('/^[\p{L}\s\'\-]+$/u')
                ->setTemplate('El nombre debe tener entre 2 y 40 caracteres válidos')
        )->key(
            'mesa',
            v::stringType()
                ->regex('/^[A-Z0-9]{5}$/')
                ->setTemplate('El código de mesa debe tener exactamente 5 caracteres alfanuméricos')
        )->key(
            'detalles',
            v::arrayType()
                ->notEmpty()
                ->each(v::arrayType())
                ->setTemplate('Debe incluir al menos un detalle válido')
        )->setTemplate('Campo {{name}} requerido');
    }

    protected static function normalize(array $data): array
    {
        if (is_string($data['nombre'] ?? null)) {
            $data['nombre'] = trim($data['nombre']);
        }

        if (is_string($data['mesa'] ?? null)) {
            $data['mesa'] = strtoupper(trim($data['mesa']));
        }

        return $data;
    }

    protected static function map(array $data): static
    {
        $detalles = array_map(
            fn (array $detalle) => DetalleRequest::fromArray($detalle),
            $data['detalles']
        );

        return new static(
            $data['nombre'],
            $data['mesa'],
            $detalles
        );
    }
}