<?php

declare(strict_types=1);

namespace App\Core;

class CacheService
{
    private static string $cacheDir = __DIR__ . '/../../storage/cache';

    /**
     * Initialize cache storage directory
     */
    private static function init(): void
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0775, true);
        }
    }

    /**
     * Get item from cache or return default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::init();
        $file = self::getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return $default;
        }

        $data = @unserialize($content);
        if ($data === false || !is_array($data) || !isset($data['expires_at'], $data['value'])) {
            @unlink($file);
            return $default;
        }

        if ($data['expires_at'] !== 0 && time() > $data['expires_at']) {
            @unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set item in cache with TTL in seconds (0 = persistent)
     */
    public static function set(string $key, mixed $value, int $ttlSeconds = 3600): bool
    {
        self::init();
        $file = self::getFilePath($key);
        $expiresAt = ($ttlSeconds > 0) ? (time() + $ttlSeconds) : 0;

        $payload = serialize([
            'expires_at' => $expiresAt,
            'created_at' => time(),
            'value' => $value
        ]);

        return (bool) file_put_contents($file, $payload, LOCK_EX);
    }

    /**
     * Remember an item in cache, computing it via callback if not present
     */
    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        self::set($key, $value, $ttlSeconds);
        return $value;
    }

    /**
     * Delete an item from cache
     */
    public static function delete(string $key): bool
    {
        self::init();
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * Flush all cached items
     */
    public static function clear(): bool
    {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        return true;
    }

    /**
     * Generate secure filename hash for cache key
     */
    private static function getFilePath(string $key): string
    {
        return self::$cacheDir . '/' . hash('xxh128', $key) . '.cache';
    }
}
