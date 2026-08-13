<?php
/**
 * License Model Class
 * Handles license CRUD operations
 */

require_once __DIR__ . '/../Database.php';

class License {
    private PDO $db;
    private static bool $allocationTableChecked = false;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
        if (!self::$allocationTableChecked) {
            $this->ensureAllocationTable();
            self::$allocationTableChecked = true;
        }
    }

    /**
     * 确保 license_allocations 配额表存在（幂等自愈迁移）
     * 配额 = Admin 分配给指定用户（经理等）在「某产品 + 某时长」下可生成的钥匙数量
     */
    private function ensureAllocationTable(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS `license_allocations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `duration_days` INT NOT NULL DEFAULT 30,
            `quantity` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_user_product_duration` (`user_id`, `product_id`, `duration_days`),
            KEY `idx_alloc_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * Get all licenses
     */
    public function getAll(): array {
        $stmt = $this->db->query('
            SELECT l.*, p.name as product_name, u.username as user_name, u.email as user_email, u.role as user_role
            FROM licenses l
            LEFT JOIN products p ON l.product_id = p.id
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
        ');
        return $stmt->fetchAll();
    }

    /**
     * Get license by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('
            SELECT l.*, p.name as product_name, u.username as user_name, u.email as user_email
            FROM licenses l
            LEFT JOIN products p ON l.product_id = p.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.id = :id
        ');
        $stmt->execute([':id' => $id]);
        $license = $stmt->fetch();
        return $license ?: null;
    }

    /**
     * Get license by key
     */
    public function getByKey(string $key): ?array {
        $stmt = $this->db->prepare('
            SELECT l.*, p.name as product_name, u.username as user_name, u.email as user_email
            FROM licenses l
            LEFT JOIN products p ON l.product_id = p.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.license_key = :key
        ');
        $stmt->execute([':key' => $key]);
        $license = $stmt->fetch();
        return $license ?: null;
    }

    /**
     * Create a single license
     */
    public function create(array $data): array {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO licenses (license_key, product_id, user_id, duration_days, status, note)
                VALUES (:license_key, :product_id, :user_id, :duration_days, :status, :note)
            ');
            
            $stmt->execute([
                ':license_key' => $data['license_key'],
                ':product_id' => $data['product_id'],
                ':user_id' => $data['user_id'] ?? null,
                ':duration_days' => $data['duration_days'] ?? 30,
                ':status' => $data['status'] ?? 'unused',
                ':note' => $data['note'] ?? null,
            ]);

            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'License created successfully'
            ];
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return [
                    'success' => false,
                    'message' => 'License key already exists: ' . $data['license_key']
                ];
            }
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Batch create licenses
     */
    public function createBatch(array $licenses): array {
        $success = 0;
        $failed = 0;
        $errors = [];

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('
                INSERT INTO licenses (license_key, product_id, user_id, duration_days, status, note)
                VALUES (:license_key, :product_id, :user_id, :duration_days, :status, :note)
            ');

            foreach ($licenses as $license) {
                try {
                    $stmt->execute([
                        ':license_key' => $license['license_key'],
                        ':product_id' => $license['product_id'],
                        ':user_id' => $license['user_id'] ?? null,
                        ':duration_days' => $license['duration_days'] ?? 30,
                        ':status' => $license['status'] ?? 'unused',
                        ':note' => $license['note'] ?? null,
                    ]);
                    $success++;
                } catch (PDOException $e) {
                    $failed++;
                    if ($e->getCode() === '23000') {
                        $errors[] = 'Duplicate key: ' . $license['license_key'];
                    } else {
                        $errors[] = 'Error: ' . $e->getMessage();
                    }
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'created' => $success,
                'failed' => $failed,
                'errors' => $errors,
                'message' => sprintf('Created %d licenses, %d failed', $success, $failed)
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Batch creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update license
     */
    public function update(int $id, array $data): array {
        try {
            $stmt = $this->db->prepare('
                UPDATE licenses 
                SET product_id = :product_id, 
                    user_id = :user_id, 
                    duration_days = :duration_days, 
                    status = :status, 
                    note = :note
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':product_id' => $data['product_id'],
                ':user_id' => $data['user_id'] ?? null,
                ':duration_days' => $data['duration_days'],
                ':status' => $data['status'],
                ':note' => $data['note'] ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'License updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Activate license
     */
    public function activate(int $id): array {
        try {
            $license = $this->getById($id);
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }

            if ($license['status'] !== 'unused') {
                return ['success' => false, 'message' => 'License is not unused'];
            }

            $activatedAt = date('Y-m-d H:i:s');
            $expiresAt = null;

            if ($license['duration_days'] < 9999) {
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$license['duration_days']} days"));
            }

            $stmt = $this->db->prepare('
                UPDATE licenses 
                SET status = :status, 
                    activated_at = :activated_at, 
                    expires_at = :expires_at 
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':status' => 'active',
                ':activated_at' => $activatedAt,
                ':expires_at' => $expiresAt,
            ]);

            return [
                'success' => true,
                'message' => 'License activated successfully',
                'expires_at' => $expiresAt
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Activation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete license
     */
    public function delete(int $id): array {
        try {
            $stmt = $this->db->prepare('DELETE FROM licenses WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return [
                'success' => true,
                'message' => 'License deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Batch delete licenses
     */
    public function deleteBatch(array $ids): array {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'No valid license IDs provided'
            ];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("DELETE FROM licenses WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $deleted = $stmt->rowCount();

            return [
                'success' => true,
                'deleted' => $deleted,
                'message' => $deleted . ' license(s) deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get statistics
     */
    public function getStats(): array {
        $stmt = $this->db->query('
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = "unused" THEN 1 ELSE 0 END) as unused,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = "disabled" THEN 1 ELSE 0 END) as disabled
            FROM licenses
        ');
        return $stmt->fetch();
    }

    /**
     * Get licenses by product and user
     */
    public function getByProductAndUser(int $productId, int $userId): array {
        $stmt = $this->db->prepare('
            SELECT l.*, p.name as product_name
            FROM licenses l
            LEFT JOIN products p ON l.product_id = p.id
            WHERE l.product_id = :product_id AND l.user_id = :user_id
            ORDER BY l.created_at DESC
        ');
        $stmt->execute([
            ':product_id' => $productId,
            ':user_id' => $userId
        ]);
        return $stmt->fetchAll();
    }

    /**
     * 生成一个全局唯一的许可证密钥（格式：NMSL-XXXX-XXXX-XXXX-XXXX）
     */
    public static function generateLicenseKey(): string {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // 去除易混淆字符 O/0/I/1
        $block = function () use ($chars) {
            $s = '';
            for ($i = 0; $i < 4; $i++) {
                $s .= $chars[random_int(0, strlen($chars) - 1)];
            }
            return $s;
        };
        return 'NMSL-' . $block() . '-' . $block() . '-' . $block() . '-' . $block();
    }

    /**
     * 统计用户在某产品+某时长下已生成的钥匙数量（配额消耗）
     */
    public function getGeneratedCount(int $userId, int $productId, int $durationDays): int {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM licenses
            WHERE user_id = :user_id AND product_id = :product_id AND duration_days = :duration_days
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':duration_days' => $durationDays
        ]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * 创建/累加配额（Admin 分配钥匙数量给用户）
     * 同一「用户+产品+时长」组合存在时累加数量，否则新增
     */
    public function createAllocation(int $userId, int $productId, int $durationDays, int $quantity): array {
        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Quantity must be greater than 0'];
        }

        try {
            $stmt = $this->db->prepare('
                INSERT INTO license_allocations (user_id, product_id, duration_days, quantity)
                VALUES (:user_id, :product_id, :duration_days, :quantity)
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
            ');
            $stmt->execute([
                ':user_id' => $userId,
                ':product_id' => $productId,
                ':duration_days' => $durationDays,
                ':quantity' => $quantity
            ]);

            return ['success' => true, 'message' => 'Allocation saved successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Allocation failed: ' . $e->getMessage()];
        }
    }

    /**
     * 获取全部配额记录（含已生成数量与剩余），供 Admin 配额管理
     */
    public function getAllocations(): array {
        $stmt = $this->db->query('
            SELECT a.id, a.user_id, a.product_id, a.duration_days, a.quantity, a.created_at,
                   u.username, u.email, u.role,
                   p.name as product_name,
                   (SELECT COUNT(*) FROM licenses l
                    WHERE l.user_id = a.user_id AND l.product_id = a.product_id AND l.duration_days = a.duration_days) as used_count
            FROM license_allocations a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN products p ON a.product_id = p.id
            ORDER BY a.created_at DESC
        ');
        return $stmt->fetchAll();
    }

    /**
     * 获取指定用户的配额（含已生成/剩余），供 Manager 面板展示
     */
    public function getQuotaForUser(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT a.id, a.product_id, a.duration_days, a.quantity,
                   p.name as product_name,
                   (SELECT COUNT(*) FROM licenses l
                    WHERE l.user_id = a.user_id AND l.product_id = a.product_id AND l.duration_days = a.duration_days) as used_count
            FROM license_allocations a
            LEFT JOIN products p ON a.product_id = p.id
            WHERE a.user_id = :user_id
            ORDER BY a.created_at DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * 删除/撤销配额（Admin 撤销未用完额度）
     */
    public function deleteAllocation(int $id): array {
        try {
            $stmt = $this->db->prepare('DELETE FROM license_allocations WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => 'Allocation removed successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Remove failed: ' . $e->getMessage()];
        }
    }

    /**
     * 在配额范围内为指定用户自动生成钥匙（绑定 user_id，状态 unused）
     * 返回生成的钥匙列表；数量超过剩余配额则拒绝
     */
    public function generateKeys(int $userId, int $productId, int $durationDays, int $quantity): array {
        $quota = $this->getQuotaForUser($userId);
        $available = 0;
        foreach ($quota as $q) {
            if ((int)$q['product_id'] === $productId && (int)$q['duration_days'] === $durationDays) {
                $available = (int)$q['quantity'] - (int)$q['used_count'];
                break;
            }
        }

        if ($available <= 0) {
            return ['success' => false, 'message' => 'No quota available for this product and duration'];
        }
        if ($quantity > $available) {
            return [
                'success' => false,
                'message' => sprintf('Insufficient quota. Available: %d, Requested: %d', $available, $quantity)
            ];
        }

        // 生成唯一钥匙
        $keys = [];
        $seen = [];
        $tries = 0;
        while (count($keys) < $quantity && $tries < 200) {
            $tries++;
            $key = self::generateLicenseKey();
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            if ($this->keyExists($key)) {
                continue;
            }
            $keys[] = $key;
        }

        if (count($keys) < $quantity) {
            return ['success' => false, 'message' => 'Failed to generate unique keys, please try again'];
        }

        $data = [];
        foreach ($keys as $key) {
            $data[] = [
                'license_key' => $key,
                'product_id' => $productId,
                'user_id' => $userId,
                'duration_days' => $durationDays,
                'status' => 'unused',
                'note' => 'Generated from quota'
            ];
        }

        $result = $this->createBatch($data);
        if ($result['success'] && $result['created'] > 0) {
            $result['keys'] = array_slice($keys, 0, $result['created']);
        }
        return $result;
    }

    /**
     * 检查钥匙是否已存在
     */
    public function keyExists(string $key): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM licenses WHERE license_key = :key');
        $stmt->execute([':key' => $key]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * 回收钥匙：仅允许「未激活（unused）且已绑定用户」的钥匙取消分配，重新入库
     */
    public function recycle(int $id): array {
        try {
            $license = $this->getById($id);
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }
            if ($license['status'] !== 'unused') {
                return ['success' => false, 'message' => 'Only unused licenses can be recycled'];
            }
            if (empty($license['user_id'])) {
                return ['success' => false, 'message' => 'License is not assigned to any user'];
            }

            $stmt = $this->db->prepare('UPDATE licenses SET user_id = NULL WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return ['success' => true, 'message' => 'License recycled back to inventory'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Recycle failed: ' . $e->getMessage()];
        }
    }
}
