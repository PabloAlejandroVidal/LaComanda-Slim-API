<?php

namespace App\DTO\Request;

use Respect\Validation\Validatable;
use Respect\Validation\Validator as v;
use Respect\Validation\Rules\Key;

final class EncuestaRequest extends AbstractRequestDTO
{
    public function __construct(
        public readonly int $puntajeMesa,
        public readonly int $puntajeRestaurante,
        public readonly int $puntajeMozo,
        public readonly int $puntajeCocinero,
        public readonly ?string $comentario
    ) {}

    protected static function normalize(array $data): array
    {
        foreach ([
            'puntajeMesa',
            'puntajeRestaurante',
            'puntajeMozo',
            'puntajeCocinero',
        ] as $campo) {
            if (array_key_exists($campo, $data) && is_numeric($data[$campo])) {
                $data[$campo] = (int) $data[$campo];
            }
        }

        if (array_key_exists('comentario', $data)) {
            $comentario = trim((string) $data['comentario']);
            $data['comentario'] = $comentario === '' ? null : $comentario;
        }

        return $data;
    }

    protected static function rules(): Validatable
    {
        return v::keySet(
            new Key('puntajeMesa', v::intType()->between(1, 10)),
            new Key('puntajeRestaurante', v::intType()->between(1, 10)),
            new Key('puntajeMozo', v::intType()->between(1, 10)),
            new Key('puntajeCocinero', v::intType()->between(1, 10)),
            new Key('comentario', v::optional(v::stringType()->length(1, 66)), false)
        );
    }

    protected static function map(array $data): static
    {
        return new static(
            puntajeMesa: $data['puntajeMesa'],
            puntajeRestaurante: $data['puntajeRestaurante'],
            puntajeMozo: $data['puntajeMozo'],
            puntajeCocinero: $data['puntajeCocinero'],
            comentario: $data['comentario'] ?? null
        );
    }
}