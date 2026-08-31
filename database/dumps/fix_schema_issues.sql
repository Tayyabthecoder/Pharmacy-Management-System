-- Migration: Database Schema Fixes
-- 1. Migrate latin1 tables to utf8mb4 for full Unicode support (emojis, non-ASCII characters)
ALTER TABLE categories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE products CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE invoices CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE invoice_items CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE system_settings CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Fix the inventory_logs ENUM definition
-- Currently, SaleController.php line 86 inserts 'return' which violates the ENUM('restock','sale','adjustment') constraint.
-- We alter the ENUM definition to officially support 'return'.
ALTER TABLE inventory_logs MODIFY COLUMN type ENUM('restock', 'sale', 'adjustment', 'return') NOT NULL;

-- 3. Fix seed data: Update the coerced empty string '' records in inventory_logs to 'return'
UPDATE inventory_logs SET type = 'return' WHERE type = '' OR remarks LIKE '%Revert%';

-- Convert inventory_logs table to utf8mb4 character set
ALTER TABLE inventory_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 4. Remove duplicate profile_image column from users table
-- The application code (ProfileController/SalesmanController, User model, and topbar view) uses user_image exclusively.
ALTER TABLE users DROP COLUMN profile_image;

-- Convert users table to utf8mb4 character set
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 5. Standardize orphaned notifications table to utf8mb4
ALTER TABLE notifications CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
