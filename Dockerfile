FROM php:8.3-apache

# ---------------------------------------------------------------------------
# SRMS image
# ---------------------------------------------------------------------------
# Supports both database drivers used by the project:
#   - pdo_mysql  : local Docker stack and InfinityFree hosting
#   - pdo_pgsql  : Render Postgres (cloud deployment)
# ---------------------------------------------------------------------------

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_mysql mysqli pdo_pgsql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Match the timezone used by the application and database.
RUN ln -snf /usr/share/zoneinfo/Asia/Manila /etc/localtime \
    && echo "Asia/Manila" > /etc/timezone

WORKDIR /var/www/html

# The application source is baked into the production image. During local
# development `compose.yml` mounts ./src over this directory instead.
COPY src/ /var/www/html/

COPY docker-entrypoint.sh /usr/local/bin/srms-entrypoint
RUN chmod +x /usr/local/bin/srms-entrypoint

# Render assigns the port at runtime through $PORT (usually 10000).
EXPOSE 80 10000

ENTRYPOINT ["srms-entrypoint"]
CMD ["apache2-foreground"]
