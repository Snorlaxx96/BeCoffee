-- ==============================================================================
-- BeCoffee — Supabase Complete PostgreSQL Schema & Storage Setup
-- Platform: Supabase (PostgreSQL 15+)
-- Run this script in your Supabase Project Dashboard -> SQL Editor
-- ==============================================================================

-- 1. Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ==============================================================================
-- 2. CORE APPLICATION TABLES
-- ==============================================================================

-- 2.1 Users & Accounts (Linked to Supabase Auth)
CREATE TABLE IF NOT EXISTS public.users (
    id BIGSERIAL PRIMARY KEY,
    auth_user_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) DEFAULT 'SUPABASE_AUTH',
    phone VARCHAR(30) NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'customer',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_users_email ON public.users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON public.users(role);

-- 2.2 Menu Categories
CREATE TABLE IF NOT EXISTS public.categories (
    id SERIAL PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_categories_slug ON public.categories(slug);

-- 2.3 Menu Items
CREATE TABLE IF NOT EXISTS public.menu_items (
    id VARCHAR(64) PRIMARY KEY,
    category_id INT NOT NULL REFERENCES public.categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    name VARCHAR(150) NOT NULL,
    origin_notes VARCHAR(150) NULL,
    elevation_info VARCHAR(100) NULL,
    price NUMERIC(10,2) NOT NULL,
    price_iced_m NUMERIC(10,2) NOT NULL,
    price_iced_l NUMERIC(10,2) NULL,
    price_hot NUMERIC(10,2) NULL,
    description TEXT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_bestseller BOOLEAN NOT NULL DEFAULT FALSE,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_menu_items_category ON public.menu_items(category_id);
CREATE INDEX IF NOT EXISTS idx_menu_items_available ON public.menu_items(is_available);
CREATE INDEX IF NOT EXISTS idx_menu_items_bestseller ON public.menu_items(is_bestseller);

-- 2.4 Item Flavors (Relational Mapping for Filter Engine)
CREATE TABLE IF NOT EXISTS public.item_flavors (
    item_id VARCHAR(64) NOT NULL REFERENCES public.menu_items(id) ON DELETE CASCADE ON UPDATE CASCADE,
    flavor_slug VARCHAR(50) NOT NULL,
    flavor_label VARCHAR(100) NOT NULL,
    PRIMARY KEY (item_id, flavor_slug)
);

CREATE INDEX IF NOT EXISTS idx_item_flavors_slug ON public.item_flavors(flavor_slug);

-- 2.5 Option Stock Status (Add-ons, Milk Types, Syrups)
CREATE TABLE IF NOT EXISTS public.options_stock (
    id SERIAL PRIMARY KEY,
    option_slug VARCHAR(64) NOT NULL UNIQUE,
    option_name VARCHAR(100) NOT NULL,
    is_in_stock BOOLEAN NOT NULL DEFAULT TRUE,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2.6 Orders
CREATE TABLE IF NOT EXISTS public.orders (
    id BIGSERIAL PRIMARY KEY,
    order_ref VARCHAR(32) NOT NULL UNIQUE,
    queue_number INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(191) NULL,
    customer_phone VARCHAR(30) NULL,
    order_type VARCHAR(20) NOT NULL DEFAULT 'dine_in',
    table_number INT NULL,
    order_source VARCHAR(20) NOT NULL DEFAULT 'web',
    subtotal NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    discount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    total NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'counter',
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    order_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    prep_status VARCHAR(20) NOT NULL DEFAULT 'queued',
    customer_notes TEXT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_orders_ref ON public.orders(order_ref);
CREATE INDEX IF NOT EXISTS idx_orders_status ON public.orders(order_status);
CREATE INDEX IF NOT EXISTS idx_orders_type ON public.orders(order_type);
CREATE INDEX IF NOT EXISTS idx_orders_created ON public.orders(created_at);

-- 2.7 Order Items
CREATE TABLE IF NOT EXISTS public.order_items (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    menu_item_id VARCHAR(64) NOT NULL REFERENCES public.menu_items(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    quantity INT NOT NULL DEFAULT 1,
    size VARCHAR(20) NOT NULL DEFAULT 'medium',
    temperature VARCHAR(20) NOT NULL DEFAULT 'iced',
    unit_price NUMERIC(10,2) NOT NULL,
    subtotal NUMERIC(10,2) NOT NULL,
    special_instructions VARCHAR(255) NULL
);

CREATE INDEX IF NOT EXISTS idx_order_items_order ON public.order_items(order_id);

-- 2.8 Order Item Add-on Options
CREATE TABLE IF NOT EXISTS public.order_item_options (
    id BIGSERIAL PRIMARY KEY,
    order_item_id BIGINT NOT NULL REFERENCES public.order_items(id) ON DELETE CASCADE ON UPDATE CASCADE,
    option_slug VARCHAR(64) NOT NULL,
    option_name VARCHAR(100) NOT NULL,
    price NUMERIC(10,2) NOT NULL DEFAULT 0.00
);

-- 2.9 System Settings
CREATE TABLE IF NOT EXISTS public.system_settings (
    setting_key VARCHAR(64) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) NULL,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2.10 System Audit Logs
CREATE TABLE IF NOT EXISTS public.system_audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES public.users(id) ON DELETE SET NULL,
    action VARCHAR(64) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ==============================================================================
-- 3. SUPABASE AUTH TRIGGER (SYNC auth.users -> public.users)
-- ==============================================================================

CREATE OR REPLACE FUNCTION public.handle_new_auth_user()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO public.users (auth_user_id, email, name, phone, role, password_hash)
    VALUES (
        NEW.id,
        LOWER(NEW.email),
        COALESCE(NEW.raw_user_meta_data->>'name', split_part(NEW.email, '@', 1)),
        NEW.raw_user_meta_data->>'phone',
        COALESCE(NEW.raw_user_meta_data->>'role', 'customer'),
        'SUPABASE_MANAGED'
    )
    ON CONFLICT (email) DO UPDATE SET
        auth_user_id = EXCLUDED.auth_user_id,
        name = COALESCE(NULLIF(EXCLUDED.name, ''), public.users.name),
        phone = COALESCE(EXCLUDED.phone, public.users.phone),
        updated_at = NOW();

    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Drop trigger if exists and recreate
DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW EXECUTE FUNCTION public.handle_new_auth_user();

-- ==============================================================================
-- 4. SUPABASE STORAGE (BUCKET & RLS POLICIES)
-- ==============================================================================

-- Create bucket 'becoffee-storage' if not exists
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
    'becoffee-storage',
    'becoffee-storage',
    TRUE,
    10485760, -- 10MB limit
    ARRAY['image/jpeg', 'image/png', 'image/webp', 'image/jpg']
)
ON CONFLICT (id) DO UPDATE SET
    public = TRUE,
    allowed_mime_types = ARRAY['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

-- Storage Policy: Allow public read of all objects in bucket
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_policies WHERE policyname = 'Public Access for BeCoffee Storage' AND tablename = 'objects'
    ) THEN
        CREATE POLICY "Public Access for BeCoffee Storage"
        ON storage.objects FOR SELECT
        USING (bucket_id = 'becoffee-storage');
    END IF;
END $$;

-- Storage Policy: Allow service_role and authenticated uploads
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_policies WHERE policyname = 'Allow Uploads to BeCoffee Storage' AND tablename = 'objects'
    ) THEN
        CREATE POLICY "Allow Uploads to BeCoffee Storage"
        ON storage.objects FOR INSERT
        WITH CHECK (bucket_id = 'becoffee-storage');
    END IF;
END $$;

-- ==============================================================================
-- 5. ROW LEVEL SECURITY (RLS) POLICIES
-- ==============================================================================

ALTER TABLE public.categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.menu_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.item_flavors ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.options_stock ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.order_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.system_settings ENABLE ROW LEVEL SECURITY;

-- Public can read menu categories, items, and flavors
CREATE POLICY "Public read categories" ON public.categories FOR SELECT USING (true);
CREATE POLICY "Public read menu_items" ON public.menu_items FOR SELECT USING (true);
CREATE POLICY "Public read item_flavors" ON public.item_flavors FOR SELECT USING (true);
CREATE POLICY "Public read options_stock" ON public.options_stock FOR SELECT USING (true);
CREATE POLICY "Public read settings" ON public.system_settings FOR SELECT USING (true);

-- Anyone can submit orders and view their order status
CREATE POLICY "Allow order creation" ON public.orders FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow order items creation" ON public.order_items FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow public read orders" ON public.orders FOR SELECT USING (true);
CREATE POLICY "Allow public read order items" ON public.order_items FOR SELECT USING (true);

-- ==============================================================================
-- 6. SEED ESSENTIAL CATALOG & OPERATIONAL DATA
-- ==============================================================================

-- 6.1 Categories
INSERT INTO public.categories (id, slug, name, sort_order) VALUES
(1, 'house-coffee', 'House Coffee', 1),
(2, 'house-specials', 'House Specials', 2),
(3, 'matcha-series', 'Matcha Series', 3),
(4, 'yogurt-smoothies', 'Yogurt Smoothies', 4),
(5, 'pastries-bakes', 'Pastries & Bakes', 5)
ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name, sort_order = EXCLUDED.sort_order;

-- 6.2 Default Operational Settings
INSERT INTO public.system_settings (setting_key, setting_value, description) VALUES
('table_qr_ordering_enabled', 'true', 'Master toggle for customer table QR ordering'),
('prep_countdown_duration_minutes', '15', 'Default kitchen countdown timer in minutes'),
('packaging_fee_per_item', '5.00', 'Eco-friendly packaging fee per takeout item')
ON CONFLICT (setting_key) DO NOTHING;

-- 6.3 Standard Options Stock
INSERT INTO public.options_stock (option_slug, option_name, is_in_stock) VALUES
('none', 'No Add-on', TRUE),
('espresso_shot', 'Extra Espresso Shot', TRUE),
('oat_milk', 'Oat Milk Sub', TRUE),
('vanilla_syrup', 'Vanilla Syrup', TRUE),
('caramel_drizzle', 'Caramel Drizzle', TRUE),
('sea_salt_foam', 'Sea Salt Cold Foam', TRUE)
ON CONFLICT (option_slug) DO NOTHING;
