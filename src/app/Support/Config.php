<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Read-only access to the application configuration array.
 */
final class Config
{
    /** @var array<mixed, mixed> */
    private static array $items = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Configuration file not found: %s', $path));
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new RuntimeException('The configuration file must return an array.');
        }

        self::$items = $config;
    }

    /**
     * Read a value using dot notation, e.g. "db.host".
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);

        return is_scalar($value) ? (string) $value : $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOL);
        }

        return is_int($value) ? $value !== 0 : $default;
    }
}
