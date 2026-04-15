<?php
namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class ValidationException extends HttpBaseException
{
    protected int $statusCode = 422;
    protected string $status = ErrorCode::VALIDATION_ERROR;
    protected string $defaultMessage = 'Datos inválidos';
}