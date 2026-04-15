<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class DataIntegrityException extends HttpBaseException
{
    protected int $statusCode = 500;
    protected string $status = ErrorCode::DATA_INTEGRITY_ERROR;
    protected string $defaultMessage = 'Se detectó una inconsistencia interna de datos';
}