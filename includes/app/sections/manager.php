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
<div class="manager-tabs mb-6 flex gap-2 border-b border-white/10">
    <button class="manager-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="users">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <?php _e('manager.user_management'); ?>
    </button>
    <button class="manager-tab active px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <?php _e('manager.license_management'); ?>
    </button>
</div>

<!-- Tab 内容区域 -->
<div class="manager-tab-content">
    <!-- Users Tab -->
    <div id="users-tab" class="tab-panel">
        <!-- User Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Total Users</span>
                </div>
                <div class="text-3xl font-bold text-white"><?php echo count($users); ?></div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-online/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-online" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Active</span>
                </div>
                <div class="text-3xl font-bold text-white"><?php echo count(array_filter($users, fn($u) => $u['status'] === 'active')); ?></div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Inactive / Banned</span>
                </div>
                <div class="text-3xl font-bold text-white"><?php echo count(array_filter($users, fn($u) => $u['status'] !== 'active')); ?></div>
            </div>
        </div>

        <!-- User List -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-lg font-semibold text-white">User List (<span id="user-count"><?php echo count($users); ?></span>)</h3>
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-56">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" id="user-search" class="app-input w-full pl-9 pr-4 py-2" placeholder="Search by username...">
                    </div>
                    <?php
                    $roleOptions = ['' => 'All Roles'];
                    foreach ($userModel->getAvailableRoles() as $key => $label) {
                        $roleOptions[$key] = $label;
                    }
                    renderCustomSelect('user-role-filter', $roleOptions, '', 'w-full sm:w-36');
                    ?>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <colgroup>
                        <col class="w-[20%]">
                        <col class="w-[26%]">
                        <col class="w-[12%]">
                        <col class="w-[12%]">
                        <col class="w-[16%]">
                        <col class="w-[14%]">
                    </colgroup>
                    <thead>
                        <tr class="text-white/50 border-b border-white/10">
                            <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('username')">Username</th>
                            <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('email')">Email</th>
                            <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('role')">Role</th>
                            <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('status')">Status</th>
                            <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('created_at')">Created</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-list">
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-white/40">
                                <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                No users found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr class="user-row border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo $u['id']; ?>" data-username="<?php echo htmlspecialchars(strtolower($u['username'])); ?>" data-email="<?php echo htmlspecialchars(strtolower($u['email'])); ?>" data-role="<?php echo $u['role']; ?>" data-status="<?php echo $u['status']; ?>" data-created="<?php echo $u['created_at']; ?>">
                                <td class="py-3 px-4 font-medium text-white user-cell-username"><?php echo htmlspecialchars($u['username']); ?></td>
                                <td class="py-3 px-4 text-white/60 user-cell-email"><span class="block truncate"><?php echo htmlspecialchars($u['email']); ?></span></td>
                                <td class="py-3 px-4 user-cell-role">
                                    <span class="role-badge text-xs px-2 py-0.5 rounded-full border <?php echo $u['role'] === 'admin' ? 'bg-accent-purple/20 text-accent-purple border-accent-purple/30' : ($u['role'] === 'manager' ? 'bg-accent-cyan/20 text-accent-cyan border-accent-cyan/30' : ($u['role'] === 'reseller' ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-white/10 text-white/60 border-white/10')); ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 user-cell-status">
                                    <span class="status-badge <?php echo $u['status'] === 'active' ? 'status-online' : 'bg-red-500/20 text-red-400 border-red-500/30'; ?> text-xs">
                                        <?php echo ucfirst($u['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-white/40 user-cell-created"><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                                <td class="py-3 px-2 flex gap-1.5">
                                    <button class="btn-user-edit text-white/40 hover:text-accent-blue transition p-1.5 rounded-lg hover:bg-white/5" title="Edit" data-user='<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>'>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn-user-toggle text-white/40 hover:text-accent-blue transition p-1.5 rounded-lg hover:bg-white/5" title="Toggle Status" data-id="<?php echo $u['id']; ?>" data-status="<?php echo $u['status']; ?>">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="md:hidden space-y-3" id="user-cards">
                <?php if (empty($users)): ?>
                <div class="text-center py-12 text-white/40">
                    <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    No users found
                </div>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <div class="user-card bg-white/5 rounded-xl p-4 border border-white/5 transition" data-id="<?php echo $u['id']; ?>" data-username="<?php echo htmlspecialchars(strtolower($u['username'])); ?>" data-email="<?php echo htmlspecialchars(strtolower($u['email'])); ?>" data-role="<?php echo $u['role']; ?>" data-status="<?php echo $u['status']; ?>" data-created="<?php echo $u['created_at']; ?>">
                        <div class="flex items-start justify-between mb-3">
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-white user-card-username"><?php echo htmlspecialchars($u['username']); ?></div>
                                <div class="text-sm text-white/50 user-card-email"><?php echo htmlspecialchars($u['email']); ?></div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="role-badge text-xs px-2 py-0.5 rounded-full border <?php echo $u['role'] === 'admin' ? 'bg-accent-purple/20 text-accent-purple border-accent-purple/30' : ($u['role'] === 'manager' ? 'bg-accent-cyan/20 text-accent-cyan border-accent-cyan/30' : ($u['role'] === 'reseller' ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-white/10 text-white/60 border-white/10')); ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                                <span class="status-badge <?php echo $u['status'] === 'active' ? 'status-online' : 'bg-red-500/20 text-red-400 border-red-500/30'; ?> text-xs user-card-status">
                                    <?php echo ucfirst($u['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-white/40 user-card-created">
                                <?php echo date('Y-m-d', strtotime($u['created_at'])); ?>
                            </span>
                            <div class="flex gap-2">
                                <button class="btn-user-edit text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit" data-user='<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>'>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button class="btn-user-toggle text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Toggle Status" data-id="<?php echo $u['id']; ?>" data-status="<?php echo $u['status']; ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-white/10">
                <div class="flex items-center gap-4">
                    <div class="text-sm text-white/40">
                        Showing <span id="user-showing-start">0</span>-<span id="user-showing-end">0</span> of <span id="user-total-count"><?php echo count($users); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-white/40">Per page</span>
                        <?php renderCustomSelect('user-per-page', [
                            '10' => '10',
                            '20' => '20',
                            '50' => '50',
                            '100' => '100'
                        ], '10', 'wk-select--fit'); ?>
                    </div>
                </div>
                <?php renderPagination('user'); ?>
            </div>
        </div>
    </div>

    <!-- Licenses Tab -->
    <div id="licenses-tab" class="tab-panel active">
        <?php if (!$canViewLicenses): ?>
        <div class="glass-card p-6 rounded-xl mb-6 text-center">
            <p class="text-white/60">Please sign in with a Manager account to view your licenses.</p>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-6">
            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Total Licenses</span>
                </div>
                <div class="text-3xl font-bold text-white" id="license-total">0</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-online/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-online" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Active</span>
                </div>
                <div class="text-3xl font-bold text-white" id="license-active">0</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-updating/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-updating" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Unused</span>
                </div>
                <div class="text-3xl font-bold text-white" id="license-unused">0</div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Expired / Disabled</span>
                </div>
                <div class="text-3xl font-bold text-white" id="license-expired">0</div>
            </div>
        </div>

        <!-- License List -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-lg font-semibold text-white">License Keys (<span id="license-count">0</span>)</h3>
                <div class="flex flex-col sm:flex-row flex-wrap gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-56">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" id="license-search" class="app-input w-full pl-9 pr-4 py-2" placeholder="Search by key...">
                    </div>
                    <?php
                    $licenseProductOptions = ['' => 'All Products'];
                    foreach ($products as $p) {
                        $licenseProductOptions[$p['id']] = $p['name'];
                    }
                    renderCustomSelect('license-product-filter', $licenseProductOptions, '', 'w-full sm:w-40');
                    renderCustomSelect('license-status-filter', [
                        '' => 'All Status',
                        'unused' => 'Unused',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'disabled' => 'Disabled'
                    ], '', 'w-full sm:w-36');
                    ?>
                    <button type="button" id="license-delete-selected" class="hidden items-center gap-1 px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap bg-red-500/20 text-red-400 border border-red-500/30 hover:bg-red-500/30 transition" title="Delete selected licenses">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Selected (<span id="license-selected-count">0</span>)
                    </button>
                    <?php renderQuotaActions($vis, ['grant_label' => 'Assign to Reseller']); ?>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <colgroup>
                        <col class="w-[4%]">
                        <col class="w-[20%]">
                        <col class="w-[9%]">
                        <col class="w-[9%]">
                        <col class="w-[9%]">
                        <col class="w-[13%]">
                        <col class="w-[12%]">
                        <col class="w-[12%]">
                        <col class="w-[12%]">
                    </colgroup>
                    <thead>
                        <tr class="text-white/50 border-b border-white/10">
                            <th class="py-3 px-4 w-10">
                                <input type="checkbox" id="license-select-all" class="license-checkbox" title="Select all on this page" aria-label="Select all licenses on this page">
                            </th>
                            <th class="text-left py-3 px-4">License Key</th>
                            <th class="text-left py-3 px-4">Product</th>
                            <th class="text-left py-3 px-4">Duration</th>
                            <th class="text-left py-3 px-4">Assigned To</th>
                            <th class="text-left py-3 px-4">Status</th>
                            <th class="text-left py-3 px-4">Created</th>
                            <th class="text-left py-3 px-4">Expires</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="license-list">
                        <?php if (empty($licenses)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-12 text-white/40">
                                <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                No licenses yet. Claim keys from your quota to get started.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($licenses as $lic): ?>
                            <tr class="border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo $lic['id']; ?>" data-product-id="<?php echo $lic['product_id']; ?>">
                                <td class="py-3 px-4">
                                    <input type="checkbox" class="license-row-check license-checkbox" data-id="<?php echo $lic['id']; ?>" aria-label="Select license">
                                </td>
                                <td class="py-3 px-4"><span class="block font-mono text-sm text-white/80 truncate"><?php echo htmlspecialchars($lic['license_key']); ?></span></td>
                                <td class="py-3 px-4 text-white/60"><span class="block truncate"><?php echo htmlspecialchars($lic['product_name'] ?? 'N/A'); ?></span></td>
                                <td class="py-3 px-4 text-white/60">
                                    <?php
                                    $days = (int)$lic['duration_days'];
                                    if ($days === 1) echo '1 Day';
                                    elseif ($days === 7) echo '7 Days';
                                    elseif ($days === 30) echo '30 Days';
                                    elseif ($days === 90) echo '90 Days';
                                    elseif ($days === 365) echo '1 Year';
                                    elseif ($days >= 9999) echo 'Lifetime';
                                    else echo $days . ' Days';
                                    ?>
                                </td>
                                <td class="py-3 px-4 text-white/60">
                                    <?php if ($lic['user_id']): ?>
                                        <?php echo htmlspecialchars($lic['user_name'] ?? 'Unknown'); ?>
                                    <?php else: ?>
                                        <span class="text-accent-purple">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="status-badge <?php echo $lic['status'] === 'active' ? 'status-online' : ($lic['status'] === 'unused' ? 'status-updating' : ($lic['status'] === 'expired' || $lic['status'] === 'disabled' ? 'bg-red-500/20 text-red-400 border-red-500/30' : '')); ?> text-xs">
                                        <?php echo ucfirst($lic['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-white/40 text-xs"><?php echo date('Y-m-d', strtotime($lic['created_at'])); ?></td>
                                <td class="py-3 px-4 text-white/40 text-xs">
                                    <?php
                                    if ($lic['activated_at'] && $lic['duration_days'] < 9999) {
                                        $expiresAt = date('Y-m-d', strtotime($lic['activated_at'] . ' + ' . $lic['duration_days'] . ' days'));
                                        echo $expiresAt;
                                    } elseif ($lic['duration_days'] >= 9999) {
                                        echo '<span class="text-accent-cyan">Never</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="py-3 px-2 flex gap-1.5">
                                    <button class="btn-license-delete text-white/40 hover:text-red-500 transition p-1.5 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $lic['id']; ?>">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="md:hidden space-y-3" id="license-cards">
                <?php if (empty($licenses)): ?>
                <div class="text-center py-12 text-white/40">
                    <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    No licenses yet. Claim keys from your quota to get started.
                </div>
                <?php else: ?>
                    <?php foreach ($licenses as $lic): ?>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/5" data-id="<?php echo $lic['id']; ?>" data-product-id="<?php echo $lic['product_id']; ?>">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <input type="checkbox" class="license-row-check license-checkbox mt-1 shrink-0" data-id="<?php echo $lic['id']; ?>" aria-label="Select license">
                                <div class="flex-1 min-w-0">
                                    <div class="font-mono text-sm font-medium text-white truncate"><?php echo htmlspecialchars($lic['license_key']); ?></div>
                                    <div class="text-xs text-white/60 mt-1"><?php echo htmlspecialchars($lic['product_name'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <span class="status-badge <?php echo $lic['status'] === 'active' ? 'status-online' : ($lic['status'] === 'unused' ? 'status-updating' : ($lic['status'] === 'expired' || $lic['status'] === 'disabled' ? 'bg-red-500/20 text-red-400 border-red-500/30' : '')); ?> text-xs shrink-0">
                                <?php echo ucfirst($lic['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-white/40">
                            <div class="flex gap-3">
                                <?php
                                $days = (int)$lic['duration_days'];
                                if ($days === 1) $durationText = '1 Day';
                                elseif ($days === 7) $durationText = '7 Days';
                                elseif ($days === 30) $durationText = '30 Days';
                                elseif ($days === 90) $durationText = '90 Days';
                                elseif ($days === 365) $durationText = '1 Year';
                                elseif ($days >= 9999) $durationText = 'Lifetime';
                                else $durationText = $days . ' Days';
                                ?>
                                <span><?php echo $durationText; ?></span>
                                <span>
                                    <?php if ($lic['user_id']): ?>
                                        <?php echo htmlspecialchars($lic['user_name'] ?? 'Unknown'); ?>
                                    <?php else: ?>
                                        <span class="text-accent-purple">Admin</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <button class="btn-license-delete text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $lic['id']; ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-white/10">
                <div class="flex items-center gap-4">
                    <div class="text-sm text-white/40">
                        Showing <span id="license-showing-start">0</span>-<span id="license-showing-end">0</span> of <span id="license-total-count">0</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-white/40">Per page</span>
                        <?php renderCustomSelect('license-per-page', [
                            '10' => '10',
                            '20' => '20',
                            '50' => '50',
                            '100' => '100'
                        ], '10', 'wk-select--fit'); ?>
                    </div>
                </div>
                <?php renderPagination('license'); ?>
            </div>
        </div>
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
<div id="user-edit-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="glass-card p-6 rounded-xl max-w-md w-full mx-4 transform scale-95 opacity-0 transition-all duration-200" id="user-edit-dialog">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white" id="user-edit-title">Edit User</h3>
            <button class="text-white/40 hover:text-white transition p-1" id="user-edit-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="user-edit-form" class="space-y-4">
            <input type="hidden" id="ue-id">
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Username</label>
                <input type="text" id="ue-username" class="app-input w-full px-4 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Email</label>
                <input type="email" id="ue-email" class="app-input w-full px-4 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Status</label>
                <?php renderCustomSelect('ue-status', [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'banned' => 'Banned'
                ], 'active', 'w-full'); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Role</label>
                <?php renderCustomSelect('ue-role', [
                    'user' => 'User',
                    'reseller' => 'Reseller',
                    'manager' => 'Manager',
                    'admin' => 'Admin'
                ], 'user', 'w-full'); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">New Password <span class="text-white/40">(leave blank to keep current)</span></label>
                <input type="password" id="ue-password" class="app-input w-full px-4 py-2" placeholder="Enter new password...">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold flex-1">
                    Save Changes
                </button>
                <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition flex-1" id="user-edit-cancel">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
