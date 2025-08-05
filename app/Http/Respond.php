<?php
namespace App\Http;

class Respond {
    public function __construct(
        public bool $success,
        public string $status,
        public string $message,
        public mixed $data = null,
        public array $errors = [],
        public int $statusCode = 200,
    ) {}

    public static function make(
        int $statusCode,
        string $message,
        mixed $data = null,
        array $errors = [],
        string $status = null
    ): self {
        return new self(
            success: $statusCode < 400,
            status: $status ?? ($statusCode < 400 ? 'OK' : 'ERROR'),
            message: $message,
            data: $data,
            errors: $errors,
            statusCode: $statusCode
        );
    }

    public function toArray(): array {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}
