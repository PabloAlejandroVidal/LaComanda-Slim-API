<?php
namespace App\Contracts;
interface ValidatableRequest
{
    public static function fromArray(array $data): self;
    public static function validate(array $data): void;

}
