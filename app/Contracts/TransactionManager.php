<?php

namespace App\Contracts;

interface TransactionManager
{
    public function transactional(callable $callback): mixed;
}