<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $db   = getenv('DB_DATABASE') ?: 'afriserva_properties';
            $user = getenv('DB_USERNAME') ?: 'root';
            $pass = getenv('DB_PASSWORD') ?: '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Failure: " . $e->getMessage());
                throw new Exception("Enterprise Database connection unavailable. Please check configuration.");
            }
        }

        return self::$instance;
    }

    public static function getInstance(): PDO
    {
        return self::connect();
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function lastInsertId(): string
    {
        return self::connect()->lastInsertId();
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connect();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
