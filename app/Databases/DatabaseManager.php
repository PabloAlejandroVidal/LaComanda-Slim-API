<?php

namespace App\Databases;

use App\Config\DatabaseConfig;
use PDO;

class DatabaseManager {
    private $connection;

    public function __construct()
    {
        $this->connect();
    }
    public function getDb(){
        return $this->connection;
    }
    private function connect()
    {
        $config = DatabaseConfig::get();
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8";

        try {
            $this->connection = new PDO($dsn, $config['user'], $config['pass']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Error al conectar con la base de datos: " . $e->getMessage());
        }
    }
}

?>
