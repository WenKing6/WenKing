<?php
/**
 * 角色权限迁移脚本
 * 为现有 users 表添加 role 字段，创建 activity_log 表
 */
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 检查 role 字段是否已存在
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'reseller', 'manager', 'admin') NOT NULL DEFAULT 'user' AFTER password_hash");
        echo "✓ Added 'role' column to users table\n";
    } else {
        echo "✓ 'role' column already exists\n";
    }

    // 创建 activity_log 表
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        action VARCHAR(50) NOT NULL,
        target_user_id INT UNSIGNED DEFAULT NULL,
        details TEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_user_id (user_id),
        KEY idx_action (action),
        KEY idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created 'activity_log' table\n";

    echo "\nMigration completed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
