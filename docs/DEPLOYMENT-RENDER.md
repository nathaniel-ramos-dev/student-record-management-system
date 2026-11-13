# Deploying SRMS to Render with the Render CLI

> A formatted Word version of this guide is available at
> [`docs/DEPLOYMENT-RENDER.docx`](DEPLOYMENT-RENDER.docx). The full project
> documentation is available at
> [`docs/SRMS-PROJECT-DOCUMENTATION.docx`](SRMS-PROJECT-DOCUMENTATION.docx).

This document is the step-by-step hosting guide for the **Student Record
Management System (SRMS)**. It reproduces the live deployment that is already
running at:

| Item | Value |
| --- | --- |
| Live application | <https://srms-2bk6.onrender.com> |
| Source repository | <https://github.com/nathaniel-ramos-dev/student-record-management-system> |
| Render web service | `srms` / `srv-dbrd0autlv9c7385mlgg` |
| Render Postgres database | `srms-db` / `dpg-dbrcvtl9fdbs73e17cag-a` |
| Region | Singapore |
| Web plan | Free (Docker) |
| Database plan | Free Postgres 18 |
| Database name | `srms_nz3p` (Render may suffix the requested name) |
| Database user | `srms` |
| Database internal host | `dpg-dbrcvtl9fdbs73e17cag-a` |
| Database expiry | **2026-12-13** (30 days after creation) |

> **Exam note:** the activity allows MySQL **or** PostgreSQL. Render does not
> offer managed MySQL, so this deployment uses Render Postgres. The application
> still runs on MySQL/MariaDB locally and on shared hosting through the same
> PDO codebase (`DB_DRIVER=mysql|pgsql`).

---

## 1. Architecture

```
Browser (HTTPS)
      |
      v
Render web service "srms"  (Docker, PHP 8.3 + Apache)
      |  internal private network, no SSL required
      v
Render Postgres "srms-db"  (PostgreSQL 18, free plan)
```

- The Docker image is built from `Dockerfile` in the repository root.
- Apache listens on Render's `$PORT` (usually `10000`).
- `docker-entrypoint.sh` runs `database/migrate.php` before Apache starts when
  `RUN_MIGRATIONS=true`.
- The migration script creates the PostgreSQL schema and seeds the default
  accounts and sample students **only when the `users` table is empty**.
- Render terminates TLS; the application detects HTTPS through
  `HTTP_X_FORWARDED_PROTO` so secure session cookies and generated URLs work.

---

## 2. Prerequisites

1. A Render account (<https://render.com>).
2. The Render CLI installed:

   ```bash
   # macOS (Homebrew)
   brew install render

   # Linux / WSL (official install script)
   curl -fsSL https://raw.githubusercontent.com/render-oss/cli/main/bin/install | bash
   ```

   Verify with:

   ```bash
   render --version
   ```

3. A GitHub repository containing this project.
4. A Render API session:

   ```bash
   render login
   ```

   The CLI prints a device code and a URL. Open the URL, approve the login, and
   confirm it succeeded:

   ```bash
   render whoami
   ```

---

## 3. Create the PostgreSQL database

```bash
render postgres create \
  --name srms-db \
  --plan free \
  --region singapore \
  --database-name srms \
  --database-user srms \
  --version 18 \
  --confirm \
  --output json
```

Render may suffix the database name (for example `srms_nz3p`). Note the values
in the JSON response: `id`, `databaseName`, and the final `status`. The
database is ready when `status` is `available`.

### Read the connection details

```bash
render postgres get srms-db --include-sensitive-connection-info --output json
```

Copy the following values (never commit the password to Git):

- `connectionInfo.internalConnectionString` — use this inside Render
- `connectionInfo.externalConnectionString` — use this from your own computer
- `connectionInfo.password`

The internal connection string looks like:

```
postgresql://srms:PASSWORD@dpg-xxxxxxxxxxxx-a/srms_nz3p
```

---

## 4. Create the web service

Run the command below from the project directory. Replace the repository URL if
you forked the project, and replace `DB_PASSWORD` with the password returned in
the previous step.

```bash
render services create \
  --name srms \
  --type web_service \
  --runtime docker \
  --plan free \
  --region singapore \
  --repo https://github.com/nathaniel-ramos-dev/student-record-management-system \
  --branch main \
  --health-check-path /health \
  --env-var DB_DRIVER=pgsql \
  --env-var DB_HOST=dpg-dbrcvtl9fdbs73e17cag-a \
  --env-var DB_PORT=5432 \
  --env-var DB_NAME=srms_nz3p \
  --env-var DB_USER=srms \
  --env-var DB_PASSWORD='<database-password>' \
  --env-var DB_SSLMODE=prefer \
  --env-var RUN_MIGRATIONS=true \
  --env-var APP_DEBUG=false \
  --confirm \
  --output json
```

Notes:

- `DB_HOST` must be the **internal** hostname (the short `dpg-...-a` form),
  not the `*.singapore-postgres.render.com` external host.
- `DB_SSLMODE=prefer` works for Render's internal network.
- `RUN_MIGRATIONS=true` applies the schema and seeds the database on the first
  boot. The migration is idempotent, so later restarts are safe.
- `APP_DEBUG=false` hides stack traces from users. Set it to `true` temporarily
  in the Render Dashboard when diagnosing a 500 error.
- The CLI returns the public URL, for example
  `https://srms-2bk6.onrender.com`.

> Render CLI 2.35 does not have an `env` update subcommand. To change
> environment variables after creation, open the service in the Render
> Dashboard → **Environment** → edit → save. The service redeploys
> automatically. Alternatively, manage the whole stack with the included
> `render.yaml` Blueprint.

---

## 5. Wait for the deploy and verify

```bash
# Watch deploy status
render deploys list srv-dbrd0autlv9c7385mlgg

# Or trigger a fresh deploy and wait for it to finish
render deploys create srv-dbrd0autlv9c7385mlgg --wait
```

Verify the health endpoint:

```bash
curl -i https://srms-2bk6.onrender.com/health
```

Expected response:

```
HTTP/2 200
content-type: text/plain; charset=UTF-8

SRMS OK - database connected
```

View the migration output:

```bash
render logs \
  --resources srv-dbrd0autlv9c7385mlgg \
  --text "Migration" \
  --limit 50 \
  --output text
```

Expected lines:

```
Running database migrations...
Migration complete: 2 user(s), 24 student(s).
```

---

## 6. First login and acceptance test

Open <https://srms-2bk6.onrender.com/login>.

| Role | Username | Password |
| --- | --- | --- |
| Administrator | `admin` | `Admin@12345` |
| Registrar Staff | `staff` | `Staff@12345` |

**Change both passwords immediately** through the user menu → *Change
password*. The seeded accounts are public knowledge.

A successful acceptance test covers:

1. Sign in as `admin`.
2. Dashboard shows 24 students and summary cards.
3. Students list loads, filters, and paginates.
4. Create a student, view it, edit it, then delete it.
5. Export the student list as CSV.
6. Open the activity log.
7. Change password, sign out, and sign in with the new password.
8. `GET /health` returns HTTP 200 with `SRMS OK - database connected`.

The deployment in this repository passed all of the above after going live.

---

## 7. Migrations and seeding

The migration script is `src/database/migrate.php` (copied into the image at
`/var/www/html/database/migrate.php`).

It automatically selects the correct SQL files:

| Driver | Schema | Seed |
| --- | --- | --- |
| `pgsql` | `src/database/schema.pgsql.sql` | `src/database/seed.pgsql.sql` |
| `mysql` | `src/database/schema.sql` | `src/database/seed.sql` |

Behaviour:

- The schema uses `CREATE TABLE IF NOT EXISTS` / `CREATE INDEX IF NOT EXISTS`,
  so it is safe to run on every boot.
- Seed data is inserted with `ON CONFLICT DO NOTHING` (PostgreSQL) or
  `INSERT IGNORE` (MySQL), and only when the `users` table is empty.
- To force seed data on an existing deployment, set `SEED_DATABASE=true` in the
  Render Dashboard and redeploy.

Manual run (when you have shell access to the service):

```bash
php /var/www/html/database/migrate.php
```

---

## 8. Day-to-day operations

### View logs

```bash
render logs --resources srv-dbrd0autlv9c7385mlgg --tail
```

### Deploy the latest commit

```bash
render deploys create srv-dbrd0autlv9c7385mlgg --wait
```

Auto-deploy is enabled: pushing to `main` on GitHub triggers a build.

### Restart the service

```bash
render restart srv-dbrd0autlv9c7385mlgg
```

### List resources

```bash
render services --output json
render postgres list --output json
```

### Open a PostgreSQL shell

Render's CLI connects to the database from your computer, so your public IP
must be allowed first:

```bash
# Allow your current IP
render postgres update srms-db \
  --ip-allow-list "cidr=$(curl -s https://api.ipify.org)/32,description=admin-cli" \
  --confirm

# Requires the psql client to be installed locally
render psql srms-db

# When finished, remove the external rule again
render postgres update srms-db --clear-ip-allow-list --confirm
```

The Render Dashboard → database → **Connect** page also offers a browser-based
query tool and the external connection string.

---

## 9. Backup and restore

Render Postgres is a managed database, but a manual logical backup before
experiments or before the free-plan expiry is strongly recommended.

### Create a backup

1. Allow your current IP (see above).
2. Read the external connection string:

   ```bash
   render postgres get srms-db --include-sensitive-connection-info --output json
   ```

3. Run `pg_dump`:

   ```bash
   export DATABASE_URL='postgresql://srms:PASSWORD@dpg-xxxxx.singapore-postgres.render.com:5432/srms_nz3p?sslmode=require'
   pg_dump "$DATABASE_URL" --no-owner --no-privileges > srms-backup-$(date +%F).sql
   ```

4. Remove the temporary IP rule:

   ```bash
   render postgres update srms-db --clear-ip-allow-list --confirm
   ```

### Restore a backup

```bash
psql "$DATABASE_URL" < srms-backup-2026-11-13.sql
```

Store backups somewhere outside Render (for example an encrypted archive or a
private cloud bucket). Do not commit database dumps to the repository.

---

## 10. Free-plan limits and the 30-day database expiry

Render's free instances are intended for demos and coursework, not production.

| Limitation | Impact |
| --- | --- |
| Web service spins down after 15 minutes without traffic | The next request has a cold start of roughly 30–60 seconds. |
| 512 MB RAM / shared CPU | Fine for this small application; not for heavy concurrency. |
| Ephemeral filesystem | Uploaded files would be lost on redeploy. SRMS stores everything in PostgreSQL, so this is not an issue. |
| Free Postgres: 1 GB storage, one per workspace | Enough for the demo dataset. |
| **Free Postgres expires 30 days after creation** | This database expires on **2026-12-13**. There is a short grace period, after which the database is unavailable. |

### Keeping the deployment alive

Upgrade the database to a paid plan before the expiry date:

```bash
render postgres update srms-db --plan 0.5c-1g --confirm
```

Check current prices on Render's pricing page before upgrading. After the plan
change, the expiry date is removed. The web service can remain on the free plan.

Alternative: export a backup, create a new paid database, restore the dump, and
update `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the Render
Dashboard.

---

## 11. Troubleshooting

| Symptom | Likely cause | Fix |
| --- | --- | --- |
| `/health` returns 503 `database unavailable` | Wrong `DB_*` values or database still creating | Run `render postgres get srms-db --output json`; check `status`; re-check env vars. |
| Deploy fails during Docker build | Missing `src/` files or Dockerfile syntax | Run `docker build -t srms-test .` locally; inspect `render deploys list`. |
| 500 error after login | PHP/runtime error | Set `APP_DEBUG=true`, redeploy, read `render logs`; turn debug off afterwards. |
| Login says invalid credentials | Seed did not run (users table not empty) | Set `SEED_DATABASE=true` and redeploy, or reset the password from the database. |
| Redirects use `http://` or cookies are rejected | Proxy HTTPS detection | Ensure the app is running this version; `is_https()` reads `HTTP_X_FORWARDED_PROTO`. |
| `psql: connection refused` / IP not allowed | External access blocked | Add your IP to the allow list, then clear it afterwards. |
| Cold start is very slow | Free web service spun down | Expected on the free plan; open the URL and wait. |

Useful commands:

```bash
render logs --resources srv-dbrd0autlv9c7385mlgg --limit 100 --output text
render deploys list srv-dbrd0autlv9c7385mlgg --output json
render services --output json
```

---

## 12. Optional: deploy from `render.yaml` (Blueprint)

The repository contains `render.yaml`, which declares the same web service and
database. It is useful for reproducing the environment or for managing
environment variables declaratively.

1. Push `render.yaml` to GitHub.
2. In the Render Dashboard choose **New + → Blueprint** and select the
   repository.
3. Render creates/updates the resources described in the file.

The CLI can validate the file after authentication:

```bash
render blueprints validate ./render.yaml
```

The primary deployment path used for this project is the CLI commands in
sections 3–5.

---

## 13. Local MySQL alternative (for comparison)

The same codebase runs locally with MySQL/MariaDB:

```bash
cp .env.example .env
# edit .env with local passwords if desired
docker compose up -d --build
```

- Application: <http://localhost:8080>
- phpMyAdmin: <http://localhost:8081>
- The database is seeded automatically from `src/database/schema.sql` and
  `src/database/seed.sql` the first time the volume is created.

Run the quality gates:

```bash
docker run --rm -u 1001:1001 -v "$PWD:/app" -w /app php:8.3-cli \
  php vendor/bin/phpunit --colors=never

docker run --rm -u 1001:1001 -v "$PWD:/app" -w /app php:8.3-cli \
  php vendor/bin/phpstan analyse --no-progress --error-format=raw
```

Expected: 26 PHPUnit tests pass and PHPStan level 9 reports no errors.
