<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Lazily-created, shared PDO connection.
 *
 * The application supports two drivers:
 *   - mysql  : local development (Docker/MariaDB) and InfinityFree hosting
 *   - pgsql  : Render Postgres in the cloud deployment
 */
final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function driver(): string
    {
        $driver = strtolower(Config::string('db.driver', 'mysql'));

        return in_array($driver, ['mysql', 'pgsql'], true) ? $driver : 'mysql';
    }

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = self::driver();
        $host = Config::string('db.host', '127.0.0.1');
        $port = Config::int('db.port', $driver === 'pgsql' ? 5432 : 3306);
        $name = Config::string('db.name');
        $user = Config::string('db.user');
        $password = Config::string('db.password');
        $charset = Config::string('db.charset', 'utf8mb4');

        $dsn = $driver === 'pgsql'
            ? sprintf(
                'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
                $host,
                $port,
                $name,
                Config::string('db.sslmode', 'prefer'),
            )
            : sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $host,
                $port,
                $name,
                $charset,
            );

        try {
            $connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);

            self::configureTimeZone($connection, $driver);
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());

            throw new RuntimeException(
                'Unable to connect to the database. Check the DB_* environment variables.',
                0,
                $exception,
            );
        }

        self::$connection = $connection;

        return $connection;
    }

    /**
     * Used by tests or long-running workers to drop the shared connection.
     */
    public static function disconnect(): void
    {
        self::$connection = null;
    }

    private static function configureTimeZone(PDO $connection, string $driver): void
    {
        $timezone = new DateTimeZone(Config::string('app.timezone', 'Asia/Manila'));

        if ($driver === 'pgsql') {
            // PostgreSQL understands names such as "Asia/Manila".
            $connection->exec('SET TIME ZONE ' . $connection->quote($timezone->getName()));

            return;
        }

        // MySQL stores only a numeric offset in this session variable.
        $offset = (new DateTimeImmutable('now', $timezone))->format('P');
        $connection->exec('SET time_zone = ' . $connection->quote($offset));
    }
}
