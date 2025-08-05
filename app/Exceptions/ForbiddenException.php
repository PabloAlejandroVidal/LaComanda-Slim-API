<?php
namespace App\Exceptions;

class ForbiddenException extends HttpBaseException
{
    public function __construct(string $message = "No tiene permiso para acceder a este recurso") {
        parent::__construct($message, 403, "FORBIDDEN");
    }
}
