<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class ForbiddenException extends HttpBaseException
{
    protected int $statusCode = 403;
    protected string $status = ErrorCode::FORBIDDEN;
    protected string $defaultMessage = 'Acceso denegado';
}