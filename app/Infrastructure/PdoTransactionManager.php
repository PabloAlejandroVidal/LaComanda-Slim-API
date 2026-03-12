<?php

namespace App\Infrastructure;

use App\Contracts\TransactionManager;
use PDO;

class PdoTransactionManager implements TransactionManager
{
    public function __construct(private PDO $pdo) {}

    public function transactional(callable $callback): mixed
    {
        try {
            $this->pdo->beginTransaction();

            $result = $callback();

            $this->pdo->commit();

            return $result;

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}