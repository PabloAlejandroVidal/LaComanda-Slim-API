<?php

namespace App\Config;

class DatabaseConfig {

    public static function get(): array
    {
        $host     = $_ENV['DB_HOST'] ?? 'localhost';
        $port     = $_ENV['DB_PORT'] ?? '3306';
        $dbname   = $_ENV['DB_DATABASE'] ?? 'la_comanda';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        return [
            'host' => $host,
            'port' => $port,
            'name' => $dbname,
            'user' => $username,
            'pass' => $password,
        ];
    }
}
?>
