<?php
namespace App\DTO\Request;

use ValidatableRequest;
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};

class DetalleRequest implements ValidatableRequest
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
        $validador = v::key('id', v::min(1)->setTemplate('El ID no puede ser 0 o un número negativo'))
                ->key('cantidad', v::min(1)->setTemplate('La cantidad debe ser mayor a 0'))
                ->setTemplate('Campo {{name}} requerido');
                $validador->assert($data);
    } 
}

?>
