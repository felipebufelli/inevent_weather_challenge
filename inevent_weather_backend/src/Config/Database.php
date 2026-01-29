<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '5432';
                $dbname = $_ENV['DB_NAME'] ?? 'inevent_weather';
                $user = $_ENV['DB_USER'] ?? 'inevent_user';
                $password = $_ENV['DB_PASSWORD'] ?? 'inevent_pass';

                $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

                self::$connection = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \Exception('Erro de conexão com o banco de dados: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}
