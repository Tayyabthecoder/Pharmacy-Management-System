-- Migration: Enhance customers table with credit limits, status, customer_type, and notes

ALTER TABLE customers 
ADD COLUMN IF NOT EXISTS credit_limit DECIMAL(10, 2) DEFAULT 1000.00 AFTER previous_balance,
ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active' AFTER credit_limit,
ADD COLUMN IF NOT EXISTS customer_type ENUM('regular', 'vip', 'wholesale') DEFAULT 'regular' AFTER status,
ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL AFTER customer_type;

ALTER TABLE invoices 
ADD COLUMN IF NOT EXISTS customer_id INT DEFAULT NULL AFTER user_id;

-- Add index if not exists (handled safely by checking or separate statement)
CREATE INDEX idx_invoices_customer_id ON invoices (customer_id);

INSERT INTO system_settings (meta_key, meta_value) VALUES 
('default_credit_limit', '1000.00'),
('allow_exceeding_credit', '0')
ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value);
