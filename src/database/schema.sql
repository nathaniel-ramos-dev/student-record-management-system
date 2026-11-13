-- ============================================================================
--  SRMS — Student Record Management System
--  Database schema for MySQL 5.7+ / MariaDB 10.4+
--
--  HOW TO IMPORT ON INFINITYFREE
--  1. Open vPanel -> MySQL Databases and create a database.
--  2. Open phpMyAdmin for that database.
--  3. Select the database in the left sidebar.
--  4. Open the "Import" tab and upload this file (schema.sql).
--  5. Repeat with seed.sql if you want the sample records.
--
--  This file does NOT create or select a database, so it works with the
--  auto-generated InfinityFree database name (e.g. if0_38214567_srms).
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+08:00';

-- ----------------------------------------------------------------------------
-- Staff accounts
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(120) NOT NULL,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    last_login_at DATETIME NULL DEFAULT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Student records
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_no  VARCHAR(30)  NOT NULL,
    first_name  VARCHAR(80)  NOT NULL,
    middle_name VARCHAR(80)  NULL DEFAULT NULL,
    last_name   VARCHAR(80)  NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  NULL DEFAULT NULL,
    gender      ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Other',
    birth_date  DATE NULL DEFAULT NULL,
    course      VARCHAR(100) NOT NULL,
    year_level  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    address     VARCHAR(255) NULL DEFAULT NULL,
    status      ENUM('Active', 'Inactive', 'Graduated') NOT NULL DEFAULT 'Active',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_students_student_no (student_no),
    UNIQUE KEY uq_students_email (email),
    KEY idx_students_name (last_name, first_name),
    KEY idx_students_course (course),
    KEY idx_students_year_level (year_level),
    KEY idx_students_status (status),
    KEY idx_students_created_at (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Audit trail
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NULL DEFAULT NULL,
    action      VARCHAR(50)  NOT NULL,
    entity      VARCHAR(50)  NOT NULL,
    entity_id   INT UNSIGNED NULL DEFAULT NULL,
    description VARCHAR(255) NOT NULL,
    ip_address  VARCHAR(45)  NULL DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activity_entity (entity, entity_id),
    KEY idx_activity_created_at (created_at),
    KEY idx_activity_user_id (user_id),
    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
