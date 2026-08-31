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
$isAdmin = $currentUser && $currentUser['role'] === 'admin';

// 配额数据（仅管理员展示配额管理）
$allocations = $isAdmin ? $licenseModel->getAllocations() : [];

// 经理用户列表（Grant Quota 弹窗用）
$managerUsers = array_filter($users, fn($u) => $u['role'] === 'manager');
// Reseller 用户列表（Grant Quota 弹窗用）
$resellerUsers = array_filter($users, fn($u) => $u['role'] === 'reseller');

/**
 * 时长显示标签
 */
function licenseDurationLabel(int $days): string {
    if ($days === 1) return '1 Day';
    if ($days === 7) return '7 Days';
    if ($days === 30) return '30 Days';
    if ($days === 90) return '90 Days';
    if ($days === 365) return '1 Year';
    if ($days >= 9999) return 'Lifetime';
    return $days . ' Days';
}

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

/**
 * Render a custom select dropdown (wk-select component)
 * 视觉与 .app-input 保持一致，结构支持键盘导航与选中态指示
 */
function renderCustomSelect(string $id, array $options, string $selected = '', string $widthClass = 'w-full', bool $required = false): void {
    $requiredAttr = $required ? ' required' : '';
    $currentLabel = $options[$selected] ?? (count($options) > 0 ? array_values($options)[0] : '');
    $currentValue = $selected;

    // 当未指定选中值或选中值不存在时，默认选中第一个选项
    if (!array_key_exists($selected, $options) && count($options) > 0) {
        $currentValue = array_key_first($options);
        $currentLabel = $options[$currentValue];
    }

    echo '<div class="wk-select ' . $widthClass . '" data-wk-select>';
    echo '    <select id="' . $id . '" class="wk-select__native"' . $requiredAttr . ' aria-hidden="true" tabindex="-1">';
    foreach ($options as $value => $label) {
        $sel = (string)$value === (string)$currentValue ? ' selected' : '';
        echo '        <option value="' . htmlspecialchars((string)$value) . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
    }
    echo '    </select>';
    echo '    <button type="button" class="wk-select__trigger" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="' . $id . '-label">';
    echo '        <span class="wk-select__value" id="' . $id . '-label">' . htmlspecialchars($currentLabel) . '</span>';
    echo '        <svg class="wk-select__chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
    echo '    </button>';
    echo '    <div class="wk-select__menu" role="listbox" aria-hidden="true">';
    echo '        <div class="wk-select__options">';
    foreach ($options as $value => $label) {
        $isSelected = (string)$value === (string)$currentValue;
        echo '        <button type="button" class="wk-select__option' . ($isSelected ? ' is-selected' : '') . '" role="option" aria-selected="' . ($isSelected ? 'true' : 'false') . '" data-value="' . htmlspecialchars((string)$value) . '">';
        echo '            <span class="wk-select__option-label">' . htmlspecialchars($label) . '</span>';
        echo '        </button>';
    }
    echo '        </div>';
    echo '        <div class="wk-select__pager">';
    echo '            <button type="button" class="wk-select__pager-btn wk-select__pager-prev" aria-label="Previous page">';
    echo '                <svg class="wk-select__pager-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>';
    echo '            </button>';
    echo '            <span class="wk-select__pager-info"></span>';
    echo '            <button type="button" class="wk-select__pager-btn wk-select__pager-next" aria-label="Next page">';
    echo '                <svg class="wk-select__pager-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>';
    echo '            </button>';
    echo '        </div>';
    echo '    </div>';
    echo '</div>';
}
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
<div class="manager-tabs mb-6 flex gap-2 border-b border-white/10">
    <button class="manager-tab active px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="products">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Products
    </button>
    <button class="manager-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="users">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        Users
    </button>
    <button class="manager-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
        </svg>
        License
    </button>
    <button class="manager-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="settings">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-lg font-semibold text-white">Product List (<span id="product-count"><?php echo count($products); ?></span>)</h3>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <?php renderCustomSelect('product-status-filter', [
                        '' => 'All Statuses',
                        'online' => 'Online',
                        'updating' => 'Updating',
                        'development' => 'Development'
                    ], '', 'w-full sm:w-40'); ?>
                    <button type="button" class="btn-primary px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap" onclick="openProductModal()">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Product
                    </button>
                </div>
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
                <table class="w-full text-sm">
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
                                <td class="py-3 px-4 text-white/60 user-cell-email"><?php echo htmlspecialchars($u['email']); ?></td>
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
                                <td class="py-3 px-4 flex gap-2">
                                    <button class="btn-user-edit text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit" data-user='<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>'>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn-user-toggle text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Toggle Status" data-id="<?php echo $u['id']; ?>" data-status="<?php echo $u['status']; ?>">
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
    <div id="licenses-tab" class="tab-panel">
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
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
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
                    <button type="button" class="btn-primary px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap" onclick="openLicenseModal()">
                        Add License
                    </button>
                    <?php if ($isAdmin): ?>
                    <button type="button" class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap bg-accent-cyan/20 text-accent-cyan border border-accent-cyan/30 hover:bg-accent-cyan/30 transition" id="grant-quota-btn">
                        Grant Quota
                    </button>
                    <?php endif; ?>
                    <button type="button" id="license-delete-selected" class="hidden items-center gap-1 px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap bg-red-500/20 text-red-400 border border-red-500/30 hover:bg-red-500/30 transition" title="Delete selected licenses">
                        Delete Selected (<span id="license-selected-count">0</span>)
                    </button>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-hidden">
                <table class="w-full text-sm table-fixed">
                    <colgroup>
                        <col class="w-[4%]">
                        <col class="w-[24%]">
                        <col class="w-[9%]">
                        <col class="w-[9%]">
                        <col class="w-[9%]">
                        <col class="w-[14%]">
                        <col class="w-[12%]">
                        <col class="w-[12%]">
                        <col class="w-[7%]">
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
                                No licenses yet. Click "Add License" to create one.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php
                            // 预先构建用户角色映射表
                            $userRoleMap = [];
                            foreach ($users as $u) {
                                $userRoleMap[$u['id']] = $u['role'];
                            }
                            ?>
                            <?php foreach ($licenses as $lic): ?>
                            <?php $userRole = !empty($lic['user_id']) ? ($userRoleMap[$lic['user_id']] ?? null) : null; ?>
                            <tr class="border-b border-white/5 hover:bg-white/5 transition"
                                data-id="<?php echo $lic['id']; ?>"
                                data-product-id="<?php echo $lic['product_id']; ?>"
                                <?php if ($userRole === 'manager'): ?>
                                data-manager-id="<?php echo $lic['user_id']; ?>"
                                <?php elseif ($userRole === 'reseller'): ?>
                                data-reseller-id="<?php echo $lic['user_id']; ?>"
                                <?php endif; ?>>
                                <td class="py-3 px-4">
                                    <input type="checkbox" class="license-row-check license-checkbox" data-id="<?php echo $lic['id']; ?>" aria-label="Select license">
                                </td>
                                <td class="py-3 px-4"><span class="block font-mono text-sm text-white/80 truncate" title="<?php echo htmlspecialchars($lic['license_key']); ?>"><?php echo htmlspecialchars($lic['license_key']); ?></span></td>
                                <td class="py-3 px-4 text-white/60"><?php echo htmlspecialchars($lic['product_name'] ?? 'N/A'); ?></td>
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
                                <td class="py-3 px-4 flex gap-2">
                                    <?php if ($lic['user_id'] && $lic['status'] === 'unused'): ?>
                                    <button class="btn-license-recycle text-white/40 hover:text-accent-cyan transition p-2 rounded-lg hover:bg-white/5" title="Recycle to inventory" data-id="<?php echo $lic['id']; ?>">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn-license-delete text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $lic['id']; ?>">
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
                    No licenses yet. Click "Add License" to create one.
                </div>
                <?php else: ?>
                    <?php
                    // 预先构建用户角色映射表
                    $userRoleMap = [];
                    foreach ($users as $u) {
                        $userRoleMap[$u['id']] = $u['role'];
                    }
                    ?>
                    <?php foreach ($licenses as $lic): ?>
                    <?php $userRole = !empty($lic['user_id']) ? ($userRoleMap[$lic['user_id']] ?? null) : null; ?>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/5"
                         data-id="<?php echo $lic['id']; ?>"
                         data-product-id="<?php echo $lic['product_id']; ?>"
                         <?php if ($userRole === 'manager'): ?>
                         data-manager-id="<?php echo $lic['user_id']; ?>"
                         <?php elseif ($userRole === 'reseller'): ?>
                         data-reseller-id="<?php echo $lic['user_id']; ?>"
                         <?php endif; ?>>
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
                            <div class="flex items-center shrink-0">
                                <?php if ($lic['user_id'] && $lic['status'] === 'unused'): ?>
                                <button class="btn-license-recycle text-white/40 hover:text-accent-cyan transition p-2 rounded-lg hover:bg-white/5" title="Recycle to inventory" data-id="<?php echo $lic['id']; ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                                <?php endif; ?>
                                <button class="btn-license-delete text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $lic['id']; ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6 pt-6 border-t border-white/10">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 min-w-0">
                    <div class="text-sm text-white/40">
                        Showing <span id="license-showing-start">0</span>-<span id="license-showing-end">0</span> of <span id="license-total-count">0</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-white/40">Per page</span>
                        <?php renderCustomSelect('license-per-page', [
                            '10' => '10',
                            '20' => '20',
                            '50' => '50',
                            '100' => '100'
                        ], '10', 'wk-select--fit'); ?>
                        <?php
                        // 角色筛选（联动下方用户筛选）
                        renderCustomSelect('license-role-filter', [
                            '' => 'All Roles',
                            'manager' => 'Manager',
                            'reseller' => 'Reseller'
                        ], '', 'w-full sm:w-36');
                        // 用户筛选（选项由 JS 根据 data-users-by-role 联动重建）
                        renderCustomSelect('license-user-filter', ['' => 'All Users'], '', 'w-full sm:w-40');
                        ?>
                    </div>
                </div>
                <?php renderPagination('license'); ?>
            </div>
        </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
            <button type="button" id="faq-add" class="btn-primary px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap" onclick="openFaqModal()">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
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
<div id="grant-quota-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="glass-card p-6 rounded-xl max-w-lg w-full mx-4 transform scale-95 opacity-0 transition-all duration-200" id="grant-quota-dialog">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Grant Quota</h3>
            <button class="text-white/40 hover:text-white transition p-1" id="grant-quota-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="grant-quota-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Role *</label>
                <?php
                renderCustomSelect('gq-role', [
                    '' => '-- Select Role --',
                    'manager' => 'Manager',
                    'reseller' => 'Reseller'
                ], '', 'w-full', true);
                ?>
            </div>
            <div id="gq-user-container" class="hidden">
                <label class="block text-sm font-medium text-white/70 mb-2">User *</label>
                <div id="gq-user-manager-wrapper">
                    <?php
                    $gqManagerOptions = ['' => '-- Select Manager --'];
                    foreach ($managerUsers as $mu) {
                        $gqManagerOptions[$mu['id']] = $mu['username'];
                    }
                    renderCustomSelect('gq-manager', $gqManagerOptions, '', 'w-full', true);
                    ?>
                </div>
                <div id="gq-user-reseller-wrapper" class="hidden">
                    <?php
                    $gqResellerOptions = ['' => '-- Select Reseller --'];
                    foreach ($resellerUsers as $ru) {
                        $gqResellerOptions[$ru['id']] = $ru['username'];
                    }
                    renderCustomSelect('gq-reseller', $gqResellerOptions, '', 'w-full', true);
                    ?>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Product *</label>
                <?php
                $gqProductOptions = ['' => '-- Select Product --'];
                foreach ($products as $p) {
                    $gqProductOptions[$p['id']] = $p['name'];
                }
                renderCustomSelect('gq-product', $gqProductOptions, '', 'w-full', true);
                ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Duration *</label>
                <?php renderCustomSelect('gq-duration', [
                    '' => '-- Select Time --',
                    '1' => '1 Day',
                    '7' => '7 Days',
                    '30' => '30 Days',
                    '90' => '90 Days',
                    '365' => '1 Year',
                    '9999' => 'Lifetime'
                ], '', 'w-full', true); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Quantity *</label>
                <div class="wk-input-number w-full">
                    <button type="button" class="wk-input-number__btn wk-input-number__btn--minus" aria-label="Decrease">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <input type="number" id="gq-quantity" class="wk-input-number__input" min="1" placeholder="How many keys can this manager claim?" required>
                    <button type="button" class="wk-input-number__btn wk-input-number__btn--plus" aria-label="Increase">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-white/40 mt-1" id="gq-inventory-hint"></p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold flex-1">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Grant Quota
                </button>
                <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition flex-1" id="grant-quota-cancel">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

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
                renderCustomSelect('lf-product', $lfProductOptions, '', 'w-full', true);
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
                ], '', 'w-full', true); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Assign Role *</label>
                <?php renderCustomSelect('lf-role', [
                    'admin' => 'Admin',
                    'manager' => 'Manager',
                    'reseller' => 'Reseller'
                ], 'admin', 'w-full', true); ?>
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


