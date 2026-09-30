-- ==============================================================================
-- Migration 009: Escobar Cafe Order Origin Tracking
-- Adds order_source ENUM ('qr_link', 'registrar', 'online') to orders table
-- ==============================================================================

USE becoffee_db;

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS order_source ENUM('qr_link', 'registrar', 'online') NOT NULL DEFAULT 'qr_link' AFTER order_type,
    ADD INDEX IF NOT EXISTS idx_order_source (order_source);

-- Backfill historical and mock orders based on order characteristics
UPDATE orders
SET order_source = CASE
    WHEN order_type = 'take_out' AND (table_number IS NULL OR table_number = '') THEN 'online'
    WHEN table_number IS NOT NULL AND table_number != '' THEN 'qr_link'
    ELSE 'registrar'
END
WHERE order_source = 'qr_link';
