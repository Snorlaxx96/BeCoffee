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
('hc-classic', 1, 'Classic Coffee', 'House Roast Blend', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Smooth, balanced house-brewed coffee with rich nutty undertones and a clean, satisfying finish.', 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-spanish', 1, 'Spanish Latte', 'Espresso & Sweetened Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Velvety espresso combined with smooth fresh milk and rich condensed milk for a perfectly sweet kick.', 'https://images.unsplash.com/photo-1570968915860-54d5c301fa9f?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-americano', 1, 'Americano', 'Double Espresso & Water', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Bold, clean double shot of house espresso poured over hot water or crisp ice.', 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-french-vanilla', 1, 'French Vanilla Latte', 'Espresso & French Vanilla', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Silky espresso and textured milk infused with sweet, aromatic French vanilla bean syrup.', 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-coffee-latte', 1, 'Coffee Latte', 'Espresso & Fresh Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'The timeless coffeehouse essential with smooth textured milk folded into freshly pulled espresso.', 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-caramel-macchiato', 1, 'Caramel Macchiato', 'Espresso, Vanilla & Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Layered vanilla-infused milk crowned with rich espresso and golden buttery caramel drizzle.', 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=700&q=80', TRUE, TRUE),
('hc-salted-caramel', 1, 'Salted Caramel', 'Espresso & Flaky Sea Salt Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Slow-cooked rich caramel paired with espresso, fresh milk, and a delicate touch of flaky sea salt.', 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=700&q=80', TRUE, TRUE),
('hc-sea-salt-latte', 1, 'Sea Salt Latte', 'Espresso & Sea Salt Cold Cream', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Rich, comforting latte topped with velvety lightly salted cream for a sublime sweet-savory contrast.', 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-ube-latte', 1, 'Ube Latte', 'Espresso & Purple Yam Jam', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Vibrant handcrafted ube halaya blend with fresh milk and a smooth espresso float.', 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-mocha', 1, 'Mocha', 'Espresso & Rich Dark Cocoa', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Decadent dark cocoa melted into freshly pulled espresso and steamed milk.', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-white-choco-mocha', 1, 'White Chocolate Mocha', 'Espresso & White Cacao', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Sweet, velvety white chocolate sauce paired with bold espresso and creamy textured milk.', 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-coffee-milo', 1, 'Coffee Milo', 'Espresso & Malted Milo Milk', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'The ultimate power brew—rich espresso combined with hearty malted Milo chocolate milk.', 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hc-biscoff-latte', 1, 'Biscoff Latte', 'Espresso & Speculoos Cookie Butter', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Spiced Belgian caramelized cookie spread melted into hot or iced espresso and milk.', 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),

-- Matcha
('mat-latte', 2, 'Matcha Latte', 'Uji Ceremonial Green Tea', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Stone-ground green tea whisked fresh with silky milk for a soothing, umami-rich experience.', 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=700&q=80', TRUE, TRUE),
('mat-dirty', 2, 'Dirty Matcha', 'Uji Matcha & Espresso Shot', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Earthy ceremonial matcha latte topped with a concentrated shot of dark espresso.', 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('mat-berry', 2, 'Matcha Berry', 'Uji Matcha & Wild Strawberry', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Layered handcrafted sweet berry compote with milk and crowned with frothy green matcha.', 'https://images.unsplash.com/photo-1553787499-6f9133860278?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('mat-ube', 2, 'Matcha Ube', 'Uji Matcha & Purple Yam Jam', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Vibrant dual-color fusion of velvety purple yam and green ceremonial matcha tea.', 'https://images.unsplash.com/photo-1505252585461-04db1eb84625?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('mat-choco', 2, 'Matchoco', 'Uji Matcha & Cocoa Chocolate', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Deep cocoa chocolate swirled together with vibrant ceremonial green tea.', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('mat-caramel', 2, 'Matcharamel', 'Uji Matcha & Golden Caramel', 'Hot / Iced', 120.00, 120.00, 140.00, 120.00, 'Ceremonial matcha latte drizzled with luscious buttery golden caramel syrup.', 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),

-- House Specials
('hs-milo', 3, 'Milo', 'Malted Chocolate Milk', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Rich, creamy malted chocolate beverage prepared hot or poured over cracked ice with malt powder.', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=700&q=80', TRUE, TRUE),
('hs-choco', 3, 'Choco', 'Classic Dark Chocolate Milk', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Decadent, velvety chocolate milk made with pure cocoa and smooth fresh milk.', 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('hs-chocoberry', 3, 'Chocoberry', 'Dark Chocolate & Berry Puree', 'Hot / Iced', 90.00, 90.00, 110.00, 90.00, 'Indulgent sweet chocolate milk infused with real strawberry and mixed berry nectar.', 'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),

-- Yogurt / Soda
('ys-strawberry', 4, 'Strawberry', 'Sweet Strawberry Puree', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Crisp, refreshing sparkling soda or creamy yogurt drink infused with ripe strawberry puree.', 'https://images.unsplash.com/photo-1553787499-6f9133860278?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('ys-blueberry', 4, 'Blueberry', 'Wild Blueberry Puree', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Tart and sweet wild blueberry syrup paired with effervescent soda or chilled probiotic yogurt.', 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('ys-mixed-berries', 4, 'Mixed Berries', 'Raspberry, Blackberry & Blueberry', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Vibrant blend of summer berries sparkling with crisp botanical soda or smooth yogurt.', 'https://images.unsplash.com/photo-1497534446932-c925b458314e?auto=format&fit=crop&w=700&q=80', FALSE, TRUE),
('ys-green-apple', 4, 'Green Apple', 'Crisp Green Apple Cordial', 'Iced Only', 80.00, 80.00, 100.00, NULL, 'Zesty, bright green apple cordial served over ice with fizzy sparkling soda or creamy yogurt.', 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=700&q=80', FALSE, TRUE)
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
