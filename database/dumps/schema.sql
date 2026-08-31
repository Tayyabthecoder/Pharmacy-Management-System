-- Database Schema for Inventory Management System

CREATE DATABASE IF NOT EXISTS PMS_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE PMS_db;

-- Users Table (Admin & Salesman)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    user_image VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'salesman') NOT NULL DEFAULT 'salesman',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default Admin User (Password: admin123)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');


-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2) DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);



-- Inventory Logs (For history)
CREATE TABLE IF NOT EXISTS inventory_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT,
    qty_change INT NOT NULL,
    type ENUM('restock', 'sale', 'adjustment', 'return') NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Invoices Table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(100),
    total_amount DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Invoice Items Table
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- System Settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meta_key VARCHAR(50) NOT NULL UNIQUE,
    meta_value TEXT
);

-- Default Settings
INSERT INTO system_settings (meta_key, meta_value) VALUES 
('company_name', 'My Inventory System'),
('currency', '$'),
('tax_rate', '5'),
('min_stock', '10'),
('company_email', 'admin@admin.com'),
('company_phone', ''),
('company_address', ''),
('company_website', ''),
('company_registration', ''),
('timezone', 'UTC'),
('language', 'en'),
('invoice_prefix', 'INV-'),
('invoice_due_days', '30'),
('invoice_number_format', 'sequential'),
('invoice_number_padding', '5'),
('invoice_paper_size', 'a4'),
('print_template', 'simple'),
('default_payment_method', 'cash'),
('return_policy_days', '15'),
('invoice_show_tax', '1'),
('enable_partial_payments', '0'),
('enable_returns', '1'),
('invoice_notes', 'Thank you for your business!'),

('max_product_image_size', '5242880'),
('default_restock_qty', '50'),
('enable_product_images', '1'),
('inventory_valuation_method', 'fifo'),
('profit_margin_warning', '10'),
('allow_negative_stock', '0'),
('track_cost_price', '1'),
('enable_barcode', '1'),
('salesman_can_edit_invoice', '1'),
('salesman_can_delete_invoice', '0'),

('salesman_can_see_cost_price', '0'),
('max_salesman_discount', '5.0'),
('approval_threshold_amount', '5000.00'),
('salesman_can_give_discount', '1'),
('require_admin_approval', '1'),
('notify_low_stock', '1'),
('notify_new_invoice', '1'),
('notify_payment_received', '1'),
('notify_product_expiry', '0'),
('notification_sound', '1'),
('low_stock_email', ''),
('daily_report_time', '18:00'),
('daily_report_email', '0'),
('smtp_enabled', '0'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_encryption', 'tls'),
('email_from_name', 'IMS Billing System'),
('email_from_address', 'noreply@company.com'),
('session_timeout', '60'),
('password_min_length', '8'),
('password_expiry_days', '90'),
('max_login_attempts', '5'),
('lockout_duration', '15'),
('ip_whitelist', ''),
('require_uppercase', '0'),
('require_special_char', '0'),
('two_factor_auth', '0'),
('enable_audit_log', '1'),
('auto_logout_on_close', '0'),
('auto_backup_enabled', '0'),
('backup_frequency', 'daily'),
('backup_retention_days', '30'),
('backup_email', ''),
('maintenance_mode', '0'),
('maintenance_message', 'The system is undergoing scheduled upgrade. Please try again later.')
ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value);


