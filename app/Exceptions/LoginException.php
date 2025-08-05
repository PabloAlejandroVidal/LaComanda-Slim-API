<?php
namespace App\Exceptions;

class LoginException extends HttpBaseException
{
    public function __construct(string $mensaje = "No se pudo iniciar sesión") {
        parent::__construct($mensaje, 401, "LOGIN_UNSUCCESSFUL");
    }
}
