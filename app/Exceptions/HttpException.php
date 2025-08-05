<?php
namespace App\Exceptions;

interface HttpException {
    public function getStatusCode(): int;
    public function getStatus(): string;
}
