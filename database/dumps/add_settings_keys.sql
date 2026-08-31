-- Migration: Add additional system settings keys
-- Run this after the initial schema.sql

INSERT INTO system_settings (meta_key, meta_value) VALUES 
-- Company / General
('company_email', ''),
('company_phone', ''),
('company_address', ''),

-- Invoice Settings
('invoice_prefix', 'INV-'),
('invoice_notes', 'Thank you for your business!'),
('invoice_show_tax', '1'),
('invoice_due_days', '30'),

-- Notification Settings
('notify_low_stock', '1'),
('notify_new_invoice', '1'),
('notify_credit_exceed', '1'),
('low_stock_email', ''),

-- Security Settings
('session_timeout', '60'),
('max_login_attempts', '5'),
('lockout_duration', '15'),
('password_min_length', '8'),

-- Appearance Settings
('theme_color', '#4f46e5'),
('theme_style', 'glass'),
('date_format', 'M d, Y'),
('records_per_page', '25'),
('sidebar_style', 'expanded')

ON DUPLICATE KEY UPDATE meta_value = meta_value;
