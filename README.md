# Student Record Management System (SRMS)

A student record management system for a university registrar, built with
**PHP 8.3**, **PDO**, and **MySQL/MariaDB** locally, with **PostgreSQL** support
for the live Render deployment.

**Live demo:** <https://srms-2bk6.onrender.com>

> The live database is on Render's free Postgres plan and expires on
> **2026-12-13**. See [docs/DEPLOYMENT-RENDER.md](docs/DEPLOYMENT-RENDER.md) for
> the full hosting guide, backup steps, and upgrade path.

---

## Default accounts

| Role | Username | Password |
| --- | --- | --- |
| Administrator | `admin` | `Admin@12345` |
| Registrar Staff | `staff` | `Staff@12345` |

These passwords are seeded for demonstration. **Change them immediately** from
the user menu → *Change password* after the first login.

---

## Features

- Secure session authentication with `password_hash()` / `password_verify()`
- CSRF protection on every state-changing request
- Prepared statements for all database access
- Student CRUD with server-side validation
- Search by student number, name, or e-mail
- Filters for course, year level, and status
- Pagination and CSV export
- Dashboard statistics: totals, active/inactive/graduated, gender split,
  new enrollees this month, students per course and year level
- Audit trail of logins and student changes
- Authenticated password change with current-password verification
- Responsive Bootstrap 5 interface
- MySQL/MariaDB and PostgreSQL support selected via `DB_DRIVER`
- Docker-based local development and production image for Render

---

## Technology

| Layer | Choice |
| --- | --- |
| Language | PHP 8.3, `declare(strict_types=1)` |
| Database | MySQL 8 / MariaDB 11 locally; PostgreSQL 18 on Render |
| Data access | PDO, prepared statements, small repository layer |
| Routing | Minimal custom router with method + path parameters |
| Views | Plain PHP templates, Bootstrap 5 via CDN |
| Web server | Apache 2.4 (Docker) |
| Tests | PHPUnit 11 (26 tests) |
| Static analysis | PHPStan level 9 |
| Cloud | Render Docker web service + Render Postgres |

---

## Local development (Docker + MySQL)

### Requirements

- Docker with the Compose plugin

### Steps

```bash
cp .env.example .env
# Optional: edit the passwords in .env
docker compose up -d --build
```

Open:

- Application: <http://localhost:8080>
- phpMyAdmin: <http://localhost:8081>

The MariaDB container automatically imports `src/database/schema.sql` and
`src/database/seed.sql` the first time its volume is created. To reset the
database completely:

```bash
docker compose down -v
docker compose up -d
```

### Environment variables (local)

| Variable | Purpose |
| --- | --- |
| `DB_DRIVER` | `mysql` (default) or `pgsql` |
| `DB_HOST` | Database host (`db` in Compose) |
| `DB_PORT` | `3306` for MySQL, `5432` for PostgreSQL |
| `DB_NAME` | Database name |
| `DB_USER` | Application database user |
| `DB_PASSWORD` | Application database password |
| `DB_SSLMODE` | PostgreSQL SSL mode (default `prefer`) |
| `APP_DEBUG` | `true` shows errors; use `false` in production |
| `APP_URL` | Optional fixed base URL; auto-detected when empty |
| `RUN_MIGRATIONS` | `true` runs the idempotent migration at container boot |
| `SEED_DATABASE` | `true` forces sample data even when users exist |

---

## Project structure

```
.
├── Dockerfile                  # PHP 8.3 + Apache production image
├── docker-entrypoint.sh        # $PORT binding + optional migrations
├── compose.yml                 # Local web + MariaDB + phpMyAdmin stack
├── render.yaml                 # Optional Render Blueprint
├── docs/
│   └── DEPLOYMENT-RENDER.md    # Step-by-step Render CLI hosting guide
├── src/
│   ├── index.php               # Front controller and routes
│   ├── app/
│   │   ├── Controllers/        # Auth, dashboard, students, activity, profile
│   │   ├── Models/             # Student, User
│   │   ├── Repositories/       # PDO data access
│   │   └── Support/            # Config, Database, Auth, Csrf, Router, ...
│   ├── config/config.php       # Environment-driven configuration
│   ├── database/
│   │   ├── schema.sql          # MySQL schema
│   │   ├── seed.sql            # MySQL sample data
│   │   ├── schema.pgsql.sql    # PostgreSQL schema
│   │   ├── seed.pgsql.sql      # PostgreSQL sample data
│   │   └── migrate.php         # Idempotent CLI migration/seed runner
│   ├── assets/css/style.css
│   └── views/                  # PHP templates
├── tests/                      # PHPUnit tests
├── phpstan.neon
└── phpunit.xml
```

The web root is `src/`. Configuration and database folders are protected by
`.htaccess` files and should never be exposed directly by a web server.

---

## Running the tests

PHPUnit:

```bash
docker run --rm -u 1001:1001 -v "$PWD:/app" -w /app php:8.3-cli \
  php vendor/bin/phpunit --colors=never
```

PHPStan level 9:

```bash
docker run --rm -u 1001:1001 -v "$PWD:/app" -w /app php:8.3-cli \
  php vendor/bin/phpstan analyse --no-progress --error-format=raw
```

Expected result: **26 tests, 41 assertions, all passing**, and **0 PHPStan
errors**.

---

## Deployment

The application is deployed on Render with the Render CLI:

- Docker web service: `srms` (`srv-dbrd0autlv9c7385mlgg`)
- PostgreSQL database: `srms-db` (`dpg-dbrcvtl9fdbs73e17cag-a`)
- Health check: `/health`

See **[docs/DEPLOYMENT-RENDER.md](docs/DEPLOYMENT-RENDER.md)** for:

- the exact CLI commands used to create the database and web service
- environment variable reference
- migration and seeding behaviour
- logs, redeploys, restarts, and psql access
- backup and restore procedures
- free-plan limits and the 30-day database expiry
- troubleshooting
- the local MySQL alternative

---

## Security notes

- `.env` is ignored by Git; only `.env.example` is committed.
- Database credentials are stored as Render environment variables, not in the
  repository.
- All SQL uses prepared statements with bound parameters.
- Output is escaped with `htmlspecialchars()` through the `e()` helper.
- Sessions are `HttpOnly`, `SameSite=Lax`, and `Secure` when HTTPS is detected.
- POST requests require a valid CSRF token.
- The change-password flow requires the current password and enforces a minimum
  length plus letter/number complexity.
- Change the seeded passwords before sharing the deployment.
