-- ==============================================================================
-- Migration 002: Seed BeCoffee Menu Categories, 26 Items, and Flavor Mappings
-- ==============================================================================

-- 1. Insert Categories
INSERT INTO categories (id, slug, name, sort_order) VALUES
(1, 'house-coffee', 'House Coffee', 1),
(2, 'matcha', 'Matcha', 2),
(3, 'house-specials', 'House Specials', 3),
(4, 'yogurt-soda', 'Yogurt / Soda', 4)
ON DUPLICATE KEY UPDATE name=VALUES(name), sort_order=VALUES(sort_order);

-- 2. Insert Menu Items
INSERT INTO menu_items (id, category_id, name, origin_notes, elevation_info, price, price_iced_m, price_iced_l, price_hot, description, image_url, is_bestseller, is_available) VALUES
-- House Coffee
('hc-classic', 1, 'Classic Coffee', 'House Roast Blend', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Single-origin house arabica blend, batch brewed fresh daily.', 'images/menu/hc-classic.webp', FALSE, TRUE),
('hc-spanish', 1, 'Spanish Latte', 'Espresso & Sweetened Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, condensed milk, fresh milk. Sweet and creamy.', 'images/menu/hc-spanish.webp', FALSE, TRUE),
('hc-americano', 1, 'Americano', 'Double Espresso & Water', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Double shot espresso, filtered water. Served hot or iced.', 'images/menu/hc-americano.webp', FALSE, TRUE),
('hc-french-vanilla', 1, 'French Vanilla Latte', 'Espresso & French Vanilla', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, vanilla bean syrup, steamed milk.', 'images/menu/hc-french-vanilla.webp', FALSE, TRUE),
('hc-coffee-latte', 1, 'Coffee Latte', 'Espresso & Fresh Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso with textured steamed milk and microfoam.', 'images/menu/hc-coffee-latte.webp', FALSE, TRUE),
('hc-caramel-macchiato', 1, 'Caramel Macchiato', 'Espresso, Vanilla & Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, vanilla syrup, fresh milk, caramel drizzle.', 'images/menu/hc-caramel-macchiato.webp', TRUE, TRUE),
('hc-salted-caramel', 1, 'Salted Caramel', 'Espresso & Flaky Sea Salt Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, house caramel syrup, sea salt, fresh milk.', 'images/menu/hc-salted-caramel.webp', TRUE, TRUE),
('hc-sea-salt-latte', 1, 'Sea Salt Latte', 'Espresso & Sea Salt Cold Cream', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, textured milk, lightly salted cold foam.', 'images/menu/hc-sea-salt-latte.webp', FALSE, TRUE),
('hc-ube-latte', 1, 'Ube Latte', 'Espresso & Purple Yam Jam', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, purple yam halaya, fresh milk.', 'images/menu/hc-ube-latte.webp', FALSE, TRUE),
('hc-mocha', 1, 'Mocha', 'Espresso & Rich Dark Cocoa', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, dark cocoa, steamed fresh milk.', 'images/menu/hc-mocha.webp', FALSE, TRUE),
('hc-white-choco-mocha', 1, 'White Chocolate Mocha', 'Espresso & White Cacao', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, white chocolate sauce, fresh milk.', 'images/menu/hc-white-choco-mocha.webp', FALSE, TRUE),
('hc-coffee-milo', 1, 'Coffee Milo', 'Espresso & Malted Milo Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, malted Milo chocolate, steamed or cold milk.', 'images/menu/hc-coffee-milo.webp', FALSE, TRUE),
('hc-biscoff-latte', 1, 'Biscoff Latte', 'Espresso & Speculoos Cookie Butter', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Espresso, spiced Belgian cookie spread, fresh milk.', 'images/menu/hc-biscoff-latte.webp', FALSE, TRUE),

-- Matcha
('mat-latte', 2, 'Matcha Latte', 'Uji Ceremonial Green Tea', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Japanese Uji green tea whisked with fresh milk.', 'images/menu/mat-latte.webp', TRUE, TRUE),
('mat-dirty', 2, 'Dirty Matcha', 'Uji Matcha & Espresso Shot', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Uji matcha latte with a fresh shot of espresso.', 'images/menu/mat-dirty.webp', FALSE, TRUE),
('mat-berry', 2, 'Matcha Berry', 'Uji Matcha & Wild Strawberry', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Uji matcha, strawberry compote, fresh milk.', 'images/menu/mat-berry.webp', FALSE, TRUE),
('mat-ube', 2, 'Matcha Ube', 'Uji Matcha & Purple Yam Jam', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Uji matcha, purple yam halaya, fresh milk.', 'images/menu/mat-ube.webp', FALSE, TRUE),
('mat-choco', 2, 'Matchoco', 'Uji Matcha & Cocoa Chocolate', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Uji matcha whisked with pure dark cocoa and milk.', 'images/menu/mat-choco.webp', FALSE, TRUE),
('mat-caramel', 2, 'Matcharamel', 'Uji Matcha & Golden Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Uji matcha latte with caramel drizzle.', 'images/menu/mat-caramel.webp', FALSE, TRUE),

-- House Specials
('hs-milo', 3, 'Milo', 'Malted Chocolate Milk', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Malted Milo chocolate drink, served hot or iced.', 'images/menu/hs-milo.webp', TRUE, TRUE),
('hs-choco', 3, 'Choco', 'Classic Dark Chocolate Milk', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Dark cocoa whisked with fresh whole milk.', 'images/menu/hs-choco.webp', FALSE, TRUE),
('hs-chocoberry', 3, 'Chocoberry', 'Dark Chocolate & Berry Puree', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Dark chocolate milk with mixed berry puree.', 'images/menu/hs-chocoberry.webp', FALSE, TRUE),

-- Yogurt / Soda
('ys-strawberry', 4, 'Strawberry', 'Sweet Strawberry Puree', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Strawberry puree with sparkling soda or yogurt.', 'images/menu/ys-strawberry.webp', FALSE, TRUE),
('ys-blueberry', 4, 'Blueberry', 'Wild Blueberry Puree', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Wild blueberry puree with sparkling soda or yogurt.', 'images/menu/ys-blueberry.webp', FALSE, TRUE),
('ys-mixed-berries', 4, 'Mixed Berries', 'Raspberry, Blackberry & Blueberry', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Raspberry, blackberry, blueberry with soda or yogurt.', 'images/menu/ys-mixed-berries.webp', FALSE, TRUE),
('ys-green-apple', 4, 'Green Apple', 'Crisp Green Apple Cordial', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Green apple cordial with sparkling soda or yogurt.', 'images/menu/ys-green-apple.webp', FALSE, TRUE)
ON DUPLICATE KEY UPDATE
    name=VALUES(name),
    category_id=VALUES(category_id),
    price=VALUES(price),
    price_iced_m=VALUES(price_iced_m),
    price_iced_l=VALUES(price_iced_l),
    price_hot=VALUES(price_hot),
    description=VALUES(description),
    image_url=VALUES(image_url),
    is_bestseller=VALUES(is_bestseller),
    is_available=VALUES(is_available);

-- 3. Insert Flavor Mappings
INSERT INTO item_flavors (item_id, flavor_slug, flavor_label) VALUES
('hc-classic', 'bold-coffee', 'Bold & Classic Coffee'),
('hc-spanish', 'sweet-caramel', 'Sweet & Caramel'),
('hc-spanish', 'bold-coffee', 'Bold Coffee'),
('hc-americano', 'bold-coffee', 'Bold & Classic Coffee'),
('hc-french-vanilla', 'sweet-caramel', 'Sweet & Caramel'),
('hc-coffee-latte', 'bold-coffee', 'Bold & Classic Coffee'),
('hc-caramel-macchiato', 'sweet-caramel', 'Sweet & Caramel'),
('hc-salted-caramel', 'sweet-caramel', 'Sweet & Caramel'),
('hc-sea-salt-latte', 'sweet-caramel', 'Sweet & Caramel'),
('hc-ube-latte', 'sweet-caramel', 'Sweet & Caramel'),
('hc-mocha', 'chocolate-malt', 'Chocolate & Malt'),
('hc-mocha', 'bold-coffee', 'Bold Coffee'),
('hc-white-choco-mocha', 'sweet-caramel', 'Sweet & Caramel'),
('hc-white-choco-mocha', 'chocolate-malt', 'White Chocolate'),
('hc-coffee-milo', 'chocolate-malt', 'Chocolate & Malt'),
('hc-coffee-milo', 'bold-coffee', 'Bold Coffee'),
('hc-biscoff-latte', 'sweet-caramel', 'Sweet & Caramel'),
('mat-latte', 'matcha', 'Ceremonial Matcha'),
('mat-dirty', 'matcha', 'Ceremonial Matcha'),
('mat-dirty', 'bold-coffee', 'Bold Coffee'),
('mat-berry', 'matcha', 'Ceremonial Matcha'),
('mat-berry', 'fruity-berry', 'Fruity & Berry'),
('mat-ube', 'matcha', 'Ceremonial Matcha'),
('mat-ube', 'sweet-caramel', 'Sweet & Caramel'),
('mat-choco', 'matcha', 'Ceremonial Matcha'),
('mat-choco', 'chocolate-malt', 'Chocolate & Malt'),
('mat-caramel', 'matcha', 'Ceremonial Matcha'),
('mat-caramel', 'sweet-caramel', 'Sweet & Caramel'),
('hs-milo', 'chocolate-malt', 'Chocolate & Malt'),
('hs-choco', 'chocolate-malt', 'Chocolate & Malt'),
('hs-chocoberry', 'chocolate-malt', 'Chocolate & Malt'),
('hs-chocoberry', 'fruity-berry', 'Fruity & Berry'),
('ys-strawberry', 'fruity-berry', 'Fruity & Refreshing'),
('ys-blueberry', 'fruity-berry', 'Fruity & Refreshing'),
('ys-mixed-berries', 'fruity-berry', 'Fruity & Refreshing'),
('ys-green-apple', 'fruity-berry', 'Fruity & Refreshing')
ON DUPLICATE KEY UPDATE flavor_label=VALUES(flavor_label);
