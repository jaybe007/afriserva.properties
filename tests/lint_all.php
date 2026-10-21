<?php

$dir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$errors = [];
$checked = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        if (str_contains($path, 'vendor')) {
            continue;
        }
        $output = [];
        $returnVar = 0;
        $phpBin = PHP_BINARY;
        exec("\"{$phpBin}\" -l \"{$path}\"", $output, $returnVar);
        $checked++;
        if ($returnVar !== 0) {
            $errors[] = $path . ': ' . implode(' ', $output);
        }
    }
}

echo "Linted {$checked} PHP files across application.\n";
if (!empty($errors)) {
    echo "SYNTAX ERRORS DETECTED:\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
    exit(1);
} else {
    echo "CLEAN: 100% of non-vendor PHP files pass syntax verification.\n";
    exit(0);
}
