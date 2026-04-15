<?php

namespace App\Exceptions;

use App\Domain\Error\ErrorCode;

abstract class HttpBaseException extends \RuntimeException
{
    protected int $statusCode = 500;
    protected string $status = ErrorCode::SERVER_ERROR;
    protected string $defaultMessage = 'Error interno del servidor';
    protected array $errors = [];

    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message ?? $this->defaultMessage, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function withErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }
}