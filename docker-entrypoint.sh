#!/bin/sh
# ---------------------------------------------------------------------------
# SRMS container entrypoint
# ---------------------------------------------------------------------------
# On Render, RUN_MIGRATIONS=true applies the schema and seeds the default
# accounts before Apache starts. The migration script is idempotent.
# ---------------------------------------------------------------------------
set -e

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running database migrations..."
    php /var/www/html/database/migrate.php
fi

PORT="${PORT:-80}"

if [ "$PORT" != "80" ]; then
    echo "Binding Apache to port ${PORT}..."
    sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

exec "$@"
