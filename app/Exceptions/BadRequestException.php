<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class BadRequestException extends HttpBaseException
{
    protected int $statusCode = 400;
    protected string $status = ErrorCode::BAD_REQUEST;
    protected string $defaultMessage = 'Solicitud inválida';
}