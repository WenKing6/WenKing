<?php
/**
 * FAQ 条目模型类
 * 处理前台常见问题（问答项）的增删改查
 * 表结构不存在时自动创建（幂等自愈迁移）
 */

require_once __DIR__ . '/../Database.php';

class FaqItem {
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
     * 确保 faq_items 表存在（幂等）
     */
    private function ensureTable(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS `faq_items` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * 获取所有可见 FAQ（前台展示用）
     */
    public function getVisible(): array {
        $stmt = $this->db->query('SELECT * FROM faq_items WHERE is_visible = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    /**
     * 获取所有 FAQ（后台管理用）
     */
    public function getAll(): array {
        $stmt = $this->db->query('SELECT * FROM faq_items ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    /**
     * 创建 FAQ 条目
     */
    public function create(array $data): array {
        $stmt = $this->db->prepare(
            'INSERT INTO faq_items (question, answer, sort_order, is_visible) VALUES (:question, :answer, :sort_order, :is_visible)'
        );
        $stmt->execute([
            ':question'   => $data['question'] ?? '',
            ':answer'     => $data['answer'] ?? '',
            ':sort_order' => (int)($data['sort_order'] ?? 0),
            ':is_visible' => (int)($data['is_visible'] ?? 1),
        ]);
        return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    /**
     * 更新 FAQ 条目
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE faq_items SET question = :question, answer = :answer, sort_order = :sort_order, is_visible = :is_visible WHERE id = :id'
        );
        return $stmt->execute([
            ':id'         => $id,
            ':question'   => $data['question'] ?? '',
            ':answer'     => $data['answer'] ?? '',
            ':sort_order' => (int)($data['sort_order'] ?? 0),
            ':is_visible' => (int)($data['is_visible'] ?? 1),
        ]);
    }

    /**
     * 删除 FAQ 条目
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM faq_items WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * 切换 FAQ 可见性（is_visible 0/1 翻转）
     * 返回切换后的可见状态
     */
    public function toggleVisibility(int $id): ?int {
        $stmt = $this->db->prepare('UPDATE faq_items SET is_visible = 1 - is_visible WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT is_visible FROM faq_items WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? (int)$row['is_visible'] : null;
    }
}
