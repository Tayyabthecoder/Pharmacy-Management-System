-- Migration: Convert Inventory System to Pharmacy Management System (PMS)
USE PMS_db;

-- 1. Create Suppliers Table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_name VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Extend Products Table with Pharmacy Fields
ALTER TABLE products 
    ADD COLUMN generic_name VARCHAR(150) DEFAULT NULL AFTER name,
    ADD COLUMN strength VARCHAR(50) DEFAULT NULL AFTER generic_name,
    ADD COLUMN dosage_form VARCHAR(50) DEFAULT NULL AFTER strength,
    ADD COLUMN batch_number VARCHAR(100) DEFAULT NULL AFTER dosage_form,
    ADD COLUMN expiry_date DATE DEFAULT NULL AFTER batch_number,
    ADD COLUMN is_prescription_required TINYINT(1) DEFAULT 0 AFTER expiry_date,
    ADD COLUMN supplier_id INT DEFAULT NULL AFTER is_prescription_required,
    ADD CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL;

-- 3. Extend Invoices Table with Doctor / Prescription Verification
ALTER TABLE invoices 
    ADD COLUMN doctor_name VARCHAR(150) DEFAULT NULL AFTER total_amount,
    ADD COLUMN doctor_license VARCHAR(50) DEFAULT NULL AFTER doctor_name;

-- 4. Create indexes to support search optimization
ALTER TABLE products ADD INDEX idx_expiry (expiry_date);
ALTER TABLE products ADD INDEX idx_generic (generic_name);

-- 5. Seed System Settings with default Pharmacy Name and Expiry warning enable
UPDATE system_settings SET meta_value = 'My Pharmacy Management System' WHERE meta_key = 'company_name';
INSERT INTO system_settings (meta_key, meta_value) VALUES ('expiry_warning_days', '90') ON DUPLICATE KEY UPDATE meta_value = '90';
