-- Migration: Remove Customer Module
USE PMS_db;

-- 1. Drop foreign key constraint on invoices
ALTER TABLE invoices DROP FOREIGN KEY fk_invoice_customer;

-- 2. Drop customer_id column from invoices
ALTER TABLE invoices DROP COLUMN customer_id;

-- 3. Drop customer_logs table
DROP TABLE IF EXISTS customer_logs;

-- 4. Drop customers table
DROP TABLE IF EXISTS customers;

-- 5. Delete Customer related settings
DELETE FROM system_settings WHERE meta_key IN (
  'vip_discount_rate',
  'wholesale_discount_rate',
  'require_customer_for_invoice',
  'customer_statement_period',
  'auto_deactivate_days',
  'salesman_can_add_customer'
);
