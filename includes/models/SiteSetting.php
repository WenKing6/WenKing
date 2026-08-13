<?php
/**
 * 站点设置模型类
 * 处理网站配置项的读写（key-value 结构）
 * 表结构不存在时自动创建（幂等自愈迁移）
 */

require_once __DIR__ . '/../Database.php';

class SiteSetting {
    private PDO $db;
    private static bool $tableChecked = false;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
        if (!self::$tableChecked) {
            $this->ensureTable();
            self::$tableChecked = true;
        }
    }

    /**
     * 确保 site_settings 表存在（幂等）
     */
    private function ensureTable(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS `site_settings` (
            `setting_key` VARCHAR(100) NOT NULL,
            `setting_value` TEXT NULL,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * 获取单个设置项
     */
    public function get(string $key): ?string {
        $stmt = $this->db->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :key');
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();
        return $row !== false ? (string)$row['setting_value'] : null;
    }

    /**
     * 写入单个设置项（存在则更新，不存在则插入）
     */
    public function set(string $key, ?string $value): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE setting_value = :new_value'
        );
        return $stmt->execute([':key' => $key, ':value' => $value, ':new_value' => $value]);
    }

    /**
     * 获取全部设置项（key => value 映射）
     */
    public function getAll(): array {
        $stmt = $this->db->query('SELECT setting_key, setting_value FROM site_settings');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    /**
     * 获取站点名称（读取数据库配置，失败时回退到 SITE_NAME 常量）
     */
    public static function getName(): string {
        $fallback = defined('SITE_NAME') ? SITE_NAME : 'WenKing';
        try {
            $name = (new self())->get('site_name');
            return ($name !== null && trim($name) !== '') ? trim($name) : $fallback;
        } catch (Throwable $e) {
            return $fallback;
        }
    }
}
