<?php
namespace App\DTO\Request;
use ValidatableRequest;
use Respect\Validation\{
    Validator as v,
    Exceptions\NestedValidationException
};
class MesaRequest implements ValidatableRequest {
    public function __construct(
        public string $id,
    ) {}
        public static function fromArray(array $data): self
    {
        self::validate($data);
        return new self(strtoupper($data['id']));
    }

    public static function validate(array $data): void{
        v::key('id', v::length(5, 5)->setTemplate('El ID debe ser de exactamente 5 caracteres'))
        ->setTemplate('Campo {{name}} requerido')
        ->assert($data);
    } 
}
?>
