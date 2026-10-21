<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/seeds/DatabaseSeeder.php';

use Database\Seeds\DatabaseSeeder;

$host = '127.0.0.1';
$db   = 'afriserva_properties';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $seeder = new DatabaseSeeder($pdo);
    $seeder->run();
} catch (Exception $e) {
    echo "Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
