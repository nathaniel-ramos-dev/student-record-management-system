<?php

declare(strict_types=1);

/**
 * ============================================================================
 *  SRMS database migration / seeding script
 * ============================================================================
 *
 *  Usage (from the application root):
 *
 *      php database/migrate.php
 *
 *  Behaviour:
 *   1. Creates the tables/indexes if they do not exist yet (idempotent).
 *   2. Seeds the default accounts and sample records when the users table is
 *      still empty (or when SEED_DATABASE=true is set explicitly).
 *
 *  It supports both MySQL (local Docker / InfinityFree) and PostgreSQL
 *  (Render), selected through the DB_DRIVER environment variable.
 * ============================================================================
 */

use App\Support\Database;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be executed from the command line.\n");
}

require __DIR__ . '/../app/bootstrap.php';

/**
 * Split a SQL file into individual statements.
 *
 * Handles single/double quoted strings, line comments, block comments and
 * PostgreSQL dollar-quoted blocks (used by the updated_at trigger function).
 *
 * @return list<string>
 */
function srms_split_sql(string $sql): array
{
    $statements = [];
    $current = '';
    $length = strlen($sql);
    $inSingle = false;
    $inDouble = false;
    $inLineComment = false;
    $inBlockComment = false;
    $dollarTag = null;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

        if ($inLineComment) {
            if ($char === "\n") {
                $inLineComment = false;
                $current .= $char;
            }

            continue;
        }

        if ($inBlockComment) {
            if ($char === '*' && $next === '/') {
                $inBlockComment = false;
                $i++;
            }

            continue;
        }

        if ($dollarTag !== null) {
            if (substr($sql, $i, strlen($dollarTag)) === $dollarTag) {
                $current .= $dollarTag;
                $i += strlen($dollarTag) - 1;
                $dollarTag = null;

                continue;
            }

            $current .= $char;

            continue;
        }

        if ($inSingle) {
            $current .= $char;

            if ($char === "'") {
                if ($next === "'") {
                    $current .= $next;
                    $i++;
                } else {
                    $inSingle = false;
                }
            }

            continue;
        }

        if ($inDouble) {
            $current .= $char;

            if ($char === '"') {
                if ($next === '"') {
                    $current .= $next;
                    $i++;
                } else {
                    $inDouble = false;
                }
            }

            continue;
        }

        if ($char === '-' && $next === '-') {
            $inLineComment = true;
            $i++;

            continue;
        }

        if ($char === '/' && $next === '*') {
            $inBlockComment = true;
            $i++;

            continue;
        }

        if ($char === "'") {
            $inSingle = true;
            $current .= $char;

            continue;
        }

        if ($char === '"') {
            $inDouble = true;
            $current .= $char;

            continue;
        }

        if ($char === '$'
            && preg_match('/^\$[A-Za-z_][A-Za-z0-9_]*\$|^\$\$/', substr($sql, $i), $matches) === 1
        ) {
            $dollarTag = $matches[0];
            $current .= $dollarTag;
            $i += strlen($dollarTag) - 1;

            continue;
        }

        if ($char === ';') {
            $statement = trim($current);

            if ($statement !== '') {
                $statements[] = $statement;
            }

            $current = '';

            continue;
        }

        $current .= $char;
    }

    $statement = trim($current);

    if ($statement !== '') {
        $statements[] = $statement;
    }

    return $statements;
}

/**
 * @param list<string> $statements
 */
function srms_run_statements(PDO $pdo, array $statements, string $label): void
{
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $exception) {
            $preview = preg_replace('/\s+/', ' ', substr($statement, 0, 140)) ?? $statement;

            throw new RuntimeException(
                sprintf(
                    'Migration failed in %s (statement %d): %s',
                    $label,
                    $index + 1,
                    $preview,
                ),
                0,
                $exception,
            );
        }
    }
}

function srms_file_for(string $driver, string $base): string
{
    $path = __DIR__ . '/' . $base . ($driver === 'pgsql' ? '.pgsql' : '') . '.sql';

    if (!is_file($path)) {
        throw new RuntimeException('SQL file not found: ' . $path);
    }

    return $path;
}

try {
    $driver = Database::driver();
    $pdo = Database::connection();

    $schemaFile = srms_file_for($driver, 'schema');
    $seedFile = srms_file_for($driver, 'seed');

    $schemaSql = file_get_contents($schemaFile);

    if ($schemaSql === false) {
        throw new RuntimeException('Unable to read ' . $schemaFile);
    }

    $schemaStatements = srms_split_sql($schemaSql);
    srms_run_statements($pdo, $schemaStatements, basename($schemaFile));

    echo sprintf(
        "Schema applied: %s (%d statements, driver=%s)\n",
        basename($schemaFile),
        count($schemaStatements),
        $driver,
    );

    $existingUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $forceSeed = filter_var(getenv('SEED_DATABASE') ?: 'false', FILTER_VALIDATE_BOOL);

    if ($forceSeed || $existingUsers === 0) {
        $seedSql = file_get_contents($seedFile);

        if ($seedSql === false) {
            throw new RuntimeException('Unable to read ' . $seedFile);
        }

        $seedStatements = srms_split_sql($seedSql);
        srms_run_statements($pdo, $seedStatements, basename($seedFile));

        echo sprintf(
            "Sample data seeded: %s (%d statements)\n",
            basename($seedFile),
            count($seedStatements),
        );
    } else {
        echo "Sample data skipped: users table already has {$existingUsers} row(s).\n";
    }

    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $studentCount = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
    echo sprintf("Migration complete: %d user(s), %d student(s).\n", $userCount, $studentCount);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration error: ' . $exception->getMessage() . "\n");
    exit(1);
}
