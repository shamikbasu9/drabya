-- ============================================================
-- Drabya Category Content & Descriptions Update
-- ============================================================

USE drabya;

-- Update Coffee Mugs
UPDATE categories 
SET description = 'Your morning coffee just got a glow-up. Whether you\'re a minimalist or a maximalist, our premium mugs are designed to match your mood. Grab your favorite and start the day with a smile.',
    icon = 'fa-mug-hot'
WHERE slug = 'coffee-mugs' OR name = 'Coffee Mugs';

-- Update Fridge Magnets
UPDATE categories 
SET description = 'Small items, big personality. Turn your fridge into a mood board with our vibrant, high-quality magnets. They’re the perfect little treat for yourself or a friend.',
    icon = 'fa-magnet'
WHERE slug = 'fridge-magnets' OR name = 'Fridge Magnets';

-- Update Coasters
UPDATE categories 
SET description = 'Protect your desk, but make it fashionable. Our coasters add a pop of color to your space while keeping things clean. Functional art for your home or office.',
    icon = 'fa-circle-dot'
WHERE slug = 'coasters' OR name = 'Coasters';

-- Update T-shirt
UPDATE categories 
SET name = 'T-shirt',
    description = 'Coming soon',
    icon = 'fa-tshirt'
WHERE slug IN ('tshirts', 't-shirts') OR name IN ('T-Shirts', 'T-shirts', 'T-shirt');

-- Insert any missing categories
INSERT INTO categories (name, slug, description, icon) VALUES
('Coffee Mugs', 'coffee-mugs', 'Your morning coffee just got a glow-up. Whether you\'re a minimalist or a maximalist, our premium mugs are designed to match your mood. Grab your favorite and start the day with a smile.', 'fa-mug-hot'),
('Coasters', 'coasters', 'Protect your desk, but make it fashionable. Our coasters add a pop of color to your space while keeping things clean. Functional art for your home or office.', 'fa-circle-dot'),
('Fridge Magnets', 'fridge-magnets', 'Small items, big personality. Turn your fridge into a mood board with our vibrant, high-quality magnets. They’re the perfect little treat for yourself or a friend.', 'fa-magnet'),
('T-shirt', 'tshirts', 'Coming soon', 'fa-tshirt')
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    description = VALUES(description),
    icon = VALUES(icon);
