-- Atlas Menu / WenKing 数据库初始化脚本
-- 数据库: wenking | 账号: wenking | 密码: wenking

CREATE DATABASE IF NOT EXISTS `wenking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `wenking`;

-- 用户表
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'reseller', 'manager', 'admin') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 操作日志表
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `target_user_id` INT UNSIGNED DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 产品表
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `tagline` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL,
    `status` ENUM('online', 'updating', 'development') NOT NULL DEFAULT 'development',
    `image` VARCHAR(255) NOT NULL DEFAULT '',
    `button_text` VARCHAR(50) NOT NULL DEFAULT 'Now Buy',
    `button_link` VARCHAR(255) NOT NULL DEFAULT '',
    `features` TEXT NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入测试数据
INSERT INTO `products` (`name`, `tagline`, `description`, `status`, `image`, `button_text`, `button_link`, `features`, `sort_order`, `is_visible`) VALUES
('GTA V Menu', 'Complete lobby dominance with advanced features', '100+ Features|Daily Updates|Undetected Status', 'online', '/assets/images/hero-bg.jpg', 'Now Buy', '/partners.php', '100+ Features|Daily Updates|Undetected Status', 1, 1),
('RDR 2 Menu', 'Wild West adventure with premium enhancements', '80+ Features|Weekly Updates|Security Protection', 'updating', '/assets/images/hero-bg.jpg', 'Coming Soon', '', '80+ Features|Weekly Updates|Security Protection', 2, 1),
('Fortnite Menu', 'Competitive edge with advanced aiming systems', 'Advanced Aimbot|Visual Enhancements|Movement Assistance', 'development', '/assets/images/hero-bg.jpg', 'In Development', '', 'Advanced Aimbot|Visual Enhancements|Movement Assistance', 3, 1);
