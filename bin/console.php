<?php

declare(strict_types=1);

// Afriserva Properties CLI Console Runner
require_once __DIR__ . '/../vendor/autoload.php';

// Simple .env Loader
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

use App\Core\CronRunner;

$args = $argv;
array_shift($args); // remove script name
$command = $args[0] ?? 'help';

echo "=== Afriserva Properties CLI Console ===\n";

switch ($command) {
    case 'cron:run':
        $task = $args[1] ?? null;
        echo "Executing scheduled tasks" . ($task ? " (Task: {$task})" : "") . "...\n";
        $results = CronRunner::runAll($task);
        print_r($results);
        echo "All tasks executed.\n";
        break;

    case 'help':
    default:
        echo "Usage: php bin/console.php [command]\n";
        echo "Commands:\n";
        echo "  cron:run [task_name]   Run all or specific background scheduled tasks\n";
        echo "  help                   Show this help menu\n";
        break;
}
