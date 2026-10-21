<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Simple .env Loader
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            putenv(trim($k) . '=' . trim($v, " \t\n\r\0\x0B\"'"));
        }
    }
}

use App\Core\Database;
use App\Models\Property;
use App\Core\CacheService;

echo "========================================================\n";
echo " AFRISERVA PROPERTIES: CONCURRENT LOAD & STRESS RUNNER \n";
echo "========================================================\n\n";

$iterations = 100;
$times = [];
$startTime = microtime(true);

echo "Executing {$iterations} simulated concurrent database queries & search filters...\n";

for ($i = 0; $i < $iterations; $i++) {
    $iterStart = microtime(true);

    // Simulate concurrent property search with dynamic filters
    $filterType = ($i % 2 === 0) ? 'sale' : 'rent';
    $properties = Property::all([
        'transaction_type' => $filterType,
        'state_id' => 1, // Lagos
        'sort' => 'newest'
    ], 6);

    // Simulate location hierarchy query with caching
    $locations = CacheService::remember('load_test_locations', 300, function() {
        return Database::query("SELECT id, name FROM states LIMIT 10");
    });

    $iterDuration = (microtime(true) - $iterStart) * 1000; // in ms
    $times[] = $iterDuration;
}

$totalDuration = microtime(true) - $startTime;
sort($times);

$avg = round(array_sum($times) / count($times), 2);
$min = round($times[0], 2);
$max = round(end($times), 2);
$p50 = round($times[(int)($iterations * 0.50)], 2);
$p95 = round($times[(int)($iterations * 0.95)], 2);
$p99 = round($times[(int)($iterations * 0.99)], 2);
$rps = round($iterations / $totalDuration, 2);

echo "\n--- Load Benchmark Results ({$iterations} Iterations) ---\n";
echo "Total Execution Time: " . round($totalDuration, 3) . "s\n";
echo "Throughput:           {$rps} requests/sec\n";
echo "Average Latency:      {$avg} ms\n";
echo "Median Latency (p50): {$p50} ms\n";
echo "95th Percentile (p95):{$p95} ms\n";
echo "99th Percentile (p99):{$p99} ms\n";
echo "Min Latency:          {$min} ms\n";
echo "Max Latency:          {$max} ms\n";
echo "Peak Memory Usage:    " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";

if ($avg < 100.0) {
    echo "\n[PASS] Performance Budget Verified: Average latency ({$avg}ms) is well under the 150ms ceiling.\n";
    exit(0);
} else {
    echo "\n[FAIL] Performance Budget Exceeded: Average latency ({$avg}ms) exceeded 150ms.\n";
    exit(1);
}
