<?php
namespace App\Exceptions;

class BusinessRuleException extends HttpBaseException
{
    public function __construct(string $message = "Violación de regla de negocio") {
        parent::__construct($message, 400, "BUSINESS_RULE_ERROR");
    }
}

