<?php
/**
 * Admin Page - Product Management Control Panel
 * Consistent style with Manager page
 */
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/License.php';
$productModel = new Product();
$userModel = new User();
$licenseModel = new License();
$products = $productModel->getAll();
$users = $userModel->getAll();
$licenses = $licenseModel->getAll();

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
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Product List (<?php echo count($products); ?>)</h3>
                <button type="button" class="btn-primary px-4 py-2 rounded-lg font-semibold text-sm" onclick="openProductModal()">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Product
                </button>
            </div>

            <!-- Desktop Table Layout -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-white/50 border-b border-white/10">
                            <th class="text-left py-3 px-4">ID</th>
                            <th class="text-left py-3 px-4">Name</th>
                            <th class="text-left py-3 px-4">Tagline</th>
                            <th class="text-left py-3 px-4">Status</th>
                            <th class="text-left py-3 px-4">Sort</th>
                            <th class="text-left py-3 px-4">Visible</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="product-list">
                        <?php foreach ($products as $p): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo $p['id']; ?>">
                            <td class="py-3 px-4 text-white/40"><?php echo $p['id']; ?></td>
                            <td class="py-3 px-4 font-medium text-white"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td class="py-3 px-4 text-white/60 max-w-xs truncate"><?php echo htmlspecialchars($p['tagline']); ?></td>
                            <td class="py-3 px-4">
                                <span class="status-badge status-<?php echo $p['status']; ?> text-xs"><?php echo ucfirst($p['status']); ?></span>
                            </td>
                            <td class="py-3 px-4 text-white/40"><?php echo $p['sort_order']; ?></td>
                            <td class="py-3 px-4"><?php echo $p['is_visible'] ? '<span class="text-status-online">✅</span>' : '<span class="text-white/30">❌</span>'; ?></td>
                            <td class="py-3 px-4 flex gap-2">
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
                <div class="relative p-4 rounded-lg bg-white/5 hover:bg-white/10 transition" data-id="<?php echo $p['id']; ?>">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center shrink-0">
                                <span class="text-sm font-semibold text-accent-purple"><?php echo $p['id']; ?></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="text-xs text-white/60 truncate"><?php echo htmlspecialchars($p['tagline']); ?></div>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $p['status']; ?> text-xs shrink-0"><?php echo ucfirst($p['status']); ?></span>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                        <div class="flex items-center gap-4 text-xs text-white/40">
                            <span>Sort: <?php echo $p['sort_order']; ?></span>
                            <span><?php echo $p['is_visible'] ? '<span class="text-status-online">✅ Visible</span>' : '<span class="text-white/30">❌ Hidden</span>'; ?></span>
                        </div>
                        <div class="flex items-center gap-1">
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
                            <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('id')">ID</th>
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
                            <td colspan="7" class="text-center py-12 text-white/40">
                                <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                No users found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php $rowIndex = 1; foreach ($users as $u): ?>
                            <tr class="user-row border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo $u['id']; ?>" data-username="<?php echo htmlspecialchars(strtolower($u['username'])); ?>" data-email="<?php echo htmlspecialchars(strtolower($u['email'])); ?>" data-role="<?php echo $u['role']; ?>" data-status="<?php echo $u['status']; ?>" data-created="<?php echo $u['created_at']; ?>">
                                <td class="py-3 px-4 text-white/40 user-cell-id"><?php echo $rowIndex++; ?></td>
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
                    <?php $rowIndex = 1; foreach ($users as $u): ?>
                    <div class="user-card bg-white/5 rounded-xl p-4 border border-white/5 transition" data-id="<?php echo $u['id']; ?>" data-username="<?php echo htmlspecialchars(strtolower($u['username'])); ?>" data-email="<?php echo htmlspecialchars(strtolower($u['email'])); ?>" data-role="<?php echo $u['role']; ?>" data-status="<?php echo $u['status']; ?>" data-created="<?php echo $u['created_at']; ?>">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-accent-purple/20 flex items-center justify-center text-sm font-bold text-accent-purple user-card-index">
                                    <?php echo $rowIndex++; ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-white user-card-username"><?php echo htmlspecialchars($u['username']); ?></div>
                                    <div class="text-sm text-white/50 user-card-email"><?php echo htmlspecialchars($u['email']); ?></div>
                                </div>
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
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add License
                    </button>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-white/50 border-b border-white/10">
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
                            <td colspan="8" class="text-center py-12 text-white/40">
                                <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                No licenses yet. Click "Add License" to create one.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($licenses as $lic): ?>
                            <tr class="border-b border-white/5 hover:bg-white/5 transition" data-id="<?php echo $lic['id']; ?>" data-product-id="<?php echo $lic['product_id']; ?>">
                                <td class="py-3 px-4 font-mono text-sm text-white/80"><?php echo htmlspecialchars($lic['license_key']); ?></td>
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
                    <?php foreach ($licenses as $lic): ?>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/5" data-id="<?php echo $lic['id']; ?>" data-product-id="<?php echo $lic['product_id']; ?>">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-mono text-sm font-medium text-white truncate"><?php echo htmlspecialchars($lic['license_key']); ?></div>
                                <div class="text-xs text-white/60 mt-1"><?php echo htmlspecialchars($lic['product_name'] ?? 'N/A'); ?></div>
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
                        ], '10', 'w-16'); ?>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="button" id="license-prev-page" class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white/60 text-sm transition disabled:opacity-30 disabled:cursor-not-allowed flex-shrink-0" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <span class="text-sm text-white/60 whitespace-nowrap flex-shrink-0 min-w-fit">Page <span id="license-current-page">1</span> of <span id="license-total-pages">1</span></span>
                    <button type="button" id="license-next-page" class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white/60 text-sm transition disabled:opacity-30 disabled:cursor-not-allowed flex-shrink-0" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
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


