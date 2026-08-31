-- Create receive_invoices table
CREATE TABLE IF NOT EXISTS `receive_invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `supplier_id` INT DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `net_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('received', 'cancelled') NOT NULL DEFAULT 'received',
  `received_date` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create receive_invoice_items table
CREATE TABLE IF NOT EXISTS `receive_invoice_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receive_invoice_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `quantity` INT NOT NULL,
  `cost_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`receive_invoice_id`) REFERENCES `receive_invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
