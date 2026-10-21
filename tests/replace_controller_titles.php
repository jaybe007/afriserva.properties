<?php

$dir = __DIR__ . '/../app/Controllers';
$files = glob($dir . '/*.php');

$replacements = [
    '| Afriserva Properties' => '| Afriserva Real Estate',
    'Afriserva Properties.' => 'Afriserva Real Estate.',
    'Afriserva Properties!' => 'Afriserva Real Estate!'
];

$count = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    $orig = $content;
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    if ($content !== $orig) {
        file_put_contents($file, $content);
        $count++;
        echo "Updated: " . basename($file) . "\n";
    }
}

echo "Total Controllers updated: $count\n";
