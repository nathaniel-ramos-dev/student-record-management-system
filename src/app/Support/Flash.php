<?php

declare(strict_types=1);

namespace App\Support;

/**
 * One-request flash messages stored in the session.
 */
final class Flash
{
    private const SESSION_KEY = '_flash';

    public static function set(string $type, string $message): void
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];

        if (!is_array($messages)) {
            $messages = [];
        }

        $messages[] = [
            'type'    => $type,
            'message' => $message,
        ];

        $_SESSION[self::SESSION_KEY] = $messages;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    public static function info(string $message): void
    {
        self::set('info', $message);
    }

    /**
     * @return list<array{type: string, message: string}>
     */
    public static function pull(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);

        if (!is_array($messages)) {
            return [];
        }

        $result = [];

        foreach ($messages as $message) {
            if (!is_array($message)) {
                continue;
            }

            $type = $message['type'] ?? null;
            $text = $message['message'] ?? null;

            if (!is_string($type) || !is_string($text)) {
                continue;
            }

            $result[] = ['type' => $type, 'message' => $text];
        }

        return $result;
    }
}
