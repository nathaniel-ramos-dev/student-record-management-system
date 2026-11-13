<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Value;

/**
 * Immutable application user.
 */
final readonly class User
{
    public function __construct(
        public int $id,
        public string $fullName,
        public string $username,
        public string $email,
        public string $passwordHash,
        public string $role,
        public ?string $lastLoginAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: Value::int($row, 'id'),
            fullName: Value::string($row, 'full_name'),
            username: Value::string($row, 'username'),
            email: Value::string($row, 'email'),
            passwordHash: Value::string($row, 'password_hash'),
            role: Value::string($row, 'role', 'staff'),
            lastLoginAt: isset($row['last_login_at']) ? Value::string($row, 'last_login_at') : null,
        );
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->fullName)) ?: [];

        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1));
        }

        return mb_strtoupper(
            mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1),
        );
    }
}
