-- ============================================================================
--  SRMS — Sample data
--
--  Import this file AFTER schema.sql.
--
--  Default accounts (change these passwords after your first login):
--    Administrator : admin / Admin@12345
--    Staff         : staff / Staff@12345
--
--  Password hashes below were generated with PHP's password_hash() using
--  the bcrypt algorithm (PASSWORD_DEFAULT).
-- ============================================================================

SET NAMES utf8mb4;

-- ----------------------------------------------------------------------------
-- Users
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO users (id, full_name, username, email, password_hash, role, last_login_at)
VALUES
    (1, 'System Administrator', 'admin', 'admin@srms.local',
     '$2y$10$Hhdpkk.7wiYGViuBSptPbefexk4FNrsAZGG0Zul0WQaM2aXvAMCS.',
     'admin', NULL),
    (2, 'Registrar Staff', 'staff', 'staff@srms.local',
     '$2y$10$b73uPhAu9Xa9jIh874XIhe3fL6MgtL/xU6Aq1vSane8yITEVqgDZO',
     'staff', NULL);

-- ----------------------------------------------------------------------------
-- Students (24 sample records)
-- created_at is spread over the last few months so the dashboard has data.
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO students
    (student_no, first_name, middle_name, last_name, email, phone, gender, birth_date,
     course, year_level, address, status, created_at)
VALUES
    ('2023-00101', 'Maria', 'Santos', 'Dela Cruz', 'maria.delacruz@example.com', '09171234001', 'Female', '2003-04-12',
     'BS Information Technology', 3, 'Barangay 5, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 3 DAY)),

    ('2023-00102', 'John Paul', 'Reyes', 'Garcia', 'johnpaul.garcia@example.com', '09171234002', 'Male', '2002-11-08',
     'BS Information Technology', 3, 'Barangay Ubanon, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 5 DAY)),

    ('2024-00103', 'Angela Marie', 'Bautista', 'Lopez', 'angela.lopez@example.com', '09171234003', 'Female', '2005-01-25',
     'BS Computer Science', 2, 'Barangay 8, Calbayog City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 8 DAY)),

    ('2022-00104', 'Mark Anthony', 'Villanueva', 'Ramos', 'mark.ramos@example.com', '09171234004', 'Male', '2001-07-19',
     'BS Accountancy', 4, 'Barangay Poblacion 2, Gandara, Samar', 'Graduated', DATE_SUB(NOW(), INTERVAL 210 DAY)),

    ('2025-00105', 'Sophia Grace', 'Mendoza', 'Torres', 'sophia.torres@example.com', '09171234005', 'Female', '2006-09-03',
     'BS Nursing', 1, 'Barangay 12, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 2 DAY)),

    ('2024-00106', 'Joshua Caleb', 'Ramos', 'Aquino', 'joshua.aquino@example.com', '09171234006', 'Male', '2004-02-14',
     'BS Civil Engineering', 2, 'Barangay San Policarpo, Calbayog City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 33 DAY)),

    ('2023-00107', 'Nicole Anne', 'Aquino', 'Salazar', 'nicole.salazar@example.com', '09171234007', 'Female', '2003-12-30',
     'BS Business Administration', 3, 'Barangay 3, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 60 DAY)),

    ('2025-00108', 'Daniel Joseph', 'Navarro', 'Fernandez', 'daniel.fernandez@example.com', '09171234008', 'Male', '2006-05-17',
     'BS Criminology', 1, 'Barangay Lagundi, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 1 DAY)),

    ('2022-00109', 'Patricia Mae', 'Domingo', 'Gonzales', 'patricia.gonzales@example.com', '09171234009', 'Female', '2001-03-06',
     'BS Education', 4, 'Barangay 7, Calbayog City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 240 DAY)),

    ('2024-00110', 'Christian Dave', 'Salazar', 'Mercado', 'christian.mercado@example.com', '09171234010', 'Male', '2004-08-22',
     'BS Hospitality Management', 2, 'Barangay 11, Catbalogan City, Samar', 'Inactive', DATE_SUB(NOW(), INTERVAL 45 DAY)),

    ('2023-00111', 'Jasmine Rose', 'Fernandez', 'Castro', 'jasmine.castro@example.com', '09171234011', 'Female', '2003-10-11',
     'BS Information Systems', 3, 'Barangay 1, Basey, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 90 DAY)),

    ('2025-00112', 'Rafael Luis', 'Gonzales', 'Flores', 'rafael.flores@example.com', '09171234012', 'Male', '2006-07-28',
     'BS Information Technology', 1, 'Barangay 6, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 7 DAY)),

    ('2024-00113', 'Hannah Beatrice', 'Castro', 'Ortiz', 'hannah.ortiz@example.com', '09171234013', 'Female', '2005-04-09',
     'BS Computer Science', 2, 'Barangay 9, Calbayog City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 28 DAY)),

    ('2022-00114', 'Emmanuel', 'Torres', 'Pascual', 'emmanuel.pascual@example.com', '09171234014', 'Male', '2000-11-16',
     'BS Accountancy', 4, 'Barangay 4, Catbalogan City, Samar', 'Graduated', DATE_SUB(NOW(), INTERVAL 300 DAY)),

    ('2023-00115', 'Alyssa Joy', 'Mercado', 'Soriano', 'alyssa.soriano@example.com', '09171234015', 'Female', '2003-06-21',
     'BS Nursing', 3, 'Barangay 10, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 75 DAY)),

    ('2025-00116', 'Gabriel Isaiah', 'Flores', 'Velasco', 'gabriel.velasco@example.com', '09171234016', 'Male', '2006-02-02',
     'BS Civil Engineering', 1, 'Barangay 2, Calbayog City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 4 DAY)),

    ('2024-00117', 'Camille Andrea', 'Ortiz', 'Lim', 'camille.lim@example.com', '09171234017', 'Female', '2005-05-13',
     'BS Business Administration', 2, 'Barangay 13, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 22 DAY)),

    ('2023-00118', 'Lorenzo Miguel', 'Pascual', 'Yap', 'lorenzo.yap@example.com', '09171234018', 'Male', '2002-09-27',
     'BS Criminology', 3, 'Barangay 1, Sta. Rita, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 120 DAY)),

    ('2022-00119', 'Danielle Kate', 'Soriano', 'Castillo', 'danielle.castillo@example.com', '09171234019', 'Female', '2001-01-18',
     'BS Education', 4, 'Barangay Talisay, Calbayog City, Samar', 'Graduated', DATE_SUB(NOW(), INTERVAL 365 DAY)),

    ('2025-00120', 'Ethan James', 'Velasco', 'Domingo', 'ethan.domingo@example.com', '09171234020', 'Male', '2006-12-05',
     'BS Hospitality Management', 1, 'Barangay 8, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 6 DAY)),

    ('2024-00121', 'Isabella Faith', 'Lim', 'Navarro', 'isabella.navarro@example.com', '09171234021', 'Female', '2004-03-15',
     'BS Information Technology', 2, 'Barangay 5, Calbayog City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 14 DAY)),

    ('2023-00122', 'Nathan Elijah', 'Ramos', 'Bautista', 'nathan.bautista@example.com', '09171234022', 'Male', '2003-08-08',
     'BS Information Systems', 3, 'Barangay 7, Catbalogan City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 50 DAY)),

    ('2025-00123', 'Samantha Claire', 'Yap', 'Dela Cruz', 'samantha.delacruz@example.com', '09171234023', 'Female', '2006-10-19',
     'BS Computer Science', 1, 'Barangay 3, Calbayog City, Samar', 'Active', DATE_SUB(NOW(), INTERVAL 9 DAY)),

    ('2024-00124', 'Adrian Paul', 'Castillo', 'Mendoza', 'adrian.mendoza@example.com', '09171234024', 'Male', '2004-07-07',
     'BS Accountancy', 2, 'Barangay 12, Catbalogan City, Samar', 'Inactive', DATE_SUB(NOW(), INTERVAL 40 DAY));

-- ----------------------------------------------------------------------------
-- Audit trail sample (only when the table is still empty)
-- ----------------------------------------------------------------------------
INSERT INTO activity_logs (user_id, action, entity, entity_id, description, ip_address, created_at)
SELECT seed.user_id, seed.action, seed.entity, seed.entity_id, seed.description, seed.ip_address, seed.created_at
FROM (
    SELECT 1 AS user_id, 'create_student' AS action, 'student' AS entity, 1 AS entity_id,
           'Created student record 2023-00101 — Maria S. Dela Cruz.' AS description,
           '127.0.0.1' AS ip_address, DATE_SUB(NOW(), INTERVAL 3 DAY) AS created_at
    UNION ALL
    SELECT 2, 'create_student', 'student', 5,
           'Created student record 2025-00105 — Sophia G. Torres.',
           '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY)
    UNION ALL
    SELECT 1, 'update_student', 'student', 10,
           'Updated student record 2024-00110 — Christian D. Mercado.',
           '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 DAY)
    UNION ALL
    SELECT 2, 'create_student', 'student', 12,
           'Created student record 2025-00112 — Rafael L. Flores.',
           '127.0.0.1', DATE_SUB(NOW(), INTERVAL 7 HOUR)
    UNION ALL
    SELECT 1, 'login', 'auth', 1,
           'System Administrator signed in.',
           '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 HOUR)
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM activity_logs);
