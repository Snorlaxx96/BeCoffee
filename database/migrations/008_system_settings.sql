-- Migration 008: System Settings & QR Table Ordering Controls
-- Allows Admin and SuperAdmin to toggle operational features (e.g. table_qr_ordering_enabled)

CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` VARCHAR(64) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) 
VALUES 
  ('table_qr_ordering_enabled', '1', 'Master switch for customer in-store table QR ordering'),
  ('table_count', '10', 'Number of active physical dining tables in the cafe')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);
