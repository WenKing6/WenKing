<?php
/**
 * Admin Page - Product Management Control Panel
 * Consistent style with Manager page
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
$licenses = $licenseModel->getAll();

// Settings tab 数据（站点身份 + FAQ 管理）
require_once __DIR__ . '/../../models/SiteSetting.php';
require_once __DIR__ . '/../../models/FaqItem.php';
$siteName = SiteSetting::getName();
$siteIcon = (new SiteSetting())->get('site_icon');
$faqItems = (new FaqItem())->getAll();
$currentIcon = $siteIcon ? SITE_URL . $siteIcon : SITE_URL . '/assets/images/gta-v-logo-transparent-free-png.webp';

// 当前用户识别：Session + 数据库角色校验（防篡改）
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUser = $sessionUserId > 0 ? $userModel->findById($sessionUserId) : null;
$currentRole = $currentUser ? $currentUser['role'] : '';
$isAdmin = $currentUser && $currentUser['role'] === 'admin';

// 配额数据（仅管理员展示配额管理）
$allocations = $isAdmin ? $licenseModel->getAllocations() : [];

// 经理用户列表（Grant Quota 弹窗用）
$managerUsers = array_filter($users, fn($u) => $u['role'] === 'manager');
// Reseller 用户列表（Grant Quota 弹窗用）
$resellerUsers = array_filter($users, fn($u) => $u['role'] === 'reseller');

// Group users by role for license assignment
$usersByRole = [];
foreach ($users as $u) {
    $role = $u['role'];
    if (!isset($usersByRole[$role])) {
        $usersByRole[$role] = [];
    }
    $usersByRole[$role][] = [
        'id' => $u['id'],
        'username' => $u['username'],
        'email' => $u['email']
    ];
}

// 页面可见性配置（权限矩阵唯一来源：LicenseModule）
$vis = LicenseModule::getUiVisibility($currentRole);
?>
<div class="app-page-header mb-8" data-users-by-role='<?php echo htmlspecialchars(json_encode($usersByRole), ENT_QUOTES); ?>'>
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">Admin Panel</span>
    </h1>
    <p class="text-white/60">Manage products, users, and system settings</p>
    <?php if ($currentUser): ?>
    <p class="text-xs text-white/40 mt-2">Signed in as <span class="text-accent-cyan"><?php echo htmlspecialchars($currentUser['username']); ?></span> (<?php echo ucfirst($currentUser['role']); ?>)</p>
    <?php endif; ?>
</div>

<!-- Tab Navigation -->
<div class="manager-tabs mb-6 grid grid-cols-2 md:grid-cols-4 gap-2 border-b border-white/10">
    <button class="manager-tab active flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="products">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Products
    </button>
    <button class="manager-tab flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="users">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        Users
    </button>
    <button class="manager-tab flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
        </svg>
        License
    </button>
    <button class="manager-tab flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="settings">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        Settings
    </button>
</div>

<!-- Tab Content Area -->
<div class="manager-tab-content">
    <!-- Products Tab -->
    <div id="products-tab" class="tab-panel active">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-6">
            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Total Products</span>
                </div>
                <div class="text-3xl font-bold text-white"><?php echo count($products); ?></div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-online/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-online" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Online</span>
                </div>
                <div class="text-3xl font-bold text-white"><?php echo count(array_filter($products, fn($p) => $p['status'] === 'online')); ?></div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-updating/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-updating" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Updating</span>
                </div>
                <div class="text-3xl font-bold text-white"><?php echo count(array_filter($products, fn($p) => $p['status'] === 'updating')); ?></div>
            </div>

            <div class="glass-card p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-status-dev/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-status-dev" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-white/60">Development</span>
                </div>
                <div class="text-3xl font-bold text-white"><?php echo count(array_filter($products, fn($p) => $p['status'] === 'development')); ?></div>
            </div>
        </div>

        <!-- Product List -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
                <h3 class="text-lg font-semibold text-white shrink-0">Product List (<span id="product-count"><?php echo count($products); ?></span>)</h3>
                <?php renderCustomSelect('product-status-filter', [
                    '' => 'All Statuses',
                    'online' => 'Online',
                    'updating' => 'Updating',
                    'development' => 'Development'
                ], '', 'w-full sm:w-36 md:shrink-0 md:basis-auto'); ?>
                <button type="button" class="btn-primary whitespace-nowrap md:ml-auto" onclick="openProductModal()">
                    Add Product
                </button>
            </div>

            <!-- Desktop Table Layout -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-white/50 border-b border-white/10">
                            <th class="text-left py-3 px-4">Name</th>
                            <th class="text-left py-3 px-4">Status</th>
                            <th class="text-left py-3 px-4">Sort</th>
                            <th class="text-left py-3 px-4">Visible</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="product-list">
                        <?php foreach ($products as $p): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo $p['id']; ?>" data-status="<?php echo $p['status']; ?>">
                            <td class="py-3 px-4 font-medium text-white"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td class="py-3 px-4">
                                <span class="status-badge status-<?php echo $p['status']; ?> text-xs"><?php echo ucfirst($p['status']); ?></span>
                            </td>
                            <td class="py-3 px-4 text-white/40"><?php echo $p['sort_order']; ?></td>
                            <td class="py-3 px-4"><span class="product-visible-indicator"><span class="pv-icon <?php echo $p['is_visible'] ? 'text-status-online' : 'text-white/30'; ?>"><?php echo $p['is_visible'] ? '✅' : '❌'; ?></span></span></td>
                            <td class="py-3 px-4 flex gap-2">
                                <button class="btn-admin-visibility text-white/40 hover:text-accent-cyan transition p-2 rounded-lg hover:bg-white/5 <?php echo $p['is_visible'] ? '' : 'opacity-40'; ?>" title="<?php echo $p['is_visible'] ? 'Hide from frontend' : 'Show on frontend'; ?>" data-id="<?php echo $p['id']; ?>" data-visible="<?php echo $p['is_visible'] ? 1 : 0; ?>">
                                    <?php if ($p['is_visible']): ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <?php else: ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    <?php endif; ?>
                                </button>
                                <button class="btn-admin-edit text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit" data-product='<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>'>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button class="btn-admin-delete text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $p['id']; ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout -->
            <div id="product-list-mobile" class="md:hidden space-y-3">
                <?php foreach ($products as $p): ?>
                <div class="product-card relative p-4 rounded-lg bg-white/5 hover:bg-white/10 transition" data-id="<?php echo $p['id']; ?>" data-status="<?php echo $p['status']; ?>">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($p['name']); ?></div>
                        </div>
                        <span class="status-badge status-<?php echo $p['status']; ?> text-xs shrink-0"><?php echo ucfirst($p['status']); ?></span>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                        <div class="flex items-center gap-4 text-xs text-white/40">
                            <span>Sort: <?php echo $p['sort_order']; ?></span>
                            <span class="product-visible-indicator"><span class="pv-icon <?php echo $p['is_visible'] ? 'text-status-online' : 'text-white/30'; ?>"><?php echo $p['is_visible'] ? '✅' : '❌'; ?></span> <span class="pv-label"><?php echo $p['is_visible'] ? 'Visible' : 'Hidden'; ?></span></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button class="btn-admin-visibility text-white/40 hover:text-accent-cyan transition p-2 rounded-lg hover:bg-white/5 <?php echo $p['is_visible'] ? '' : 'opacity-40'; ?>" title="<?php echo $p['is_visible'] ? 'Hide from frontend' : 'Show on frontend'; ?>" data-id="<?php echo $p['id']; ?>" data-visible="<?php echo $p['is_visible'] ? 1 : 0; ?>">
                                <?php if ($p['is_visible']): ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?php else: ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                <?php endif; ?>
                            </button>
                            <button class="btn-admin-edit text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit" data-product='<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>'>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="btn-admin-delete text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $p['id']; ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-white/10">
                <div class="flex items-center gap-4">
                    <div class="text-sm text-white/40">
                        Showing <span id="product-showing-start">0</span>-<span id="product-showing-end">0</span> of <span id="product-total-count"><?php echo count($products); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-white/40">Per page</span>
                        <?php renderCustomSelect('product-per-page', [
                            '10' => '10',
                            '20' => '20',
                            '50' => '50',
                            '100' => '100'
                        ], '10', 'wk-select--fit'); ?>
                    </div>
                </div>
                <?php renderPagination('product'); ?>
            </div>
        </div>
    </div>

    <!-- Users Tab -->
    <div id="users-tab" class="tab-panel">
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
        <?php
        renderLicenseSection($licenses, $products, $vis, [
            'is_admin' => true,
            'users'    => $users,
        ]);
        ?>
    </div>

</div>

<!-- Settings Tab -->
<div id="settings-tab" class="tab-panel">
    <!-- General Settings -->
    <div class="glass-card p-6 rounded-xl mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">General Settings</h3>
                    <p class="text-sm text-white/40">Website identity shown across the entire site</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Site Icon Upload -->
            <div class="rounded-xl bg-white/5 border border-white/5 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-lg bg-accent-cyan/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-white">Site Icon</h4>
                        <p class="text-xs text-white/40">PNG, JPG or SVG &middot; Max 2MB</p>
                    </div>
                </div>

                <div id="site-icon-zone" class="icon-upload-zone">
                    <div class="icon-upload-preview">
                        <img id="site-icon-preview" src="<?php echo $currentIcon; ?>" alt="Site Icon" onerror="this.style.opacity='0.15'">
                    </div>
                    <div class="icon-upload-controls">
                        <input type="file" id="site-icon-file" accept=".png,.jpg,.jpeg,.svg,image/png,image/jpeg,image/svg+xml" hidden>
                        <button type="button" id="site-icon-pick" class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/80 text-sm font-semibold transition">
                            Choose Image
                        </button>
                        <button type="button" id="site-icon-upload" class="px-4 py-2 rounded-lg btn-primary text-sm font-semibold transition" disabled>
                            Upload
                        </button>
                    </div>
                    <p id="site-icon-hint" class="icon-upload-hint">Drag &amp; drop an image here, or click "Choose Image"</p>
                </div>
            </div>

            <!-- Site Name Edit -->
            <div class="rounded-xl bg-white/5 border border-white/5 p-6" id="site-name-card">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-white">Site Name</h4>
                        <p class="text-xs text-white/40">Up to 50 characters &middot; special characters are filtered</p>
                    </div>
                </div>

                <!-- 显示模式 -->
                <div id="site-name-view" class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div id="site-name-initial" class="w-12 h-12 rounded-xl bg-accent-purple/20 flex items-center justify-center font-display font-bold text-xl text-accent-purple shrink-0"><?php echo htmlspecialchars(strtoupper(mb_substr($siteName, 0, 1))); ?></div>
                        <div class="min-w-0">
                            <div id="site-name-value" class="text-lg font-semibold text-white truncate"><?php echo htmlspecialchars($siteName); ?></div>
                            <div class="text-xs text-white/40">Current site name</div>
                        </div>
                    </div>
                    <button type="button" id="site-name-edit-btn" class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/80 text-sm font-semibold transition whitespace-nowrap">
                        Edit
                    </button>
                </div>

                <!-- 编辑模式 -->
                <form id="site-name-form" class="space-y-3" novalidate>
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2">Site Name</label>
                        <div class="relative">
                            <input type="text" id="site-name-input" class="app-input w-full pr-14" value="<?php echo htmlspecialchars($siteName); ?>" maxlength="50" placeholder="Enter site name...">
                            <span id="site-name-count" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-white/40"><?php echo mb_strlen($siteName); ?>/50</span>
                        </div>
                        <p id="site-name-error" class="text-xs text-red-400 mt-1 hidden"></p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary px-5 py-2 rounded-lg font-semibold text-sm">Save</button>
                        <button type="button" id="site-name-cancel-btn" class="px-5 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FAQ Manager -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-white">FAQ Manager (<span id="faq-count"><?php echo count($faqItems); ?></span>)</h3>
                <p class="text-sm text-white/40 mt-1">Manage the collapsible Q&amp;A items shown on the front page</p>
            </div>
            <button type="button" id="faq-add" class="btn-primary whitespace-nowrap" onclick="openFaqModal()">
                Add FAQ
            </button>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-white/50 border-b border-white/10">
                        <th class="text-left py-3 px-4">Question</th>
                        <th class="text-left py-3 px-4 w-20">Sort</th>
                        <th class="text-left py-3 px-4 w-28">Visible</th>
                        <th class="text-left py-3 px-4 w-36">Actions</th>
                    </tr>
                </thead>
                <tbody id="faq-list">
                    <?php if (empty($faqItems)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-12 text-white/40">
                            <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            No FAQ items yet. Click "Add FAQ" to create one.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($faqItems as $item): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo (int)$item['id']; ?>">
                            <td class="py-3 px-4 font-medium text-white faq-cell-question"><?php echo htmlspecialchars($item['question']); ?></td>
                            <td class="py-3 px-4 text-white/40 faq-cell-sort"><?php echo (int)$item['sort_order']; ?></td>
                            <td class="py-3 px-4">
                                <span class="status-badge <?php echo $item['is_visible'] ? 'status-online' : 'bg-white/10 text-white/60 border-white/10'; ?> text-xs faq-cell-visible"><?php echo $item['is_visible'] ? 'Visible' : 'Hidden'; ?></span>
                            </td>
                            <td class="py-3 px-4 flex gap-2">
                                <button class="btn-faq-toggle text-white/40 hover:text-accent-cyan transition p-2 rounded-lg hover:bg-white/5 <?php echo $item['is_visible'] ? '' : 'opacity-40'; ?>" title="Toggle visibility" data-id="<?php echo (int)$item['id']; ?>" data-visible="<?php echo (int)$item['is_visible']; ?>">
                                    <?php if ($item['is_visible']): ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <?php else: ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    <?php endif; ?>
                                </button>
                                <button class="btn-faq-edit text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit" data-faq='<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES); ?>'>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button class="btn-faq-delete text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo (int)$item['id']; ?>">
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

        <!-- Mobile Cards -->
        <div id="faq-list-mobile" class="md:hidden space-y-3">
            <?php if (empty($faqItems)): ?>
            <div class="text-center py-12 text-white/40">
                <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                No FAQ items yet. Click "Add FAQ" to create one.
            </div>
            <?php else: ?>
                <?php foreach ($faqItems as $item): ?>
                <div class="bg-white/5 rounded-xl p-4 border border-white/5" data-id="<?php echo (int)$item['id']; ?>">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-white faq-cell-question"><?php echo htmlspecialchars($item['question']); ?></div>
                            <div class="text-xs text-white/40 mt-1">Sort: <span class="faq-cell-sort"><?php echo (int)$item['sort_order']; ?></span></div>
                        </div>
                        <span class="status-badge <?php echo $item['is_visible'] ? 'status-online' : 'bg-white/10 text-white/60 border-white/10'; ?> text-xs faq-cell-visible shrink-0"><?php echo $item['is_visible'] ? 'Visible' : 'Hidden'; ?></span>
                    </div>
                    <div class="flex items-center justify-end gap-1 border-t border-white/5 pt-3">
                        <button class="btn-faq-toggle text-white/40 hover:text-accent-cyan transition p-2 rounded-lg hover:bg-white/5 <?php echo $item['is_visible'] ? '' : 'opacity-40'; ?>" title="Toggle visibility" data-id="<?php echo (int)$item['id']; ?>" data-visible="<?php echo (int)$item['is_visible']; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        <button class="btn-faq-edit text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit" data-faq='<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES); ?>'>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button class="btn-faq-delete text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo (int)$item['id']; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Grant Quota Modal -->
<!-- Grant Quota Modal（LicenseModule 共享渲染，交互在 license-module.js） -->
<?php renderGrantQuotaModal([
    'id_prefix'        => 'gq',
    'title'            => 'Grant Quota',
    'show_role_select' => true,
    'roles'            => ['' => '-- Select Role --', 'manager' => 'Manager', 'reseller' => 'Reseller'],
    'managers'         => array_values($managerUsers),
    'resellers'        => array_values($resellerUsers),
    'products'         => $products,
    'from_own_quota'   => false,
]); ?>

<!-- License Edit Modal -->
<div id="license-edit-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="glass-card p-6 rounded-xl max-w-lg w-full mx-4 transform scale-95 opacity-0 transition-all duration-200 max-h-[90vh] overflow-y-auto" id="license-edit-dialog">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white" id="license-edit-title">Add License</h3>
            <button class="text-white/40 hover:text-white transition p-1" id="license-edit-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="license-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Product *</label>
                <?php
                $lfProductOptions = ['' => '-- Select Product --'];
                foreach ($products as $p) {
                    $lfProductOptions[$p['id']] = $p['name'];
                }
                renderCustomSelect('lf-product', $lfProductOptions, '', 'w-full');
                ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Duration *</label>
                <?php renderCustomSelect('lf-duration', [
                    '' => '-- Select Time --',
                    '1' => '1 Day',
                    '7' => '7 Days',
                    '30' => '30 Days',
                    '90' => '90 Days',
                    '365' => '1 Year',
                    '9999' => 'Lifetime'
                ], '', 'w-full'); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Assign Role *</label>
                <?php renderCustomSelect('lf-role', [
                    'admin' => 'Admin',
                    'manager' => 'Manager',
                    'reseller' => 'Reseller'
                ], 'admin', 'w-full'); ?>
            </div>
            <div id="lf-user-container" style="display: none;">
                <label class="block text-sm font-medium text-white/70 mb-2">Assign User *</label>
                <?php renderCustomSelect('lf-user', ['' => '-- Select User --'], '', 'w-full'); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">License Keys *</label>
                <textarea id="lf-license-keys" class="app-input w-full px-4 py-2 font-mono text-sm" rows="6" placeholder="Enter one key per line, e.g.&#10;ABCD-1234-EFGH-5678&#10;IJKL-9012-MNOP-3456&#10;QRST-7890-UVWX-1234" required></textarea>
                <p class="text-xs text-white/40 mt-1">One key per line. You can paste multiple keys at once.</p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold flex-1">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add License
                </button>
                <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition flex-1" id="license-cancel-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- User Edit Modal (outside tab-panel to avoid display:none) -->
<?php renderUserEditModal(['show_role' => true]); ?>

<!-- Product Edit Modal (outside tab-panel to avoid display:none) -->
<div id="product-edit-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="glass-card p-6 rounded-xl max-w-2xl w-full mx-4 transform scale-95 opacity-0 transition-all duration-200 max-h-[90vh] overflow-y-auto" id="product-edit-dialog">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white" id="product-edit-title">Add New Product</h3>
            <button class="text-white/40 hover:text-white transition p-1" id="product-edit-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="product-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" id="edit-id" value="">
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Product Name *</label>
                <input type="text" id="f-name" class="app-input w-full px-4 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Tagline</label>
                <input type="text" id="f-tagline" class="app-input w-full px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Status</label>
                <?php renderCustomSelect('f-status', [
                    'online' => 'Online',
                    'updating' => 'Updating',
                    'development' => 'Development'
                ], 'online', 'w-full'); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Image Path</label>
                <input type="text" id="f-image" class="app-input w-full px-4 py-2" placeholder="/assets/images/hero-bg.jpg">
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Button Text</label>
                <input type="text" id="f-button-text" class="app-input w-full px-4 py-2" value="Now Buy">
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Button Link</label>
                <input type="text" id="f-button-link" class="app-input w-full px-4 py-2" placeholder="/partners.php">
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Sort Order</label>
                <input type="number" id="f-sort-order" class="app-input w-full px-4 py-2 number-input" value="0">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="f-is-visible" checked class="w-4 h-4 rounded">
                    <span class="text-sm text-white/60">Visible on Frontend</span>
                </label>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-white/70 mb-2">Features (separated by |)</label>
                <input type="text" id="f-features" class="app-input w-full px-4 py-2" placeholder="100+ Features|Daily Updates|Undetected">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-white/70 mb-2">Description</label>
                <textarea id="f-description" class="app-input w-full px-4 py-2" rows="2"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold flex-1" id="submit-btn">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Product
                </button>
                <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition flex-1" id="cancel-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- FAQ Edit Modal (outside tab-panel to avoid display:none) -->
<div id="faq-edit-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="glass-card p-6 rounded-xl max-w-lg w-full mx-4 transform scale-95 opacity-0 transition-all duration-200 max-h-[90vh] overflow-y-auto" id="faq-edit-dialog">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white" id="faq-edit-title">Add FAQ</h3>
            <button class="text-white/40 hover:text-white transition p-1" id="faq-edit-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="faq-form" class="space-y-4">
            <input type="hidden" id="fe-id" value="">
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Question *</label>
                <input type="text" id="fe-question" class="app-input w-full px-4 py-2" maxlength="255" placeholder="Enter the question..." required>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Answer *</label>
                <textarea id="fe-answer" class="app-input w-full px-4 py-2" rows="4" placeholder="Enter the answer..." required></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Sort Order</label>
                    <input type="number" id="fe-sort" class="app-input w-full px-4 py-2 number-input" value="0">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="fe-visible" checked class="w-4 h-4 rounded">
                        <span class="text-sm text-white/60">Visible on frontend</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold flex-1" id="faq-submit-btn">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add FAQ
                </button>
                <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition flex-1" id="faq-cancel-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


