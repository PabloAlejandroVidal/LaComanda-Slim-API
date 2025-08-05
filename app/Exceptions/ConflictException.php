<?php
namespace App\Exceptions;

class ConflictException extends HttpBaseException
{
    public function __construct(string $message = "Conflicto con el estado actual del recurso") {
        parent::__construct($message, 409, "CONFLICT");
    }
}
