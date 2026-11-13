<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use App\Repositories\UserRepository;
use Throwable;

/**
 * Session-based authentication with simple brute-force throttling.
 */
final class Auth
{
    private const SESSION_KEY   = 'auth_user';
    private const THROTTLE_KEY  = '_login_throttle';
    private const MAX_ATTEMPTS  = 5;
    private const LOCKOUT_SECS  = 300;

    /**
     * @return array{id: int, full_name: string, username: string, email: string, role: string}|null
     */
    public static function user(): ?array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_array($user) || !isset($user['id'])) {
            return null;
        }

        return [
            'id'        => Value::int($user, 'id'),
            'full_name' => Value::string($user, 'full_name'),
            'username'  => Value::string($user, 'username'),
            'email'     => Value::string($user, 'email'),
            'role'      => Value::string($user, 'role', 'staff'),
        ];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function require(): void
    {
        if (self::check()) {
            return;
        }

        Flash::error('Please sign in to continue.');
        redirect('/login');
    }

    public static function attempt(string $username, string $password): bool
    {
        if (self::lockedOut()) {
            return false;
        }

        try {
            $users = new UserRepository(Database::connection());
            $user = $users->findByUsername($username);
        } catch (Throwable $exception) {
            error_log('Login lookup failed: ' . $exception->getMessage());

            return false;
        }

        $passwordHash = $user instanceof User ? $user->passwordHash : '';

        if (!$user instanceof User || $passwordHash === '' || !password_verify($password, $passwordHash)) {
            self::recordFailure();

            return false;
        }

        if (password_needs_rehash($passwordHash, PASSWORD_DEFAULT)) {
            $users->updatePasswordHash($user->id, password_hash($password, PASSWORD_DEFAULT));
        }

        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id'        => $user->id,
            'full_name' => $user->fullName,
            'username'  => $user->username,
            'email'     => $user->email,
            'role'      => $user->role,
        ];

        unset($_SESSION[self::THROTTLE_KEY]);

        $users->touchLastLogin($user->id);

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    public static function lockedOut(): bool
    {
        return self::throttle()['locked_until'] > time();
    }

    public static function lockoutSecondsRemaining(): int
    {
        return max(0, self::throttle()['locked_until'] - time());
    }

    private static function recordFailure(): void
    {
        $throttle = self::throttle();

        $count = $throttle['count'] + 1;
        $lockedUntil = $count >= self::MAX_ATTEMPTS ? time() + self::LOCKOUT_SECS : 0;

        $_SESSION[self::THROTTLE_KEY] = [
            'count'        => $count,
            'locked_until' => $lockedUntil,
        ];
    }

    /**
     * @return array{count: int, locked_until: int}
     */
    private static function throttle(): array
    {
        $throttle = $_SESSION[self::THROTTLE_KEY] ?? null;

        if (!is_array($throttle)) {
            return ['count' => 0, 'locked_until' => 0];
        }

        return [
            'count'        => Value::int($throttle, 'count'),
            'locked_until' => Value::int($throttle, 'locked_until'),
        ];
    }
}
