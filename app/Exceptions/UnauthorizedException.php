<?php
namespace App\Exceptions;

class UnauthorizedException extends HttpBaseException
{
    public function __construct(string $message = "No autenticado") {
        parent::__construct($message, 401, "UNAUTHORIZED");
    }
}
