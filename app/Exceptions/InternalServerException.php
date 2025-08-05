<?php
namespace App\Exceptions;

class InternalServerException extends HttpBaseException
{
    public function __construct(string $message = "Ocurrió un error inesperado") {
        parent::__construct($message, 500, "INTERNAL_ERROR");
    }
}
