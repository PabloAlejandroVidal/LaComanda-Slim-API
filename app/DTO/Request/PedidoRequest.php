<?php
namespace App\DTO;

use ValidatableRequest;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class PedidoRequest implements ValidatableRequest
{
    public function __construct(
        public string $nombre,
        public string $mesa,
        public array $detalles       // cada elemento debe ser ['id' => int, 'cantidad' => int]
    ) {}

    public static function fromArray(array $data): self
    {
        self::validate($data);

        return new self(
            $data['nombre'],
            strtoupper($data['mesa']), // normalizamos a mayúscula si lo necesitás
            $data['detalles']
        );
    }

    public static function validate(array $data): void
    {
        v::key('nombre', v::alpha('áéíóúÁÉÍÓÚñÑ ')->length(2, 40))
         ->key('mesa', v::alnum()->length(5, 5))
         ->key('detalles', v::arrayType()->each(
             v::key('id', v::intVal())
              ->key('cantidad', v::intVal()->positive())
         ))
         ->assert($data);
    }
}
