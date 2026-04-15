<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class NotFoundException extends HttpBaseException
{
    protected int $statusCode = 404;
    protected string $status = ErrorCode::RESOURCE_NOT_FOUND;
    protected string $defaultMessage = 'Recurso no encontrado.';
}