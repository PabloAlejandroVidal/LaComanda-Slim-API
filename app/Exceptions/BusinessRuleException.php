<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

class BusinessRuleException extends HttpBaseException
{
    protected int $statusCode = 409;
    protected string $status = ErrorCode::BUSINESS_RULE_VIOLATION;
    protected string $defaultMessage = 'La operación no puede realizarse por una regla de negocio';
}