<?php
namespace App\Exceptions;

abstract class HttpBaseException extends \Exception implements HttpException
{
    public function __construct(
        string $message = "",
        protected int $statusCode = 500,
        protected string $status = "INTERNAL_ERROR",
        protected array $errors = [] 
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getStatus(): string {
        return $this->status;
    }
    public function getErrors(): array {
        return $this->errors;
    }
}
