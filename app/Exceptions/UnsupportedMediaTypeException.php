<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class UnsupportedMediaTypeException extends HttpBaseException
{
    protected int $statusCode = 415;
    protected string $status = ErrorCode::UNSUPPORTED_MEDIA_TYPE;
    protected string $defaultMessage = 'Tipo de contenido no soportado';
}