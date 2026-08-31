-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2026 at 09:00 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Tech', 'This category contains tech-related products like computers, laptop etc', '2026-02-23 09:02:26');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `previous_balance` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `customer_type` enum('regular','vip','wholesale') DEFAULT 'regular',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `address`, `previous_balance`, `status`, `customer_type`, `notes`, `created_at`) VALUES
(1, 'Admin Test Customer', 'wamtayyab2@gmail.com', '0334 9972526', 'FazilPur', '0.00', 'active', 'regular', NULL, '2026-02-28 21:34:27'),
(2, 'Ahmad', 'example@example.com', '03319823536', 'Fazilpur', '10000.00', 'active', 'regular', '', '2026-02-28 23:07:01');

-- --------------------------------------------------------

--
-- Table structure for table `customer_logs`
--

CREATE TABLE `customer_logs` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer_logs`
--

INSERT INTO `customer_logs` (`id`, `customer_id`, `customer_name`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 2, 'Ahmad', 1, 'toggle_status', 'Customer status changed from \'active\' to \'inactive\'.', '2026-05-29 18:12:27'),
(2, 2, 'Ahmad', 1, 'toggle_status', 'Customer status changed from \'inactive\' to \'active\'.', '2026-05-29 18:12:28'),
(3, 2, 'Ahmad', 1, 'edit', 'Customer \'Ahmad\' profile updated. Type: \'regular\', Status: \'active\', Balance: $20000.', '2026-06-17 18:44:25'),
(4, 2, 'Ahmad', 1, 'payment', 'Payment of $10,000.00 received via Cash. Balance reduced from $20,000.00 to $10,000.00', '2026-06-17 18:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `qty_change` int(11) NOT NULL,
  `type` enum('restock','sale','adjustment','return') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`id`, `product_id`, `user_id`, `qty_change`, `type`, `remarks`, `created_at`) VALUES
(1, 1, 2, -3, 'sale', 'Invoice #1', '2026-02-23 09:06:41'),
(2, 1, 1, 3, 'restock', 'restock', '2026-02-27 04:24:36'),
(3, 1, 2, -10, 'sale', 'Invoice #2', '2026-02-27 12:35:51'),
(4, 1, 2, -2, 'sale', 'Invoice #2', '2026-02-27 12:35:51'),
(5, 1, 2, -3, 'sale', 'Invoice #2', '2026-02-27 12:35:51'),
(6, 1, 1, 20, 'restock', '', '2026-02-28 17:46:43'),
(7, 1, 1, 5, 'restock', '', '2026-02-28 17:46:48'),
(8, 1, 2, -2, 'sale', 'Invoice #3', '2026-02-28 21:35:23'),
(9, 1, 2, -3, 'sale', 'Invoice #4', '2026-02-28 23:07:56'),
(10, 1, 1, 3, 'return', 'Invoice Edit Revert #4', '2026-03-01 16:04:35'),
(11, 1, 1, -1, 'sale', 'Invoice Edit #4', '2026-03-01 16:04:35'),
(12, 1, 1, 2, 'return', 'Invoice Edit Revert #3', '2026-03-01 16:05:47'),
(13, 1, 1, -1, 'sale', 'Invoice Edit #3', '2026-03-01 16:05:47'),
(14, 1, 1, 10, 'return', 'Invoice Edit Revert #2', '2026-03-01 16:06:06'),
(15, 1, 1, 2, 'return', 'Invoice Edit Revert #2', '2026-03-01 16:06:06'),
(16, 1, 1, 3, 'return', 'Invoice Edit Revert #2', '2026-03-01 16:06:06'),
(17, 1, 1, -8, 'sale', 'Invoice Edit #2', '2026-03-01 16:06:06'),
(18, 1, 1, -2, 'sale', 'Invoice Edit #2', '2026-03-01 16:06:06'),
(19, 1, 1, -3, 'sale', 'Invoice Edit #2', '2026-03-01 16:06:06'),
(20, 1, 1, 8, 'return', 'Invoice Edit Revert #2', '2026-03-01 16:07:40'),
(21, 1, 1, 2, 'return', 'Invoice Edit Revert #2', '2026-03-01 16:07:40'),
(22, 1, 1, 3, 'return', 'Invoice Edit Revert #2', '2026-03-01 16:07:40'),
(23, 1, 1, -4, 'sale', 'Invoice Edit #2', '2026-03-01 16:07:40'),
(24, 1, 1, -2, 'sale', 'Invoice Edit #2', '2026-03-01 16:07:40'),
(25, 1, 1, -3, 'sale', 'Invoice Edit #2', '2026-03-01 16:07:40'),
(26, 1, 1, 20, 'restock', 'new stock', '2026-06-13 08:43:01'),
(27, 1, 1, 20, 'restock', 'new stock', '2026-06-13 08:46:47');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `user_id`, `customer_id`, `customer_name`, `total_amount`, `tax_amount`, `created_at`) VALUES
(1, 2, NULL, 'Tayyab', '135000.00', '0.00', '2026-02-23 09:06:41'),
(2, 2, NULL, 'Walk-in Customer', '405000.00', '0.00', '2026-02-27 12:35:51'),
(3, 2, 1, 'Tayyab', '45000.00', '0.00', '2026-02-28 21:35:23'),
(4, 2, 2, 'Ahmad', '45000.00', '0.00', '2026-02-28 23:07:56');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 3, '45000.00', '135000.00'),
(7, 4, 1, 1, '45000.00', '45000.00'),
(8, 3, 1, 1, '45000.00', '45000.00'),
(12, 2, 1, 4, '45000.00', '180000.00'),
(13, 2, 1, 2, '45000.00', '90000.00'),
(14, 2, 1, 3, '45000.00', '135000.00');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, 1, 'System Update', 'The notification system is now live!', 'success', 0, NULL, '2026-02-27 15:26:13'),
(2, 1, 'Low Stock Alert', 'Product X is running low on stock.', 'warning', 0, NULL, '2026-02-27 15:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `quantity`, `image`, `created_at`) VALUES
(1, 1, 'Laptop', '45000.00', 74, 'uploads/1771837479_download.jpg', '2026-02-23 09:04:39');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `meta_key` varchar(50) NOT NULL,
  `meta_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `meta_key`, `meta_value`) VALUES
(1, 'company_name', 'My Inventory System'),
(2, 'currency', 'PKR'),
(3, 'tax_rate', '10'),
(10, 'min_stock', '30'),
(11, 'notif_new_order', '1'),
(12, 'notif_low_stock', '1'),
(15, 'company_email', ''),
(16, 'company_phone', ''),
(17, 'company_address', ''),
(18, 'invoice_prefix', 'INV-'),
(19, 'invoice_notes', 'Thank you for your business!'),
(20, 'invoice_show_tax', '1'),
(21, 'invoice_due_days', '30'),
(22, 'notify_low_stock', '1'),
(23, 'notify_new_invoice', '1'),
(24, 'notify_credit_exceed', '1'),
(25, 'low_stock_email', ''),
(26, 'session_timeout', '60'),
(27, 'max_login_attempts', '5'),
(28, 'lockout_duration', '15'),
(29, 'password_min_length', '8'),
(30, 'theme_color', '#2563eb'),
(31, 'date_format', 'M d, Y'),
(32, 'records_per_page', '25'),
(33, 'sidebar_style', 'expanded'),
(35, 'theme_style', 'glass');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','salesman') NOT NULL DEFAULT 'salesman',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `user_image`, `password`, `role`, `status`, `created_at`, `phone`, `bio`, `address`) VALUES
(1, 'Admin', 'admin@admin.com', 'user_1_1773016302.jpg', '$2y$10$4OaU5ZWH5SLiKK1ajjGmj.mKxRXQ83UtNLAb5JKJActHETwvN7d3O', 'admin', 'active', '2026-02-16 17:31:44', '', 'Adding a test bio', ''),
(2, 'Tayyab', 'Tayyab@Tayyab.com', NULL, '$2y$10$j4dcOcsMx9aBpaWtsARalOincHIBGAu.fwXqrRE3Mc065LY0Fzb0S', 'salesman', 'active', '2026-02-16 17:43:43', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_logs`
--
ALTER TABLE `customer_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_invoices_customer_id` (`customer_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_expires` (`ip_address`,`expires_at`),
  ADD KEY `idx_email_expires` (`email`,`expires_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meta_key` (`meta_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_logs`
--
ALTER TABLE `customer_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=358;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_logs`
--
ALTER TABLE `customer_logs`
  ADD CONSTRAINT `customer_logs_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoice_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
