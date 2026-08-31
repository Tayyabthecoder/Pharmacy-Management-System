-- ================================================================
-- Login Rate Limiter Table
-- ================================================================
-- Tracks failed login attempts per IP address for brute-force
-- protection. Records expire automatically via the expires_at column.
-- ================================================================

CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `ip_address`  VARCHAR(45)     NOT NULL,           -- Supports IPv4 & IPv6
    `email`       VARCHAR(255)    NOT NULL DEFAULT '',
    `attempted_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`  DATETIME        NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_ip_expires`  (`ip_address`, `expires_at`),
    INDEX `idx_email_expires` (`email`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
