<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class NotFoundException extends HttpBaseException
{
    protected int $statusCode = 401;
    protected string $status = ErrorCode::NOT_IMPLEMENTED;
    protected string $defaultMessage = 'Endpoint aún no implementado.';
}