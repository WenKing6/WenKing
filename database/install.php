<?php
/**
 * 数据库安装脚本
 * 访问此文件即可自动创建数据库和表
 * 安装完成后请删除此文件
 */

require_once __DIR__ . '/../config/database.php';

try {
    // 连接 MySQL（不指定数据库）
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 创建数据库
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ 数据库 '" . DB_NAME . "' 创建成功\n";

    // 选择数据库
    $pdo->exec("USE `" . DB_NAME . "`");

    // 创建 users 表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_username` (`username`),
            UNIQUE KEY `uk_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ 数据表 'users' 创建成功\n";

    // 创建 products 表
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ 数据表 'products' 创建成功\n";

    // 插入测试产品数据（仅在表为空时插入）
    $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO `products` (`name`, `tagline`, `description`, `status`, `image`, `button_text`, `button_link`, `features`, `sort_order`, `is_visible`) VALUES
            ('GTA V Menu', 'Complete lobby dominance with advanced features', '100+ Features|Daily Updates|Undetected Status', 'online', '/assets/images/hero-bg.jpg', 'Now Buy', '/partners.php', '100+ Features|Daily Updates|Undetected Status', 1, 1),
            ('RDR 2 Menu', 'Wild West adventure with premium enhancements', '80+ Features|Weekly Updates|Security Protection', 'updating', '/assets/images/hero-bg.jpg', 'Coming Soon', '', '80+ Features|Weekly Updates|Security Protection', 2, 1),
            ('Fortnite Menu', 'Competitive edge with advanced aiming systems', 'Advanced Aimbot|Visual Enhancements|Movement Assistance', 'development', '/assets/images/hero-bg.jpg', 'In Development', '', 'Advanced Aimbot|Visual Enhancements|Movement Assistance', 3, 1)
        ");
        echo "✓ 测试产品数据插入成功\n";
    } else {
        echo "ℹ products 表已有数据，跳过插入\n";
    }

    // 创建 site_settings 表（Settings 页网站配置）
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `site_settings` (
            `setting_key` VARCHAR(100) NOT NULL,
            `setting_value` TEXT NULL,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ 数据表 'site_settings' 创建成功\n";

    // 创建 faq_items 表（Settings 页 FAQ Manager 管理的前台问答项）
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `faq_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `question` VARCHAR(255) NOT NULL,
            `answer` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_faq_sort` (`sort_order`),
            KEY `idx_faq_visible` (`is_visible`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ 数据表 'faq_items' 创建成功\n";

    echo "\n安装完成！请删除此文件 (database/install.php) 以确保安全。\n";
} catch (PDOException $e) {
    echo "✗ 安装失败: " . $e->getMessage() . "\n";
    echo "请检查 config/database.php 中的数据库配置是否正确。\n";
}
