-- Migration 005: Option Stock Availability Table
-- Supports real-time cancellation / 86'ing of cafe drink customization options

CREATE TABLE IF NOT EXISTS option_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_type VARCHAR(50) NOT NULL,
    option_key VARCHAR(100) NOT NULL UNIQUE,
    option_label VARCHAR(100) NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed standard drink customization options
INSERT INTO option_availability (category_type, option_key, option_label, is_available) VALUES
('temperature', 'Iced', 'Iced', 1),
('temperature', 'Hot', 'Hot', 1),
('milk', 'Regular Milk', 'Regular Milk (+₱0)', 1),
('milk', 'Oat Milk', 'Oat Milk (+₱30)', 1),
('milk', 'Almond Milk', 'Almond Milk (+₱30)', 1),
('sweetness', 'Normal (100%)', '100% Normal', 1),
('sweetness', 'Less Sweet (75%)', '75% Less Sweet', 1),
('sweetness', 'Half Sweet (50%)', '50% Half Sweet', 1),
('sweetness', 'No Sugar (0%)', '0% No Sugar', 1)
ON DUPLICATE KEY UPDATE option_label = VALUES(option_label);
