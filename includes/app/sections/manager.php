<?php
/**
 * Manager 页面 - 管理员控制面板
 * 用户管理与许可证管理样式与 Admin Panel 保持一致
 */
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/License.php';
require_once __DIR__ . '/../section-helpers.php';
$productModel = new Product();
$userModel = new User();
$licenseModel = new License();
$products = $productModel->getAll();
$users = $userModel->getAll();

// 当前用户识别：Session + 数据库角色校验（不信任 URL 参数，防篡改）
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUser = $sessionUserId > 0 ? $userModel->findById($sessionUserId) : null;
$currentRole = $currentUser ? $currentUser['role'] : '';
$isManagerView = in_array($currentRole, ['manager', 'reseller'], true);
$isAdminView = $currentRole === 'admin';
$canViewLicenses = $isManagerView || $isAdminView;

// 经理只看自己的钥匙；管理员可查看全部（审计用）
if ($isManagerView) {
    $licenses = $licenseModel->getByUser($sessionUserId);
    $allocations = $licenseModel->getAllocations($sessionUserId);
} elseif ($isAdminView) {
    $licenses = $licenseModel->getAll();
    $allocations = $licenseModel->getAllocations();
} else {
    $licenses = [];
    $allocations = [];
}

// 页面可见性配置（权限矩阵唯一来源：LicenseModule）
$vis = LicenseModule::getUiVisibility($currentRole);

// 经理「分配许可证给经销商」所需数据
$resellerUsers = array_values(array_filter($users, fn($u) => $u['role'] === 'reseller'));
// 自己的配额映射 "productId|duration" => remaining（划转额度提示）
$ownQuotaMap = [];
foreach ($allocations as $a) {
    $ownQuotaMap[(int)$a['product_id'] . '|' . (int)$a['duration_days']] = (int)$a['quantity'] - (int)$a['used_count'];
}

?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent"><?php _e('manager.title'); ?></span>
    </h1>
    <p class="text-white/60"><?php _e('manager.subtitle'); ?></p>
    <?php if ($currentUser): ?>
    <p class="text-xs text-white/40 mt-2">Signed in as <span class="text-accent-cyan"><?php echo htmlspecialchars($currentUser['username']); ?></span> (<?php echo ucfirst($currentRole); ?>)</p>
    <?php endif; ?>
</div>

<!-- Tab 导航 -->
<div class="manager-tabs mb-6 grid grid-cols-2 gap-2 border-b border-white/10">
    <button class="manager-tab active flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="users">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <?php _e('manager.user_management'); ?>
    </button>
    <button class="manager-tab flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <?php _e('manager.license_management'); ?>
    </button>
</div>

<!-- Tab 内容区域 -->
<div class="manager-tab-content">
    <!-- Users Tab -->
    <div id="users-tab" class="tab-panel active">
        <?php
        $userRoleOptions = ['' => 'All Roles'];
        foreach ($userModel->getAvailableRoles() as $key => $label) {
            $userRoleOptions[$key] = $label;
        }
        renderUserSection($users, ['role_options' => $userRoleOptions]);
        ?>
    </div>

    <!-- Licenses Tab -->
    <div id="licenses-tab" class="tab-panel">
        <?php if (!$canViewLicenses): ?>
        <div class="glass-card p-6 rounded-xl mb-6 text-center">
            <p class="text-white/60">Please sign in with a Manager account to view your licenses.</p>
        </div>
        <?php endif; ?>
        <?php
        renderLicenseSection($licenses, $products, $vis, [
            'grant_label' => 'Assign to Reseller',
        ]);
        ?>
    </div>
</div>

<!-- 许可证模块共享弹窗（outside tab-panel to avoid display:none） -->
<?php renderClaimKeysModal($allocations); ?>
<?php renderGrantQuotaModal([
    'id_prefix'       => 'gq',
    'title'           => 'Assign Licenses to Reseller',
    'show_role_select' => false,
    'fixed_role'      => 'reseller',
    'resellers'       => $resellerUsers,
    'products'        => $products,
    'from_own_quota'  => true,
    'own_quotas'      => $ownQuotaMap,
    'submit_label'    => 'Assign Quota',
]); ?>

<!-- User Edit Modal (outside tab-panel to avoid display:none) -->
<?php renderUserEditModal(['show_role' => true]); ?>
