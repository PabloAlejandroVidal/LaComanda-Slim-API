<?php
declare(strict_types=1);

namespace App\DTO\Request;

use App\Contracts\ValidatableRequest;
use App\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

abstract class AbstractRequestDTO implements ValidatableRequest
{
    final public static function fromArray(array $data): static
    {
        $data = static::normalize($data);
        static::validate($data);

        return static::map($data);
    }

    final public static function validate(array $data): void
    {
        try {
            static::rules()->assert($data);
        } catch (NestedValidationException $e) {
            throw (new ValidationException(previous: $e))
                ->withErrors($e->getMessages());
        }
    }

    protected static function normalize(array $data): array
    {
        return $data;
    }

    abstract protected static function rules(): Validatable;

    abstract protected static function map(array $data): static;
}