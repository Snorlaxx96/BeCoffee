-- ==============================================================================
-- Migration 004: Escobar Cafe OMS Operations Schema Extensions
-- Adds Queue Numbers (#104), Order Types (Dine-in/Take-out), Payment Methods (Cash/GCash),
-- QA Lockout Audit Flags, and Line-Item Customization Specs
-- ==============================================================================

USE becoffee_db;

-- 1. Alter orders table to support OMS workflow
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS queue_number INT NOT NULL DEFAULT 100 AFTER id,
    ADD COLUMN IF NOT EXISTS order_type ENUM('dine_in', 'take_out') NOT NULL DEFAULT 'dine_in' AFTER order_reference,
    ADD COLUMN IF NOT EXISTS table_number VARCHAR(20) NULL AFTER order_type,
    ADD COLUMN IF NOT EXISTS payment_method ENUM('cash', 'gcash') NOT NULL DEFAULT 'cash' AFTER table_number,
    ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'verified') NOT NULL DEFAULT 'unpaid' AFTER payment_method,
    MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS qa_payment_verified BOOLEAN NOT NULL DEFAULT FALSE AFTER status,
    ADD COLUMN IF NOT EXISTS qa_customizations_followed BOOLEAN NOT NULL DEFAULT FALSE AFTER qa_payment_verified,
    ADD COLUMN IF NOT EXISTS qa_packaging_secured BOOLEAN NOT NULL DEFAULT FALSE AFTER qa_customizations_followed,
    ADD COLUMN IF NOT EXISTS completed_at TIMESTAMP NULL AFTER created_at,
    ADD INDEX IF NOT EXISTS idx_order_queue (queue_number),
    ADD INDEX IF NOT EXISTS idx_order_status (status),
    ADD INDEX IF NOT EXISTS idx_order_date_status (created_at, status);

-- 2. Alter order_items table to store drink customization specs
ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS temperature ENUM('Hot', 'Iced') NOT NULL DEFAULT 'Iced' AFTER item_name,
    ADD COLUMN IF NOT EXISTS milk_option VARCHAR(50) NOT NULL DEFAULT 'Regular Milk' AFTER temperature,
    ADD COLUMN IF NOT EXISTS sweetness_level VARCHAR(50) NOT NULL DEFAULT 'Normal (100%)' AFTER milk_option,
    ADD COLUMN IF NOT EXISTS custom_notes VARCHAR(255) NULL AFTER sweetness_level;
