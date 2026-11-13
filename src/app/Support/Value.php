<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Safe, type-narrowing readers for loosely-typed arrays such as PDO rows,
 * $_POST payloads and session data.
 *
 * PHPStan level 9 treats every offset of array<string, mixed> as mixed. These
 * helpers perform the narrowing once so the rest of the codebase can work with
 * real strings, integers and booleans.
 */
final class Value
{
    /**
     * Convert any scalar (or null) into a trimmed string.
     */
    public static function toString(mixed $value, string $default = '', bool $trim = true): string
    {
        if (!is_scalar($value)) {
            return $default;
        }

        $string = (string) $value;

        return $trim ? trim($string) : $string;
    }

    /**
     * Convert any numeric scalar (or null) into an integer.
     */
    public static function toInt(mixed $value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $default;
    }

    /**
     * Convert any boolean-ish scalar into a boolean.
     */
    public static function toBool(mixed $value, bool $default = false): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOL);
        }

        return $default;
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public static function string(array $data, string $key, string $default = '', bool $trim = true): string
    {
        return self::toString($data[$key] ?? null, $default, $trim);
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public static function int(array $data, string $key, int $default = 0): int
    {
        return self::toInt($data[$key] ?? null, $default);
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public static function bool(array $data, string $key, bool $default = false): bool
    {
        return self::toBool($data[$key] ?? null, $default);
    }

    /**
     * @param array<mixed, mixed> $data
     * @return array<mixed, mixed>
     */
    public static function array(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        return is_array($value) ? $value : [];
    }
}
