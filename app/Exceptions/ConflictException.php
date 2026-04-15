<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class ConflictException extends HttpBaseException
{
    protected int $statusCode = 409;
    protected string $status = ErrorCode::CONFLICT;
    protected string $defaultMessage = 'Conflicto con el estado actual del recurso';
}