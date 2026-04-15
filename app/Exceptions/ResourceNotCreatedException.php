<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

final class ResourceNotCreatedException extends HttpBaseException
{
    protected int $statusCode = 500;
    protected string $status = ErrorCode::RESOURCE_NOT_CREATED;
    protected string $defaultMessage = 'No se pudo crear el recurso';
}