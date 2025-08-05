<?php
namespace App\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

class ValidationHelper
{
    public static function validate(array $input, Validatable $schema): array
    {
        try {
            return $schema->assert($input) ?: $input;
        } catch (NestedValidationException $e) {
            throw new \DomainException($e->getFullMessage());
        }
    }
}
