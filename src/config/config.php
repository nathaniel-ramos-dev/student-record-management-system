<?php

declare(strict_types=1);

/**
 * ============================================================================
 *  SRMS APPLICATION CONFIGURATION
 * ============================================================================
 *
 *  LOCAL (Docker / XAMPP):  the values below are read from environment
 *  variables first, so `docker compose up` works without editing this file.
 *
 *  INFINITYFREE / CPANEL:  environment variables are usually not available on
 *  free hosting, so replace the fallback values (the text after "?:") with the
 *  details shown in vPanel -> MySQL Databases.
 *
 *  IMPORTANT FOR INFINITYFREE
 *  --------------------------
 *  - The database host is NOT "localhost". It looks like:
 *        sql301.infinityfree.com
 *  - The database name and username look like:
 *        if0_38214567_srms
 *        if0_38214567
 *  - Keep this file OUTSIDE of public access (the included .htaccess in this
 *    folder blocks direct browser access).
 * ============================================================================
 */

return [
    'app' => [
        'name'         => 'SRMS',
        'full_name'    => 'Student Record Management System',
        'school'       => 'Samar State University',
        'timezone'     => 'Asia/Manila',
        'debug'        => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL),
        // Leave empty to auto-detect the base URL. Set it manually if the
        // application lives in a sub-folder and links are generated wrongly.
        'url'          => (string) (getenv('APP_URL') ?: ''),
        'session_name' => 'srms_session',
        'per_page'     => 10,
    ],

    'db' => [
        // "mysql" (local Docker / InfinityFree) or "pgsql" (Render Postgres).
        'driver'   => (string) (getenv('DB_DRIVER') ?: 'mysql'),
        'host'     => (string) (getenv('DB_HOST') ?: 'sql301.infinityfree.com'),
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'name'     => (string) (getenv('DB_NAME') ?: 'if0_00000000_srms'),
        'user'     => (string) (getenv('DB_USER') ?: 'if0_00000000'),
        'password' => (string) (getenv('DB_PASSWORD') ?: 'CHANGE_ME'),
        'charset'  => 'utf8mb4',
        // PostgreSQL only. Render's internal connections do not require SSL;
        // use "require" when connecting from outside Render.
        'sslmode'  => (string) (getenv('DB_SSLMODE') ?: 'prefer'),
    ],
];
