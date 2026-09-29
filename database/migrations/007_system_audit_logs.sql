-- ==============================================================================
-- Migration 007: System Audit Logs Table
-- Lightweight, indexed event log for SuperAdmin (Developer) security oversight
-- ==============================================================================

USE becoffee_db;

CREATE TABLE IF NOT EXISTS system_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_email VARCHAR(150) NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'system',
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_created_at (created_at),
    INDEX idx_audit_action (action),
    INDEX idx_audit_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed initial installation log events
INSERT INTO system_audit_logs (user_id, user_email, role, action, details, ip_address)
VALUES 
    (3, 'superadmin', 'superadmin', 'SCHEMA_MIGRATION', 'Migration 007 executed: system_audit_logs created.', '127.0.0.1'),
    (3, 'superadmin', 'superadmin', 'RBAC_INIT', 'SuperAdmin developer security suite initialized.', '127.0.0.1');
