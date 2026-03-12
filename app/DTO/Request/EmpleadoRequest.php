<?php
namespace App\DTO\Request;

use ValidatableRequest;
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};

class EmpleadoRequest implements ValidatableRequest{
    public function __construct(
        public string $nombre,
        public string $clave,
        public string $email,
        public string $tipo,
    ) {}

    public static function fromArray(array $data): self {
                
        self::validate($data);

        return new self(
            $data['nombre'],
            $data['clave'],
            $data['email'],
            $data['tipo']
        );
    }

    public static function validate($data): void {
                $validador = v::key('nombre', v::alpha()->length(2, 20)->setTemplate('El campo nombre debe tener entre 2 y 20 letras'))
            ->key('email', v::email()->setTemplate('Debe ser un email válido'))
            ->key('tipo', v::stringType()->in(['bartender', 'cervecero', 'cocinero', 'mozo'])->setTemplate('Tipo inválido'))
            ->key('clave', v::length(4, 24)->setTemplate('Clave entre 4 y 24 caracteres'))
            ->setTemplate('Campo {{name}} requerido');
            $validador->assert($data);
    } 
}

?>
