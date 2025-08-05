<?php
namespace App\DTO;

use ValidatableRequest;
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};

class PedidoRequest implements ValidatableRequest
{
    public function __construct(
        public int $id,
        public int $cantidad,
    ) {}
    public static function fromArray(array $data): self
    {
        self::validate($data);
        
        return new self($data['id'], $data['cantidad']);
    }

    public static function validate(array $data): void{
        v::key('nombre', v::alpha()->length(2, 40))
        ->key('mesa', v::alnum()->length(5, 5))
        ->key('detalles', v::arrayType()->each(v::key('id', v::intVal())
        ->key('cantidad', v::intVal()->positive())))
        ->assert($data);
    } 
}

?>
