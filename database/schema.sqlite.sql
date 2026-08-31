CREATE TABLE `categories` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp
);

CREATE TABLE `companies` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp
);

CREATE TABLE `generics` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp
);

CREATE TABLE `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `user_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'salesman',
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` text DEFAULT NULL
);

CREATE TABLE `suppliers` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(150) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp
);

CREATE TABLE `products` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `category_id` INTEGER DEFAULT NULL,
  `generic_id` INTEGER DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `company_id` INTEGER DEFAULT NULL,
  `manufacturer` varchar(150) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `trad_price` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `quantity` INTEGER DEFAULT 0,
  `min_stock_level` INTEGER DEFAULT 10,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`generic_id`) REFERENCES `generics` (`id`) ON DELETE SET NULL
);

CREATE TABLE `inventory_logs` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `product_id` INTEGER NOT NULL,
  `user_id` INTEGER DEFAULT NULL,
  `qty_change` INTEGER NOT NULL,
  `type` varchar(50) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
);

CREATE TABLE `invoices` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `doctor_name` varchar(150) DEFAULT NULL,
  `doctor_license` varchar(50) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `is_return` TINYINT(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
);

CREATE TABLE `invoice_items` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `invoice_id` INTEGER NOT NULL,
  `product_id` INTEGER DEFAULT NULL,
  `quantity` INTEGER NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
);

CREATE TABLE `login_attempts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp,
  `expires_at` datetime NOT NULL
);

CREATE TABLE `notifications` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(20) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE `receive_invoices` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `invoice_number` varchar(50) NOT NULL UNIQUE,
  `supplier_id` INTEGER DEFAULT NULL,
  `user_id` INTEGER DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) NOT NULL DEFAULT 'N/A',
  `status` varchar(20) NOT NULL DEFAULT 'received',
  `received_date` date NOT NULL,
  `is_return` TINYINT(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
);

CREATE TABLE `receive_invoice_items` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `receive_invoice_id` INTEGER NOT NULL,
  `product_id` INTEGER DEFAULT NULL,
  `quantity` INTEGER NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  FOREIGN KEY (`receive_invoice_id`) REFERENCES `receive_invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
);

CREATE TABLE `system_settings` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `meta_key` varchar(50) NOT NULL UNIQUE,
  `meta_value` text DEFAULT NULL
);
