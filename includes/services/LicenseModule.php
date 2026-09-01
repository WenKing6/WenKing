<?php
/**
 * ============================================================
 * 许可证业务模块（可复用通用模块）
 * ============================================================
 * 统一封装「配额授权（分配钥匙）」与「库存领取（Create License）」两大核心能力，
 * 所有角色权限均由 权限矩阵（self::PERMISSIONS）配置驱动：
 * 后续新增角色或调整权限，只需修改 PERMISSIONS 一处配置，无需改动任何业务代码。
 *
 * 权限模型（配额转移制）：
 *   Admin    ──授权──▶ Manager / Reseller   （配额来源：管理员，不占用他人配额）
 *   Manager  ──授权──▶ Reseller             （配额来源：经理自己的剩余配额，划转制）
 *   Manager / Reseller ──领取──▶ 全局库存    （licenses 表中 unused 且未分配的钥匙）
 *
 * 术语约定：
 *   - 「授权」(grant_quota)：给某角色授予「产品 + 时长」维度可领取的钥匙数量配额
 *   - 「领取」(claim)：从库存中把钥匙挂到自己名下，同步扣减自己的配额 used_count
 *   - 「划转」(transfer)：经理授权经销商时，立即从经理剩余配额中扣减对应数量
 *
 * 接口一览（纯函数均可脱离数据库进行单元测试）：
 *   LicenseModule::can($role, $action, $targetRole)      角色能否执行某动作
 *   LicenseModule::getUiVisibility($role)                 角色在页面上可见的内容配置
 *   LicenseModule::evaluateManagerTransfer($own, $qty)    经理划转额度校验
 *   (new LicenseModule())->grantQuota($actor, ...)        授权配额（含权限校验与划转事务）
 *   (new LicenseModule())->claim($actor, ...)             从库存领取钥匙（含权限校验）
 */

require_once __DIR__ . '/../models/License.php';
require_once __DIR__ . '/../models/User.php';

class LicenseModule
{
    /** 动作：授权配额（分配钥匙） */
    public const ACTION_GRANT_QUOTA = 'grant_quota';

    /** 动作：从库存领取钥匙（Create License 按钮） */
    public const ACTION_CLAIM = 'claim';

    /** 动作：回收未激活钥匙回库存（仅管理员） */
    public const ACTION_RECYCLE = 'recycle';

    /** 动作：删除配额记录（仅管理员） */
    public const ACTION_DELETE_ALLOCATION = 'delete_allocation';

    /** 动作：生成新钥匙入库（仅管理员，通过 Add License / batch_create 完成） */
    public const ACTION_GENERATE = 'generate';

    /**
     * 角色权限矩阵（唯一权限配置源）
     *
     * 结构说明：
     *   - ACTION_CLAIM / ACTION_RECYCLE / ACTION_DELETE_ALLOCATION / ACTION_GENERATE：
     *         bool，表示该角色能否执行此动作
     *   - ACTION_GRANT_QUOTA：数组，表示该角色可以将配额授予哪些目标角色
     *   - 'from_own_quota'：bool，授权时是否从自己的剩余配额中划转
     *         （manager 为 true：授予经销商的数量立即从经理配额 used_count 中扣减）
     *
     * 调整示例：若希望 Reseller 也能授权给普通用户，
     * 只需把 'reseller' 的 ACTION_GRANT_QUOTA 改为 ['user'] 并按需打开 from_own_quota。
     */
    private const PERMISSIONS = [
        'admin' => [
            self::ACTION_CLAIM             => false, // 管理员直接拥有库存，无需领取
            self::ACTION_GRANT_QUOTA       => ['manager', 'reseller'],
            self::ACTION_RECYCLE           => true,
            self::ACTION_DELETE_ALLOCATION => true,
            self::ACTION_GENERATE          => true,
            'from_own_quota'               => false,
        ],
        'manager' => [
            self::ACTION_CLAIM             => true,
            self::ACTION_GRANT_QUOTA       => ['reseller'],
            self::ACTION_RECYCLE           => false,
            self::ACTION_DELETE_ALLOCATION => false,
            self::ACTION_GENERATE          => false,
            'from_own_quota'               => true,
        ],
        'reseller' => [
            self::ACTION_CLAIM             => true,
            self::ACTION_GRANT_QUOTA       => [],
            self::ACTION_RECYCLE           => false,
            self::ACTION_DELETE_ALLOCATION => false,
            self::ACTION_GENERATE          => false,
            'from_own_quota'               => false,
        ],
        'user' => [
            self::ACTION_CLAIM             => false,
            self::ACTION_GRANT_QUOTA       => [],
            self::ACTION_RECYCLE           => false,
            self::ACTION_DELETE_ALLOCATION => false,
            self::ACTION_GENERATE          => false,
            'from_own_quota'               => false,
        ],
    ];

    /** 授权/领取事务中的锁等待上限（秒）：锁冲突时快速失败并给出明确提示，避免页面长时间无响应 */
    private const LOCK_WAIT_SECONDS = 10;

    /**
     * 判断角色能否执行某动作（纯函数，可单测）
     *
     * @param string      $role       执行者角色（admin/manager/reseller/user）
     * @param string      $action     动作常量（ACTION_*）
     * @param string|null $targetRole 授权动作的目标角色；其他动作传 null
     */
    public static function can(string $role, string $action, ?string $targetRole = null): bool
    {
        if (!isset(self::PERMISSIONS[$role])) {
            return false;
        }
        $perms = self::PERMISSIONS[$role];

        if ($action === self::ACTION_GRANT_QUOTA) {
            return $targetRole !== null
                && isset($perms[self::ACTION_GRANT_QUOTA])
                && in_array($targetRole, $perms[self::ACTION_GRANT_QUOTA], true);
        }

        return !empty($perms[$action]);
    }

    /**
     * 返回角色在页面上可见的内容配置（纯函数，可单测）
     *
     * 返回结构：
     *   - quota_panel:        bool  是否渲染 My Quota 配额面板
     *   - create_license_btn: bool  是否显示 Create License（领取）按钮
     *   - grant_quota_targets:[]   「分配许可证」按钮可见的授权目标角色列表（空 = 不显示按钮）
     *   - license_scope:      string 钥匙列表范围：all（全部，审计）| own（仅自己的）| none
     *   - recycle / delete_license / add_license / user_management: bool
     *     对应页面上回收按钮、删除按钮、Add License 入口、用户管理标签的可见性
     *
     * 页面渲染时按此配置输出内容，前端 JS 不做任何权限判断，
     * 保证「页面可见性」与「接口权限」同源（均来自 PERMISSIONS）。
     */
    public static function getUiVisibility(string $role): array
    {
        $perms = self::PERMISSIONS[$role] ?? null;
        if ($perms === null) {
            // 未知角色：什么都不可见（安全默认值）
            return [
                'quota_panel'        => false,
                'create_license_btn' => false,
                'grant_quota_targets' => [],
                'license_scope'      => 'none',
                'recycle'            => false,
                'delete_license'     => false,
                'add_license'        => false,
                'user_management'    => false,
            ];
        }

        return [
            'quota_panel'         => self::can($role, self::ACTION_CLAIM) || !empty($perms[self::ACTION_GRANT_QUOTA]),
            'create_license_btn'  => self::can($role, self::ACTION_CLAIM),
            'grant_quota_targets' => $perms[self::ACTION_GRANT_QUOTA] ?? [],
            'license_scope'       => $role === 'admin' ? 'all' : (self::can($role, self::ACTION_CLAIM) ? 'own' : 'none'),
            'recycle'             => self::can($role, self::ACTION_RECYCLE),
            'delete_license'      => self::can($role, self::ACTION_GENERATE) && $role === 'admin',
            'add_license'         => self::can($role, self::ACTION_GENERATE),
            'user_management'     => $role === 'admin' || $role === 'manager',
        ];
    }

    /**
     * 校验经理向经销商划转配额是否可行（纯函数，可单测）
     *
     * @param array|null $ownAllocation 经理自己的配额行（含 quantity/used_count），无配额时传 null
     * @param int        $quantity      拟划转数量
     * @return array{ok: bool, message: string, remaining: int}
     *         remaining 语义：校验失败时为当前剩余额度；校验通过时为划转后的剩余额度
     */
    public static function evaluateManagerTransfer(?array $ownAllocation, int $quantity): array
    {
        if ($quantity <= 0) {
            return ['ok' => false, 'message' => 'Invalid quantity', 'remaining' => 0];
        }
        if (!$ownAllocation) {
            return ['ok' => false, 'message' => 'You have no quota for this product & duration', 'remaining' => 0];
        }
        $remaining = (int)$ownAllocation['quantity'] - (int)$ownAllocation['used_count'];
        if ($remaining < $quantity) {
            return [
                'ok'       => false,
                'message'  => 'Insufficient quota, only ' . $remaining . ' remaining for this product & duration',
                'remaining' => $remaining,
            ];
        }
        return ['ok' => true, 'message' => 'Quota granted successfully', 'remaining' => $remaining - $quantity];
    }

    /**
     * 授权配额（分配钥匙）
     *
     * 权限校验通过后：
     *   - admin：直接为目标角色创建/累加配额（来源管理员，无上限）
     *   - manager（划转制）：事务内锁定自己的配额行 → 校验剩余充足 → 目标配额入账 → 自己 used_count 扣减
     *   - 其他角色：拒绝
     *
     * @param array $actor        执行者 ['id' => int, 'role' => string]（来自 Session + 数据库校验）
     * @param int   $targetUserId 目标用户 ID
     * @param int   $productId    产品 ID
     * @param int   $durationDays 时长（天）
     * @param int   $quantity     数量
     * @return array{success: bool, message: string}
     */
    public function grantQuota(array $actor, int $targetUserId, int $productId, int $durationDays, int $quantity): array
    {
        if (empty($actor['id']) || empty($actor['role'])) {
            return ['success' => false, 'message' => 'Please sign in'];
        }
        if ($targetUserId <= 0 || $productId <= 0 || $durationDays <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // 目标用户必须存在，且在执行者的可授权角色列表内
        $userModel = new User();
        $target = $userModel->findById($targetUserId);
        if (!$target) {
            return ['success' => false, 'message' => 'Target user not found'];
        }
        if (!self::can($actor['role'], self::ACTION_GRANT_QUOTA, $target['role'])) {
            return ['success' => false, 'message' => 'Permission denied'];
        }

        $license = new License();

        // admin：直接授权，无划转
        if (!self::PERMISSIONS[$actor['role']]['from_own_quota']) {
            return $license->createAllocation($targetUserId, $productId, $durationDays, $quantity);
        }

        // manager：从自己剩余配额中划转（事务 + 行锁 + 短锁等待，冲突时快速失败）
        $pdo = $license->getPdo();
        $pdo->exec('SET SESSION innodb_lock_wait_timeout = ' . self::LOCK_WAIT_SECONDS);
        try {
            $pdo->beginTransaction();

            // 锁定自己的配额行，防止并发划转超发
            $own = $license->getAllocationRow((int)$actor['id'], $productId, $durationDays, true);
            $check = self::evaluateManagerTransfer($own, $quantity);
            if (!$check['ok']) {
                $pdo->rollBack();
                return ['success' => false, 'message' => $check['message']];
            }

            // 目标配额入账（已存在则累加）
            $result = $license->createAllocation($targetUserId, $productId, $durationDays, $quantity);
            if (empty($result['success'])) {
                $pdo->rollBack();
                return $result;
            }

            // 自己的剩余配额同步扣减（划转出去）
            $license->addUsedCount((int)$own['id'], $quantity);

            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * 从库存领取钥匙（Create License）
     *
     * 权限校验通过后委托 License::claimKeys：
     * 事务内校验配额剩余与库存数量，锁定库存钥匙并挂到执行者名下。
     *
     * @param array $actor        执行者 ['id' => int, 'role' => string]
     * @param int   $productId    产品 ID
     * @param int   $durationDays 时长（天）
     * @param int   $quantity     数量
     * @return array{success: bool, message: string}
     */
    public function claim(array $actor, int $productId, int $durationDays, int $quantity): array
    {
        if (empty($actor['id']) || empty($actor['role'])) {
            return ['success' => false, 'message' => 'Please sign in'];
        }
        if (!self::can($actor['role'], self::ACTION_CLAIM)) {
            return ['success' => false, 'message' => 'Permission denied'];
        }
        if ($productId <= 0 || $durationDays <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Product, duration and quantity are required'];
        }

        $license = new License();
        return $license->claimKeys((int)$actor['id'], $productId, $durationDays, $quantity);
    }
}
