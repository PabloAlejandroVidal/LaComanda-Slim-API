<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class UnauthorizedException extends HttpBaseException
{
    protected int $statusCode = 401;
    protected string $status = ErrorCode::UNAUTHORIZED;
    protected string $defaultMessage = 'No autorizado';
}