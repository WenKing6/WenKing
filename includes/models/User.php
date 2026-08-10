<?php
/**
 * 用户模型类
 * 处理用户注册、登录、查询、角色管理等操作
 */

require_once __DIR__ . '/../Database.php';

class User {
    private PDO $db;

    /**
     * 角色层级定义（数字越大权限越高）
     */
    private const ROLE_HIERARCHY = [
        'user'     => 1,
        'reseller' => 2,
        'manager'  => 3,
        'admin'    => 4,
    ];

    /**
     * 角色显示名称
     */
    private const ROLE_LABELS = [
        'user'     => 'User',
        'reseller' => 'Reseller',
        'manager'  => 'Manager',
        'admin'    => 'Admin',
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * 注册新用户（默认分配 User 角色）
     */
    public function register(string $username, string $email, string $password): array {
        // 检查用户名是否已存在
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username OR email = :email');
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => '用户名或邮箱已被注册'];
        }

        // 插入新用户，默认角色为 user
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)'
        );
        $stmt->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':password_hash' => $passwordHash,
            ':role'          => 'user',
        ]);

        return ['success' => true, 'user_id' => $this->db->lastInsertId()];
    }

    /**
     * 用户登录
     */
    public function login(string $username, string $password): array {
        $stmt = $this->db->prepare('SELECT id, username, email, password_hash, role, status FROM users WHERE username = :username OR email = :email');
        $stmt->execute([':username' => $username, ':email' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => '用户名或密码错误'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => '账户已被禁用'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => '用户名或密码错误'];
        }

        return [
            'success' => true,
            'user'    => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ],
        ];
    }

    /**
     * 根据邮箱查找用户
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT id, username, email, role FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * 根据 ID 查找用户
     */
    public function findById(int $userId): ?array {
        $stmt = $this->db->prepare('SELECT id, username, email, role, status, created_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * 重置密码
     */
    public function resetPassword(int $userId, string $newPassword): bool {
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        return $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id'            => $userId,
        ]);
    }

    /**
     * 获取所有用户
     */
    public function getAll(): array {
        $stmt = $this->db->query('SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 根据状态筛选用户
     */
    public function getByStatus(string $status): array {
        $stmt = $this->db->prepare('SELECT id, username, email, role, status, created_at FROM users WHERE status = :status ORDER BY created_at DESC');
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 根据角色筛选用户
     */
    public function getByRole(string $role): array {
        $stmt = $this->db->prepare('SELECT id, username, email, role, status, created_at FROM users WHERE role = :role ORDER BY created_at DESC');
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 搜索用户
     */
    public function search(string $keyword): array {
        $stmt = $this->db->prepare('SELECT id, username, email, role, status, created_at FROM users WHERE username LIKE :keyword OR email LIKE :keyword ORDER BY created_at DESC');
        $stmt->execute([':keyword' => '%' . $keyword . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 更新用户状态
     */
    public function updateStatus(int $userId, string $status): bool {
        $stmt = $this->db->prepare('UPDATE users SET status = :status WHERE id = :id');
        return $stmt->execute([':status' => $status, ':id' => $userId]);
    }

    /**
     * 删除用户
     */
    public function delete(int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * 更新用户信息
     */
    public function update(int $userId, string $username, string $email, string $status, string $password = '', string $role = ''): array {
        // 检查用户名是否已被其他用户使用
        $stmt = $this->db->prepare('SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id');
        $stmt->execute([':username' => $username, ':email' => $email, ':id' => $userId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // 验证角色合法性
        if (!empty($role) && !array_key_exists($role, self::ROLE_HIERARCHY)) {
            return ['success' => false, 'message' => 'Invalid role'];
        }

        if (!empty($password) && !empty($role)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare('UPDATE users SET username = :username, email = :email, status = :status, role = :role, password_hash = :password_hash WHERE id = :id');
            $success = $stmt->execute([
                ':username'      => $username,
                ':email'         => $email,
                ':status'        => $status,
                ':role'          => $role,
                ':password_hash' => $passwordHash,
                ':id'            => $userId,
            ]);
        } elseif (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare('UPDATE users SET username = :username, email = :email, status = :status, password_hash = :password_hash WHERE id = :id');
            $success = $stmt->execute([
                ':username'      => $username,
                ':email'         => $email,
                ':status'        => $status,
                ':password_hash' => $passwordHash,
                ':id'            => $userId,
            ]);
        } elseif (!empty($role)) {
            $stmt = $this->db->prepare('UPDATE users SET username = :username, email = :email, status = :status, role = :role WHERE id = :id');
            $success = $stmt->execute([
                ':username' => $username,
                ':email'    => $email,
                ':status'   => $status,
                ':role'     => $role,
                ':id'       => $userId,
            ]);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET username = :username, email = :email, status = :status WHERE id = :id');
            $success = $stmt->execute([
                ':username' => $username,
                ':email'    => $email,
                ':status'   => $status,
                ':id'       => $userId,
            ]);
        }

        return ['success' => $success, 'message' => $success ? 'User updated successfully' : 'Update failed'];
    }

    /**
     * 更新用户角色
     */
    public function updateRole(int $userId, string $newRole, int $operatorId): array {
        // 验证角色是否合法
        if (!array_key_exists($newRole, self::ROLE_HIERARCHY)) {
            return ['success' => false, 'message' => 'Invalid role'];
        }

        // 获取操作者角色
        $operator = $this->findById($operatorId);
        if (!$operator) {
            return ['success' => false, 'message' => 'Operator not found'];
        }

        // 权限检查：操作者角色必须高于目标角色
        $operatorLevel = self::ROLE_HIERARCHY[$operator['role']] ?? 0;
        $targetLevel = self::ROLE_HIERARCHY[$newRole] ?? 0;

        if ($operatorLevel <= $targetLevel) {
            return ['success' => false, 'message' => 'Insufficient permissions to assign this role'];
        }

        // 获取目标用户当前角色
        $targetUser = $this->findById($userId);
        if (!$targetUser) {
            return ['success' => false, 'message' => 'Target user not found'];
        }

        $oldRole = $targetUser['role'];

        // 执行角色更新
        $stmt = $this->db->prepare('UPDATE users SET role = :role WHERE id = :id');
        $success = $stmt->execute([':role' => $newRole, ':id' => $userId]);

        if ($success) {
            // 记录操作日志
            $this->logActivity($operatorId, 'role_change', $userId, json_encode([
                'old_role' => $oldRole,
                'new_role' => $newRole,
            ]));
        }

        return ['success' => $success, 'message' => $success ? 'Role updated successfully' : 'Update failed'];
    }

    /**
     * 检查用户是否有指定角色
     */
    public function hasRole(int $userId, string $role): bool {
        $user = $this->findById($userId);
        return $user && $user['role'] === $role;
    }

    /**
     * 检查用户角色是否达到指定级别
     */
    public function hasRoleLevel(int $userId, string $minRole): bool {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }
        $userLevel = self::ROLE_HIERARCHY[$user['role']] ?? 0;
        $minLevel = self::ROLE_HIERARCHY[$minRole] ?? 0;
        return $userLevel >= $minLevel;
    }

    /**
     * 获取角色显示名称
     */
    public function getRoleLabel(string $role): string {
        return self::ROLE_LABELS[$role] ?? ucfirst($role);
    }

    /**
     * 获取所有可用角色
     */
    public function getAvailableRoles(): array {
        return self::ROLE_LABELS;
    }

    /**
     * 记录操作日志
     */
    private function logActivity(int $userId, string $action, ?int $targetUserId = null, ?string $details = null): void {
        $stmt = $this->db->prepare(
            'INSERT INTO activity_log (user_id, action, target_user_id, details, ip_address) VALUES (:user_id, :action, :target_user_id, :details, :ip_address)'
        );
        $stmt->execute([
            ':user_id'       => $userId,
            ':action'        => $action,
            ':target_user_id' => $targetUserId,
            ':details'       => $details,
            ':ip_address'    => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
