-- ============================================================
-- Drabya Rebrand Migration Script
-- Run this ONLY on existing xcell_store_india installations
-- to migrate data to the new drabya database.
-- ============================================================

-- Step 1: Create new database
CREATE DATABASE IF NOT EXISTS drabya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 2: (Manual) Copy all table data from xcell_store_india to drabya
-- mysqldump xcell_store_india | mysql drabya

-- Step 3: Rename care_instructions column to product_details
USE drabya;
ALTER TABLE products CHANGE COLUMN care_instructions product_details TEXT;

-- Step 4: Update admin email
UPDATE admins SET email = 'admin@drabya.org.in' WHERE email = 'admin@xcellstore.com';

-- Step 5: Update site_name setting
UPDATE settings SET setting_value = 'Drabya' WHERE setting_key = 'site_name';

-- Step 6: Replace old plant categories with Drabya merchandise categories
-- WARNING: Only do this if you want to replace all existing categories.
-- Make sure to reassign any products to new categories first if needed.
DELETE FROM categories WHERE slug IN ('indoor-plants','outdoor-plants','succulents','flowering-plants','air-purifying','medicinal-plants');

INSERT IGNORE INTO categories (name, slug, description, icon) VALUES
('Coffee Mugs', 'coffee-mugs', 'Custom printed coffee mugs for every occasion', 'fa-mug-hot'),
('Coasters', 'coasters', 'Stylish coasters to protect your surfaces', 'fa-circle'),
('Fridge Magnets', 'fridge-magnets', 'Fun and decorative fridge magnets', 'fa-magnet'),
('T-Shirts', 'tshirts', 'Graphic and custom printed t-shirts', 'fa-tshirt');

-- Step 7: Update support email in contact tickets if stored
UPDATE support_tickets SET email = 'support@drabya.org.in' WHERE email = 'support@xcellstore.com';

SELECT 'Migration complete! Drabya database is ready.' AS status;
