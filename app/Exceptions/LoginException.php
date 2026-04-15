<?php
namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class LoginException extends HttpBaseException
{
    protected int $statusCode = 401;
    protected string $status = ErrorCode::LOGIN_UNSUCCESSFUL;
    protected string $defaultMessage = 'Credenciales inválidas.';

}