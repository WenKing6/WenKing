<?php
/**
 * 产品模型类
 * 处理产品的增删改查
 */

require_once __DIR__ . '/../Database.php';

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * 获取所有可见产品（前台展示用）
     */
    public function getVisible(): array {
        $stmt = $this->db->query('SELECT * FROM products WHERE is_visible = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    /**
     * 获取所有产品（后台管理用）
     */
    public function getAll(): array {
        $stmt = $this->db->query('SELECT * FROM products ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    /**
     * 根据 ID 获取产品
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    /**
     * 创建产品
     */
    public function create(array $data): array {
        $stmt = $this->db->prepare(
            'INSERT INTO products (name, tagline, description, status, image, button_text, button_link, features, sort_order, is_visible)
             VALUES (:name, :tagline, :description, :status, :image, :button_text, :button_link, :features, :sort_order, :is_visible)'
        );
        $stmt->execute([
            ':name'         => $data['name'] ?? '',
            ':tagline'      => $data['tagline'] ?? '',
            ':description'  => $data['description'] ?? '',
            ':status'       => $data['status'] ?? 'development',
            ':image'        => $data['image'] ?? '',
            ':button_text'  => $data['button_text'] ?? 'Now Buy',
            ':button_link'  => $data['button_link'] ?? '',
            ':features'     => $data['features'] ?? '',
            ':sort_order'   => (int)($data['sort_order'] ?? 0),
            ':is_visible'   => (int)($data['is_visible'] ?? 1),
        ]);
        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    /**
     * 更新产品
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE products SET name = :name, tagline = :tagline, description = :description,
             status = :status, image = :image, button_text = :button_text, button_link = :button_link,
             features = :features, sort_order = :sort_order, is_visible = :is_visible WHERE id = :id'
        );
        return $stmt->execute([
            ':id'           => $id,
            ':name'         => $data['name'] ?? '',
            ':tagline'      => $data['tagline'] ?? '',
            ':description'  => $data['description'] ?? '',
            ':status'       => $data['status'] ?? 'development',
            ':image'        => $data['image'] ?? '',
            ':button_text'  => $data['button_text'] ?? 'Now Buy',
            ':button_link'  => $data['button_link'] ?? '',
            ':features'     => $data['features'] ?? '',
            ':sort_order'   => (int)($data['sort_order'] ?? 0),
            ':is_visible'   => (int)($data['is_visible'] ?? 1),
        ]);
    }

    /**
     * 删除产品
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * 解析 features 字段（用 | 分隔）
     */
    public static function parseFeatures(string $features): array {
        return array_filter(array_map('trim', explode('|', $features)));
    }
}
