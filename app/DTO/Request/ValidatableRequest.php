<?php
interface ValidatableRequest
{
    public static function fromArray(array $data): self;
}
