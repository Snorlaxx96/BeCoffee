-- ==============================================================================
-- Migration 006: 4-Tier RBAC Schema & Seed Accounts
-- Defines 4 Roles: superadmin (developer secret), admin, staff, customer
-- ==============================================================================

USE becoffee_db;

-- 1. Enforce Role ENUM constraint on users table
ALTER TABLE users 
    MODIFY COLUMN role ENUM('superadmin', 'admin', 'staff', 'customer') NOT NULL DEFAULT 'customer';

-- 2. Seed Superadmin (Developer Secret) Account
-- Login: superadmin / superadmin123 (or dev@becoffee.internal / superadmin123)
INSERT INTO users (name, email, password_hash, role)
VALUES (
    'BeCoffee SuperAdmin (Developer)',
    'dev@becoffee.internal',
    '$2y$12$jo9iRyLPl7SKWzQL18TrkuwFwrNwbZstCoGkZZ8ZVa36.XMgG1bNa',
    'superadmin'
)
ON DUPLICATE KEY UPDATE 
    role = 'superadmin',
    password_hash = VALUES(password_hash),
    name = VALUES(name);

-- 3. Seed Staff (Barista / POS Station) Account
-- Login: staff / staff123 (or staff@becoffee.ph / staff123)
INSERT INTO users (name, email, password_hash, role)
VALUES (
    'BeCoffee Counter Staff',
    'staff@becoffee.ph',
    '$2y$12$9lUp4HrI3TzHgxGThvvzy.2LWySHvnTM4yRyvoIzDsQQGBVd1Q58K',
    'staff'
)
ON DUPLICATE KEY UPDATE 
    role = 'staff',
    password_hash = VALUES(password_hash),
    name = VALUES(name);

-- 4. Ensure Admin Account Has 'admin' Role & Seeded Password (admin / admin123)
UPDATE users 
SET role = 'admin',
    password_hash = '$2y$12$0ttQMzL8qIDUTU.gDyePlugiaEp1wUGwpJYGoQNrse9h8C9W9W3Qy'
WHERE email IN ('admin', 'admin@becoffee.ph');
