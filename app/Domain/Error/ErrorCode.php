<?php
namespace App\Domain\Error;

class ErrorCode
{
    public const SUCCESS = "SUCCESS";
    public const LOGIN_UNSUCCESSFUL = "LOGIN_UNSUCCESSFUL";
    public const SERVER_ERROR = "SERVER_ERROR";
    public const VALIDATION_ERROR = "VALIDATION_ERROR";
    public const RESOURCE_NOT_FOUND = "RESOURCE_NOT_FOUND";
    public const FORBIDDEN = "FORBIDDEN";
    public const UNAUTHORIZED = "UNAUTHORIZED";
    public const CONFLICT = "CONFLICT";
    public const BUSINESS_RULE_VIOLATION = "BUSINESS_RULE_VIOLATION";
    public const BAD_REQUEST = "BAD_REQUEST";
    public const DATA_INTEGRITY_ERROR = "DATA_INTEGRITY_ERROR";
    public const UNSUPPORTED_MEDIA_TYPE = "UNSUPPORTED_MEDIA_TYPE";
    public const NOT_IMPLEMENTED = "NOT_IMPLEMENTED";
    public const RESOURCE_NOT_CREATED = "RESOURCE_NOT_CREATED";
    public const INTERNAL_ERROR = "INTERNAL_ERROR";
    
    
}