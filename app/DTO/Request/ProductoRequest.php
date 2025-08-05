<?php
namespace App\DTO;

use ValidatableRequest;
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};

class ProductoRequest implements ValidatableRequest{
    public function __construct(
        public string $nombre,
        public string $sector,
        public string $precio,
    ) {}

    public static function fromArray(array $data): self {
                
        self::validate($data);

        return new self(
            $data['nombre'],
            $data['sector'],
            $data['precio'],
        );
    }

    public static function validate($data): void {
        $v = v::key('nombre', v::length(2, 20)->alpha()->setTemplate('debe tener entre 2 y 20 letras'))
        ->key('sector', v::stringType()->setTemplate('debe ser un sector valido'))
        ->key('precio', v::numericVal()->between(1, 100000)->setTemplate('debe ser un precio valido'))
        ->setTemplate('El campo {{name}} debe estar presente');
        $v->assert($data);
    } 
}

?>
