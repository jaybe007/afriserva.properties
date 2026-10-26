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

            // 1. Base schema: create foundational tables if properties table is absent
            $hasProperties = $pdo->query("SHOW TABLES LIKE 'properties'")->fetch();
            if (!$hasProperties) {
                self::executeSqlFile($pdo, __DIR__ . '/../../database/schema.sql');
            }

            // 2. Incremental migrations: ensure market_articles and subsequent modules exist
            $hasMarketArticles = $pdo->query("SHOW TABLES LIKE 'market_articles'")->fetch();
            if (!$hasMarketArticles) {
                $migrationFiles = [
                    __DIR__ . '/../../database/migrations/003_resolve_oversights.sql',
                    __DIR__ . '/../../database/migrations/004_all_states_and_international.sql',
                    __DIR__ . '/../../database/migrations/004_part2.sql',
                    __DIR__ . '/../../database/005_saved_properties.sql',
                    __DIR__ . '/../../database/006_website_homepage_search_expansion.sql',
                    __DIR__ . '/../../database/007_lgas_and_lead_crm.sql',
                    __DIR__ . '/../../database/008_auth_dashboard_admin_verification.sql',
                    __DIR__ . '/../../database/009_oversights_resolution.sql',
                    __DIR__ . '/../../database/010_messaging_monetization_rentals.sql',
                    __DIR__ . '/../../database/011_database_architecture_and_security.sql',
                    __DIR__ . '/../../database/012_notifications_and_seo.sql',
                    __DIR__ . '/../../database/013_commission_plan_and_transaction_desk.sql',
                ];

                try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;"); } catch (\Throwable $e) {}
                foreach ($migrationFiles as $file) {
                    if (file_exists($file)) {
                        self::executeSqlFile($pdo, $file);
                    }
                }
                try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;"); } catch (\Throwable $e) {}
            }

            // Ensure Migration 013 (Commission Plan and Transaction Desk) is applied
            $hasTransactions = $pdo->query("SHOW TABLES LIKE 'property_transactions'")->fetch();
            if (!$hasTransactions) {
                $file013 = __DIR__ . '/../../database/013_commission_plan_and_transaction_desk.sql';
                if (file_exists($file013)) {
                    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;"); } catch (\Throwable $e) {}
                    self::executeSqlFile($pdo, $file013);
                    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;"); } catch (\Throwable $e) {}
                }
            }

            // 3. Seed baseline records if properties table is empty
            $propCount = 0;
            try {
                $propCount = (int)$pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
            } catch (\Throwable $e) {}

            if ($propCount === 0) {
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
            error_log("Database auto-migration notice: " . $e->getMessage());
        }
    }

    public static function executeSqlFile(PDO $pdo, string $filePath): void
    {
        if (!file_exists($filePath)) {
            return;
        }

        $sql = file_get_contents($filePath);
        if (trim($sql) === '') {
            return;
        }

        // Try direct block execution first
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec($sql);
            return;
        } catch (\Throwable $t) {
            // Fall back to statement-by-statement execution
        } finally {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $buffer = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }
            $buffer .= ' ' . $trimmed;
            if (str_ends_with($trimmed, ';')) {
                $stmt = trim($buffer);
                $buffer = '';
                if ($stmt !== '' && $stmt !== ';') {
                    // Compatibility for MySQL 8 without ADD COLUMN IF NOT EXISTS
                    if (stripos($stmt, 'ALTER TABLE') !== false && stripos($stmt, 'IF NOT EXISTS') !== false) {
                        $stmt = preg_replace('/ADD\s+COLUMN\s+IF\s+NOT\s+EXISTS/i', 'ADD COLUMN', $stmt);
                        $stmt = preg_replace('/ADD\s+IF\s+NOT\s+EXISTS/i', 'ADD', $stmt);
                    }
                    try {
                        $pdo->exec($stmt);
                    } catch (\Throwable $qe) {
                        // Tolerate already-existing columns/tables
                    }
                }
            }
        }
    }
}
