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
            $this->ensureClaimIndex();
            self::$allocationTableChecked = true;
        }
    }

    /**
     * 确保 license_allocations 配额表存在（幂等自愈迁移）
     * 配额 = Admin 给经理分配的「某产品 + 某时长」可领取的钥匙数量
     * quantity   = 总量
     * used_count = 经理已领取的数量
     */
    private function ensureAllocationTable(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS `license_allocations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `duration_days` INT NOT NULL DEFAULT 30,
            `quantity` INT NOT NULL DEFAULT 0,
            `used_count` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_user_product_duration` (`user_id`, `product_id`, `duration_days`),
            KEY `idx_alloc_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * 确保 licenses 表存在领取查询复合索引（幂等自愈迁移）
     * claimKeys 的 SELECT ... FOR UPDATE 按 (product_id, duration_days, status, user_id) 精确锁定，
     * 缺失该索引时会走近似全表扫描并放大锁范围，导致并发下长时间锁等待
     */
    private function ensureClaimIndex(): void {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = 'licenses'
                  AND index_name = 'idx_claim_lookup'
            ");
            $stmt->execute();
            if ((int)$stmt->fetchColumn() === 0) {
                $this->db->exec("
                    ALTER TABLE `licenses`
                    ADD INDEX `idx_claim_lookup` (`product_id`, `duration_days`, `status`, `user_id`)
                ");
            }
        } catch (PDOException $e) {
            // 索引创建失败不阻断业务（例如权限不足），仅保留原查询路径
        }
    }

    /**
     * Get all licenses
     */
    public function getAll(): array {
        $stmt = $this->db->query('
            SELECT l.*, p.name as product_name, u.username as user_name, u.email as user_email
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
     * Get licenses assigned to a specific user (经理只看自己的钥匙)
     */
    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare('
            SELECT l.*, p.name as product_name, u.username as user_name
            FROM licenses l
            LEFT JOIN products p ON l.product_id = p.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.user_id = :user_id
            ORDER BY l.created_at DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all allocations (管理员) or allocations of one user (经理)
     */
    public function getAllocations(?int $userId = null): array {
        $sql = '
            SELECT a.*, p.name as product_name, u.username as manager_name
            FROM license_allocations a
            LEFT JOIN products p ON a.product_id = p.id
            LEFT JOIN users u ON a.user_id = u.id
        ';
        $params = [];
        if ($userId !== null) {
            $sql .= ' WHERE a.user_id = :user_id';
            $params[':user_id'] = $userId;
        }
        $sql .= ' ORDER BY a.updated_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create / increase a quota for a manager (某产品 + 某时长 可领 X 把钥匙)
     */
    public function createAllocation(int $userId, int $productId, int $durationDays, int $quantity): array {
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
                ':quantity' => $quantity,
            ]);
            return ['success' => true, 'message' => 'Quota granted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete an allocation (管理员撤销配额)
     */
    public function deleteAllocation(int $id): array {
        try {
            $stmt = $this->db->prepare('DELETE FROM license_allocations WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => 'Quota deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
        }
    }

    /**
     * 统计某产品 + 某时长的库存可用钥匙数量（unused 且未分配给任何人）
     */
    public function getInventoryCount(int $productId, int $durationDays): int {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM licenses
            WHERE product_id = :product_id AND duration_days = :duration_days
              AND status = "unused" AND user_id IS NULL
        ');
        $stmt->execute([':product_id' => $productId, ':duration_days' => $durationDays]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * 经理从库存领取钥匙（事务：校验配额剩余 + 库存数量，分配钥匙并同步 used_count）
     */
    public function claimKeys(int $userId, int $productId, int $durationDays, int $quantity): array {
        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid quantity'];
        }

        try {
            $this->db->beginTransaction();

            // 锁定配额行，防止并发超领
            $stmt = $this->db->prepare('
                SELECT id, quantity, used_count FROM license_allocations
                WHERE user_id = :user_id AND product_id = :product_id AND duration_days = :duration_days
                FOR UPDATE
            ');
            $stmt->execute([
                ':user_id' => $userId,
                ':product_id' => $productId,
                ':duration_days' => $durationDays,
            ]);
            $alloc = $stmt->fetch();
            if (!$alloc) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No quota allocated for this product & duration'];
            }

            $remaining = (int)$alloc['quantity'] - (int)$alloc['used_count'];
            if ($remaining < $quantity) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Insufficient quota, only ' . $remaining . ' key(s) remaining'];
            }

            // 锁定库存中可分配的钥匙（unused 且未分配）
            $limit = (int)$quantity; // 已校验为正整数，直接内联避免 LIMIT 参数化问题
            $stmt = $this->db->prepare('
                SELECT id FROM licenses
                WHERE product_id = :product_id AND duration_days = :duration_days
                  AND status = "unused" AND user_id IS NULL
                LIMIT ' . $limit . '
                FOR UPDATE
            ');
            $stmt->execute([':product_id' => $productId, ':duration_days' => $durationDays]);
            $keys = $stmt->fetchAll();

            if (count($keys) < $quantity) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Not enough keys in inventory (available: ' . count($keys) . ')'];
            }

            // 把钥匙挂到经理名下
            $ids = array_column($keys, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $upd = $this->db->prepare("UPDATE licenses SET user_id = ? WHERE id IN ($placeholders)");
            $upd->execute(array_merge([$userId], $ids));

            // 同步配额已领取数量
            $upd2 = $this->db->prepare('UPDATE license_allocations SET used_count = used_count + ? WHERE id = ?');
            $upd2->execute([$quantity, $alloc['id']]);

            $this->db->commit();
            return ['success' => true, 'claimed' => count($ids), 'message' => count($ids) . ' key(s) claimed successfully'];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Claim failed: ' . $e->getMessage()];
        }
    }

    /**
     * 管理员回收经理已领取但未激活的钥匙（回库存，配额 used_count 减一）
     */
    public function recycle(int $licenseId): array {
        try {
            $license = $this->getById($licenseId);
            if (!$license) {
                return ['success' => false, 'message' => 'License not found'];
            }
            if (!$license['user_id']) {
                return ['success' => false, 'message' => 'License is not assigned to any user'];
            }
            if ($license['status'] !== 'unused') {
                return ['success' => false, 'message' => 'Only unactivated keys can be recycled'];
            }

            $this->db->beginTransaction();

            // 收回钥匙回库存
            $stmt = $this->db->prepare('UPDATE licenses SET user_id = NULL WHERE id = :id');
            $stmt->execute([':id' => $licenseId]);

            // 对应配额 used_count 减一（不低于 0）
            $stmt = $this->db->prepare('
                UPDATE license_allocations SET used_count = GREATEST(used_count - 1, 0)
                WHERE user_id = :user_id AND product_id = :product_id AND duration_days = :duration_days
            ');
            $stmt->execute([
                ':user_id' => $license['user_id'],
                ':product_id' => $license['product_id'],
                ':duration_days' => $license['duration_days'],
            ]);

            $this->db->commit();
            return ['success' => true, 'message' => 'License recycled back to inventory'];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Recycle failed: ' . $e->getMessage()];
        }
    }
}
