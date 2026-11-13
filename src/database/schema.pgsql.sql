-- ============================================================================
--  SRMS — Student Record Management System
--  Database schema for PostgreSQL 14+ (Render Postgres)
--
--  This file is idempotent: it can be executed on every deployment/restart.
--  It is applied automatically by database/migrate.php when RUN_MIGRATIONS
--  is set to "true" (see render.yaml / docs/DEPLOYMENT-RENDER.md).
-- ============================================================================

SET TIME ZONE 'Asia/Manila';

-- ----------------------------------------------------------------------------
-- Staff accounts
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,
    full_name     VARCHAR(120) NOT NULL,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(10)  NOT NULL DEFAULT 'staff',
    last_login_at TIMESTAMP    NULL DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_username UNIQUE (username),
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT chk_users_role CHECK (role IN ('admin', 'staff'))
);

-- ----------------------------------------------------------------------------
-- Student records
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id          SERIAL PRIMARY KEY,
    student_no  VARCHAR(30)  NOT NULL,
    first_name  VARCHAR(80)  NOT NULL,
    middle_name VARCHAR(80)  NULL DEFAULT NULL,
    last_name   VARCHAR(80)  NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  NULL DEFAULT NULL,
    gender      VARCHAR(10)  NOT NULL DEFAULT 'Other',
    birth_date  DATE         NULL DEFAULT NULL,
    course      VARCHAR(100) NOT NULL,
    year_level  SMALLINT     NOT NULL DEFAULT 1,
    address     VARCHAR(255) NULL DEFAULT NULL,
    status      VARCHAR(15)  NOT NULL DEFAULT 'Active',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_students_student_no UNIQUE (student_no),
    CONSTRAINT uq_students_email UNIQUE (email),
    CONSTRAINT chk_students_gender CHECK (gender IN ('Male', 'Female', 'Other')),
    CONSTRAINT chk_students_year_level CHECK (year_level BETWEEN 1 AND 5),
    CONSTRAINT chk_students_status CHECK (status IN ('Active', 'Inactive', 'Graduated'))
);

CREATE INDEX IF NOT EXISTS idx_students_name ON students (last_name, first_name);
CREATE INDEX IF NOT EXISTS idx_students_course ON students (course);
CREATE INDEX IF NOT EXISTS idx_students_year_level ON students (year_level);
CREATE INDEX IF NOT EXISTS idx_students_status ON students (status);
CREATE INDEX IF NOT EXISTS idx_students_created_at ON students (created_at);

-- ----------------------------------------------------------------------------
-- Audit trail
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    id          BIGSERIAL PRIMARY KEY,
    user_id     INTEGER      NULL DEFAULT NULL,
    action      VARCHAR(50)  NOT NULL,
    entity      VARCHAR(50)  NOT NULL,
    entity_id   INTEGER      NULL DEFAULT NULL,
    description VARCHAR(255) NOT NULL,
    ip_address  VARCHAR(45)  NULL DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_activity_entity ON activity_logs (entity, entity_id);
CREATE INDEX IF NOT EXISTS idx_activity_created_at ON activity_logs (created_at);
CREATE INDEX IF NOT EXISTS idx_activity_user_id ON activity_logs (user_id);

-- ----------------------------------------------------------------------------
-- Keep updated_at in sync (PostgreSQL has no ON UPDATE CURRENT_TIMESTAMP)
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION srms_set_updated_at()
RETURNS TRIGGER AS $srms$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$srms$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_users_updated_at ON users;
CREATE TRIGGER trg_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION srms_set_updated_at();

DROP TRIGGER IF EXISTS trg_students_updated_at ON students;
CREATE TRIGGER trg_students_updated_at
    BEFORE UPDATE ON students
    FOR EACH ROW
    EXECUTE FUNCTION srms_set_updated_at();
