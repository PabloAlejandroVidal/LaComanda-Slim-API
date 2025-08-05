<?php
namespace App\Exceptions;

class ValidationException extends HttpBaseException
{
        public function __construct(array $errores = []) {
        parent::__construct(
            "Datos inválidos",
            422,
            "VALIDATION_ERROR",
            $errores
        );
    }
}
