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

    public static function autoMigrateIfEmpty(): void
    {
        try {
            $pdo = self::connect();
            $check = $pdo->query("SHOW TABLES LIKE 'properties'")->fetch();
            if (!$check) {
                $schemaFile = __DIR__ . '/../../database/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    try {
                        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                        $pdo->exec($sql);
                    } catch (\Throwable $t) {
                        // Split statements on semicolon if batch exec fails
                        $statements = array_filter(array_map('trim', explode(';', $sql)));
                        foreach ($statements as $stmt) {
                            if (!empty($stmt)) {
                                try {
                                    $pdo->exec($stmt);
                                } catch (\Throwable $st) {
                                    // Continue with next statement
                                }
                            }
                        }
                    } finally {
                        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                    }
                }

                $seederFile = __DIR__ . '/../../database/seeds/DatabaseSeeder.php';
                if (file_exists($seederFile)) {
                    require_once $seederFile;
                    ob_start();
                    try {
                        $seeder = new \Database\Seeds\DatabaseSeeder($pdo);
                        $seeder->run();
                    } catch (\Throwable $se) {
                        error_log("Database Seeding notice: " . $se->getMessage());
                    }
                    ob_end_clean();
                }
            }
        } catch (\Throwable $e) {
            error_log("Database initialization notice: " . $e->getMessage());
        }
    }
}
