-- Database schema for Pinnacle Hunt Platform

CREATE DATABASE IF NOT EXISTS `pinnacle_hunt` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pinnacle_hunt`;

-- Drop tables if they exist to allow clean reinstall
DROP TABLE IF EXISTS `withdrawals`;
DROP TABLE IF EXISTS `ledger`;
DROP TABLE IF EXISTS `commissions`;
DROP TABLE IF EXISTS `matrix_nodes`;
DROP TABLE IF EXISTS `user_packages`;
DROP TABLE IF EXISTS `packages`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;

-- Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `sponsor_id` INT DEFAULT NULL,
    `wallet_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `reserve_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sponsor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Packages Table
CREATE TABLE `packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `direct_commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    `sort_order` INT NOT NULL DEFAULT 1,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Packages (Active / Completed / Upgraded Tiers)
CREATE TABLE `user_packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `package_id` INT NOT NULL,
    `status` ENUM('active', 'completed', 'upgraded') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_pkg_unique` (`user_id`, `package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Matrix Nodes Table (3-matrix company-wide spillover, 6 levels depth per tree)
CREATE TABLE `matrix_nodes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `parent_id` INT DEFAULT NULL,
    `depth` INT NOT NULL DEFAULT 1,
    `position_index` INT NOT NULL DEFAULT 1, -- 1, 2, or 3 under parent
    `children_count` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `matrix_nodes`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `pkg_user_unique` (`package_id`, `user_id`),
    INDEX `idx_pkg_parent_children` (`package_id`, `parent_id`, `children_count`),
    INDEX `idx_pkg_depth` (`package_id`, `depth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Commissions Table
CREATE TABLE `commissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `source_user_id` INT NOT NULL,
    `package_id` INT NOT NULL,
    `type` ENUM('direct_sponsor', 'matrix_level') NOT NULL,
    `level` INT DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `reserved_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `net_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`source_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Financial Ledger Table
CREATE TABLE `ledger` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('credit', 'debit', 'reserve_hold', 'reserve_release', 'upgrade_payment') NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `balance_after` DECIMAL(15,2) NOT NULL,
    `reserve_after` DECIMAL(15,2) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Withdrawal Requests Table
CREATE TABLE `withdrawals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payout_details` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `processed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings Table
CREATE TABLE `settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Investment Packages
INSERT INTO `packages` (`id`, `name`, `price`, `direct_commission_percent`, `sort_order`, `status`) VALUES
(1, 'Tier 1 - Starter', 500.00, 20.00, 1, 'active'),
(2, 'Tier 2 - Basic', 1000.00, 20.00, 2, 'active'),
(3, 'Tier 3 - Pro', 3000.00, 20.00, 3, 'active'),
(4, 'Tier 4 - Advanced', 5000.00, 20.00, 4, 'active'),
(5, 'Tier 5 - Executive', 10000.00, 20.00, 5, 'active'),
(6, 'Tier 6 - Master', 25000.00, 20.00, 6, 'active'),
(7, 'Tier 7 - Elite', 50000.00, 20.00, 7, 'active'),
(8, 'Tier 8 - Pinnacle', 100000.00, 20.00, 8, 'active');

-- Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('reserve_percentage', '25.00', 'Percentage of earnings directed to reserve pool for auto-upgrades'),
('auto_upgrade_milestone_level', '3', 'Level completion milestone (1-7) that triggers package auto-progression check'),
('direct_sponsor_commission_percent', '20.00', 'Global direct referral commission percentage'),
('matrix_level_1_percent', '12.50', 'Matrix level 1 payout percentage'),
('matrix_level_2_percent', '10.00', 'Matrix level 2 payout percentage'),
('matrix_level_3_percent', '7.50', 'Matrix level 3 payout percentage'),
('matrix_level_4_percent', '5.00', 'Matrix level 4 payout percentage'),
('matrix_level_5_percent', '2.50', 'Matrix level 5 payout percentage'),
('matrix_level_6_percent', '1.25', 'Matrix level 6 payout percentage'),
('matrix_level_7_percent', '1.25', 'Matrix level 7 payout percentage');

-- Create Default Company Admin User (Password: Admin@123456)
-- Password hash generated with password_hash('Admin@123456', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `sponsor_id`, `wallet_balance`, `reserve_balance`, `status`) VALUES
(1, 'admin', 'admin@pinnaclehunt.com', '$2y$10$6U.1K20tE10j4rGPZCB8yu30XgWUuEwxhN979tuVkQzW1/gRq2aNq', 'Pinnacle Admin', '+1000000000', 'admin', NULL, 0.00, 0.00, 'active');
