<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class InternalServerException extends HttpBaseException
{
    protected int $statusCode = 500;
    protected string $status = ErrorCode::INTERNAL_ERROR;
    protected string $defaultMessage = 'Error interno del servidor';
}