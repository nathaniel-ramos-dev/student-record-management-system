<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?User
    {
        $statement = $this->pdo->prepare(
            'SELECT id, full_name, username, email, password_hash, role, last_login_at
             FROM users
             WHERE username = :username OR email = :email
             LIMIT 1',
        );
        $statement->execute(['username' => $username, 'email' => $username]);

        return $this->toUser($statement->fetch());
    }

    public function findById(int $id): ?User
    {
        $statement = $this->pdo->prepare(
            'SELECT id, full_name, username, email, password_hash, role, last_login_at
             FROM users
             WHERE id = :id
             LIMIT 1',
        );
        $statement->execute(['id' => $id]);

        return $this->toUser($statement->fetch());
    }

    private function toUser(mixed $row): ?User
    {
        if (!is_array($row)) {
            return null;
        }

        $normalised = [];

        foreach ($row as $key => $value) {
            if (is_string($key)) {
                $normalised[$key] = $value;
            }
        }

        return User::fromRow($normalised);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $statement->execute(['hash' => $hash, 'id' => $id]);
    }

    public function touchLastLogin(int $id): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
