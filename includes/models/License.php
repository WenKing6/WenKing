<?php
/**
 * License Model Class
 * Handles license CRUD operations
 */

require_once __DIR__ . '/../Database.php';

class License {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
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
}
