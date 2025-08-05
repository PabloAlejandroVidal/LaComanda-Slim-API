<?php
namespace App\DTO;

use ValidatableRequest;
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};

class UsuarioRequest implements ValidatableRequest{
    public function __construct(
        public string $email,
        public string $clave,
    ) {}

    public static function fromArray(array $data): self {
                
        self::validate($data);
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El campo email no es válido');
        }

        return new self(
            $data['email'],
            $data['clave'],
        );
    }

    public static function validate($data): void {
                $validador = v::key('email', v::email()->setTemplate('Debe ser un email válido'))
            ->key('clave', v::length(4, 24)->setTemplate('Clave entre 4 y 24 caracteres'))
            ->setTemplate('Campo {{name}} requerido');
            $validador->assert($data);
    } 
}

?>
