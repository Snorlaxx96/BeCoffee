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
('hc-classic', 1, 'Classic Coffee', 'House Roast Blend', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Smooth, balanced house-brewed coffee with rich nutty undertones and a clean, satisfying finish.', 'images/menu/hc-classic.jpg', FALSE, TRUE),
('hc-spanish', 1, 'Spanish Latte', 'Espresso & Sweetened Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Velvety espresso combined with smooth fresh milk and rich condensed milk for a perfectly sweet kick.', 'images/menu/hc-spanish.jpg', FALSE, TRUE),
('hc-americano', 1, 'Americano', 'Double Espresso & Water', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Bold, clean double shot of house espresso poured over hot water or crisp ice.', 'images/menu/hc-americano.jpg', FALSE, TRUE),
('hc-french-vanilla', 1, 'French Vanilla Latte', 'Espresso & French Vanilla', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Silky espresso and textured milk infused with sweet, aromatic French vanilla bean syrup.', 'images/menu/hc-french-vanilla.jpg', FALSE, TRUE),
('hc-coffee-latte', 1, 'Coffee Latte', 'Espresso & Fresh Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'The timeless coffeehouse essential with smooth textured milk folded into freshly pulled espresso.', 'images/menu/hc-coffee-latte.jpg', FALSE, TRUE),
('hc-caramel-macchiato', 1, 'Caramel Macchiato', 'Espresso, Vanilla & Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Layered vanilla-infused milk crowned with rich espresso and golden buttery caramel drizzle.', 'images/menu/hc-caramel-macchiato.jpg', TRUE, TRUE),
('hc-salted-caramel', 1, 'Salted Caramel', 'Espresso & Flaky Sea Salt Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Slow-cooked rich caramel paired with espresso, fresh milk, and a delicate touch of flaky sea salt.', 'images/menu/hc-salted-caramel.jpg', TRUE, TRUE),
('hc-sea-salt-latte', 1, 'Sea Salt Latte', 'Espresso & Sea Salt Cold Cream', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Rich, comforting latte topped with velvety lightly salted cream for a sublime sweet-savory contrast.', 'images/menu/hc-sea-salt-latte.jpg', FALSE, TRUE),
('hc-ube-latte', 1, 'Ube Latte', 'Espresso & Purple Yam Jam', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Vibrant handcrafted ube halaya blend with fresh milk and a smooth espresso float.', 'images/menu/hc-ube-latte.jpg', FALSE, TRUE),
('hc-mocha', 1, 'Mocha', 'Espresso & Rich Dark Cocoa', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Decadent dark cocoa melted into freshly pulled espresso and steamed milk.', 'images/menu/hc-mocha.jpg', FALSE, TRUE),
('hc-white-choco-mocha', 1, 'White Chocolate Mocha', 'Espresso & White Cacao', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Sweet, velvety white chocolate sauce paired with bold espresso and creamy textured milk.', 'images/menu/hc-white-choco-mocha.jpg', FALSE, TRUE),
('hc-coffee-milo', 1, 'Coffee Milo', 'Espresso & Malted Milo Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'The ultimate power brew—rich espresso combined with hearty malted Milo chocolate milk.', 'images/menu/hc-coffee-milo.jpg', FALSE, TRUE),
('hc-biscoff-latte', 1, 'Biscoff Latte', 'Espresso & Speculoos Cookie Butter', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Spiced Belgian caramelized cookie spread melted into hot or iced espresso and milk.', 'images/menu/hc-biscoff-latte.jpg', FALSE, TRUE),

-- Matcha
('mat-latte', 2, 'Matcha Latte', 'Uji Ceremonial Green Tea', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Stone-ground green tea whisked fresh with silky milk for a soothing, umami-rich experience.', 'images/menu/mat-latte.jpg', TRUE, TRUE),
('mat-dirty', 2, 'Dirty Matcha', 'Uji Matcha & Espresso Shot', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Earthy ceremonial matcha latte topped with a concentrated shot of dark espresso.', 'images/menu/mat-dirty.jpg', FALSE, TRUE),
('mat-berry', 2, 'Matcha Berry', 'Uji Matcha & Wild Strawberry', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Layered handcrafted sweet berry compote with milk and crowned with frothy green matcha.', 'images/menu/mat-berry.jpg', FALSE, TRUE),
('mat-ube', 2, 'Matcha Ube', 'Uji Matcha & Purple Yam Jam', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Vibrant dual-color fusion of velvety purple yam and green ceremonial matcha tea.', 'images/menu/mat-ube.jpg', FALSE, TRUE),
('mat-choco', 2, 'Matchoco', 'Uji Matcha & Cocoa Chocolate', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Deep cocoa chocolate swirled together with vibrant ceremonial green tea.', 'images/menu/mat-choco.jpg', FALSE, TRUE),
('mat-caramel', 2, 'Matcharamel', 'Uji Matcha & Golden Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Ceremonial matcha latte drizzled with luscious buttery golden caramel syrup.', 'images/menu/mat-caramel.jpg', FALSE, TRUE),

-- House Specials
('hs-milo', 3, 'Milo', 'Malted Chocolate Milk', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Rich, creamy malted chocolate beverage prepared hot or poured over cracked ice with malt powder.', 'images/menu/hs-milo.jpg', TRUE, TRUE),
('hs-choco', 3, 'Choco', 'Classic Dark Chocolate Milk', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Decadent, velvety chocolate milk made with pure cocoa and smooth fresh milk.', 'images/menu/hs-choco.jpg', FALSE, TRUE),
('hs-chocoberry', 3, 'Chocoberry', 'Dark Chocolate & Berry Puree', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Indulgent sweet chocolate milk infused with real strawberry and mixed berry nectar.', 'images/menu/hs-chocoberry.jpg', FALSE, TRUE),

-- Yogurt / Soda
('ys-strawberry', 4, 'Strawberry', 'Sweet Strawberry Puree', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Crisp, refreshing sparkling soda or creamy yogurt drink infused with ripe strawberry puree.', 'images/menu/ys-strawberry.jpg', FALSE, TRUE),
('ys-blueberry', 4, 'Blueberry', 'Wild Blueberry Puree', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Tart and sweet wild blueberry syrup paired with effervescent soda or chilled probiotic yogurt.', 'images/menu/ys-blueberry.jpg', FALSE, TRUE),
('ys-mixed-berries', 4, 'Mixed Berries', 'Raspberry, Blackberry & Blueberry', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Vibrant blend of summer berries sparkling with crisp botanical soda or smooth yogurt.', 'images/menu/ys-mixed-berries.jpg', FALSE, TRUE),
('ys-green-apple', 4, 'Green Apple', 'Crisp Green Apple Cordial', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Zesty, bright green apple cordial served over ice with fizzy sparkling soda or creamy yogurt.', 'images/menu/ys-green-apple.jpg', FALSE, TRUE)
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
