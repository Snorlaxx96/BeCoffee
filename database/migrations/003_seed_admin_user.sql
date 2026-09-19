-- ==============================================================================
-- Migration 003: Seed Default Admin User
-- Email: admin@becoffee.ph
-- Password: AdminBeCoffee2026!
-- ==============================================================================

INSERT INTO users (name, email, password_hash, phone, role)
VALUES (
    'BeCoffee Administrator',
    'admin@becoffee.ph',
    '$2y$12$hdqLASxZyThzEhVEGDtTaOcaUoJSAOxXMAUcL2jgXOE1XdHBW9BaK',
    '+63 917 555 2026',
    'admin'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role = 'admin';
