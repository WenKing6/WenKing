<?php
/**
 * Reseller Page - Reseller Management Panel
 * Licenses 标签接入 LicenseModule 共享渲染：
 *   - My Quota 配额面板（配额卡片 + Create License 按钮）
 *   - My Licenses（真实钥匙数据）
 *   - Create License 领取卡片弹窗（renderClaimKeysModal）
 * 页面可见性由 LicenseModule::getUiVisibility($role) 决定。
 */
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/License.php';
require_once __DIR__ . '/../section-helpers.php';

// 当前用户识别：Session + 数据库角色校验（不信任 URL 参数，防篡改）
$userModel = new User();
$licenseModel = new License();
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUser = $sessionUserId > 0 ? $userModel->findById($sessionUserId) : null;
$currentRole = $currentUser ? $currentUser['role'] : '';

// 页面可见性配置（权限矩阵唯一来源：LicenseModule）
$vis = LicenseModule::getUiVisibility($currentRole);

// 管理员可查看全部（审计）；经理/经销商只看自己的
if ($currentRole === 'admin') {
    $allocations = $licenseModel->getAllocations();
    $myLicenses = $licenseModel->getAll();
} elseif ($vis['license_scope'] === 'own') {
    $allocations = $licenseModel->getAllocations($sessionUserId);
    $myLicenses = $licenseModel->getByUser($sessionUserId);
} else {
    $allocations = [];
    $myLicenses = [];
}

// 钥匙状态徽章映射
$licenseBadgeMap = [
    'unused'   => ['status-updating', 'Unused'],
    'active'   => ['status-online', 'Active'],
    'expired'  => ['status-updating', 'Expired'],
    'disabled' => ['status-updating', 'Disabled'],
];
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent"><?php _e('reseller.title'); ?></span>
    </h1>
    <p class="text-white/60"><?php _e('reseller.subtitle'); ?></p>
</div>

<!-- Tab Navigation -->
<div class="reseller-tabs mb-6 flex gap-2 border-b border-white/10">
    <button class="reseller-tab active px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="customers">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <?php _e('reseller.customers'); ?>
    </button>
    <button class="reseller-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <?php _e('reseller.licenses'); ?>
    </button>
    <button class="reseller-tab px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="settings">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 01-1.066 2.573c-.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <?php _e('reseller.settings'); ?>
    </button>
</div>

<!-- Tab Content Area -->
<div class="reseller-tab-content">
    <!-- Customers Tab -->
    <div id="customers-tab" class="tab-panel active">
        <!-- Search and Filter -->
        <div class="glass-card p-4 rounded-xl mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text" 
                               id="customer-search"
                               class="app-input w-full pl-10 pr-4 py-2"
                               placeholder="<?php _e('reseller.search_ph'); ?>">
                    </div>
                </div>
                <div class="flex gap-2">
                    <select class="app-select">
                        <option value=""><?php _e('reseller.all_status'); ?></option>
                        <option value="active"><?php _e('reseller.active'); ?></option>
                        <option value="inactive"><?php _e('reseller.inactive'); ?></option>
                        <option value="banned"><?php _e('reseller.banned'); ?></option>
                    </select>
                    <button class="btn-primary px-6 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <?php _e('reseller.add_customer'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer List - Card Layout -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white"><?php _e('reseller.customer_list'); ?></h3>
                <span class="text-sm text-white/40"><?php _e('reseller.customers_count'); ?></span>
            </div>

            <div class="space-y-3">
            <!-- Customer Card 1 -->
            <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-full bg-accent-purple/20 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-accent-purple">JD</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-white truncate">JohnDoe</div>
                            <div class="text-xs text-white/60 truncate hidden sm:block">john@example.com</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan hidden sm:inline-block">WK-2024-XXXX-XXXX</code>
                        <span class="status-badge status-online text-xs"><?php _e('reseller.active'); ?></span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-white/5 text-xs text-white/40 sm:hidden">
                    <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan">WK-2024-XXXX-XXXX</code>
                    <span>192.168.1.100</span>
                </div>
            </div>

            <!-- Customer Card 2 -->
            <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-full bg-accent-cyan/20 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-accent-cyan">AS</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-white truncate">AliceSmith</div>
                            <div class="text-xs text-white/60 truncate hidden sm:block">alice@example.com</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan hidden sm:inline-block">WK-2024-YYYY-YYYY</code>
                        <span class="status-badge status-online text-xs"><?php _e('reseller.active'); ?></span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-white/5 text-xs text-white/40 sm:hidden">
                    <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan">WK-2024-YYYY-YYYY</code>
                    <span>10.0.0.50</span>
                </div>
            </div>

            <!-- Customer Card 3 -->
            <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-amber-400">BW</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-white truncate">BobWilson</div>
                            <div class="text-xs text-white/60 truncate hidden sm:block">bob@example.com</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan hidden sm:inline-block">WK-2024-ZZZZ-ZZZZ</code>
                        <span class="status-badge status-updating text-xs"><?php _e('reseller.inactive'); ?></span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button class="text-white/40 hover:text-accent-purple transition p-2 rounded-lg hover:bg-white/5" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-accent-blue transition p-2 rounded-lg hover:bg-white/5" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button class="text-white/40 hover:text-red-500 transition p-2 rounded-lg hover:bg-white/5" title="Disable">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-white/5 text-xs text-white/40 sm:hidden">
                    <code class="text-xs bg-white/10 px-2 py-0.5 rounded text-accent-cyan">WK-2024-ZZZZ-ZZZZ</code>
                    <span>172.16.0.25</span>
                </div>
            </div>
        </div>
    </div>

        <!-- Pagination -->
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-white/60"><?php _e('reseller.showing'); ?></div>
            <div class="flex gap-2">
                <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed"><?php _e('reseller.previous'); ?></button>
                <button class="px-3 py-1 rounded bg-white/5 text-white/40 cursor-not-allowed"><?php _e('reseller.next'); ?></button>
            </div>
        </div>
    </div>

    <!-- Licenses Tab -->
    <div id="licenses-tab" class="tab-panel">
        <!-- Batch Query -->
        <div class="glass-card p-6 rounded-xl mb-6">
            <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('reseller.license_lookup'); ?></h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('reseller.single_query'); ?></label>
                    <div class="flex gap-2">
                        <input type="text" 
                               class="app-input flex-1 px-4 py-2"
                               placeholder="<?php _e('reseller.single_query_ph'); ?>">
                        <button class="btn-primary px-6 py-2 rounded-lg font-semibold whitespace-nowrap">
                            <?php _e('reseller.query'); ?>
                        </button>
                    </div>
                </div>
                <div class="border-t border-white/10 pt-4">
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('reseller.batch_query'); ?></label>
                    <textarea class="app-input w-full px-4 py-2 h-32 resize-none"
                              placeholder="WK-2024-XXXX-XXXX&#10;WK-2024-YYYY-YYYY&#10;WK-2024-ZZZZ-ZZZZ"></textarea>
                    <div class="flex gap-2 mt-2">
                        <button class="btn-primary px-6 py-2 rounded-lg font-semibold">
                            <?php _e('reseller.batch_btn'); ?>
                        </button>
                        <button class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition">
                            <?php _e('reseller.export_results'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Licenses - Card Layout（真实数据） -->
        <div class="glass-card p-6 rounded-xl">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <h3 class="text-lg font-semibold text-white"><?php _e('reseller.allocation_records'); ?></h3>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm text-white/40"><?php echo count($myLicenses); ?> key(s)</span>
                    <?php renderQuotaActions($vis); ?>
                </div>
            </div>

            <?php if (empty($myLicenses)): ?>
            <div class="text-center py-8 text-white/40">
                No licenses yet. Click "Create License" to claim keys from inventory within your quota.
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($myLicenses as $lic):
                    $badge = $licenseBadgeMap[$lic['status']] ?? ['status-updating', ucfirst($lic['status'])];
                ?>
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/10 transition">
                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-white/40 mb-1"><?php _e('reseller.license_key'); ?></div>
                            <code class="text-xs bg-white/10 px-2 py-1 rounded text-accent-cyan"><?php echo htmlspecialchars($lic['license_key']); ?></code>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1"><?php _e('reseller.product'); ?></div>
                            <div class="text-sm text-white"><?php echo htmlspecialchars(($lic['product_name'] ?? 'N/A') . ' (' . licenseDurationLabel((int)$lic['duration_days']) . ')'); ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1"><?php _e('reseller.assigned_to'); ?></div>
                            <div class="text-sm text-white/60"><?php echo htmlspecialchars($lic['user_name'] ?? '—'); ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-white/40 mb-1"><?php _e('reseller.time'); ?></div>
                            <div class="text-sm text-white"><?php _e('reseller.activated'); ?>: <?php echo !empty($lic['activated_at']) ? date('Y-m-d', strtotime($lic['activated_at'])) : '—'; ?></div>
                            <div class="text-xs text-white/60"><?php _e('reseller.expires'); ?>: <?php echo !empty($lic['expires_at']) ? date('Y-m-d', strtotime($lic['expires_at'])) : '—'; ?></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                        <span class="status-badge <?php echo $badge[0]; ?> text-xs"><?php echo $badge[1]; ?></span>
                        <div class="text-xs text-white/40">Created: <?php echo date('Y-m-d', strtotime($lic['created_at'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="settings-tab" class="tab-panel">
        <div class="glass-card p-6 rounded-xl">
            <h3 class="text-lg font-semibold mb-6 text-white"><?php _e('reseller.reseller_info'); ?></h3>
            <form class="space-y-6">
                <!-- Logo -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('reseller.logo'); ?></label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <button type="button" class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold">
                            <?php _e('reseller.upload_logo'); ?>
                        </button>
                    </div>
                </div>

                <!-- Website Link -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('reseller.website_link'); ?></label>
                    <input type="url" 
                           class="app-input w-full px-4 py-2"
                           placeholder="https://your-website.com"
                           value="https://example-reseller.com">
                </div>

                <!-- Payment Methods -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('reseller.payment_methods'); ?></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox" checked>
                            <span class="text-sm text-white">PayPal</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox" checked>
                            <span class="text-sm text-white">Credit Card</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox">
                            <span class="text-sm text-white">Crypto</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox">
                            <span class="text-sm text-white">Alipay</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-lg bg-white/5 hover:bg-white/10 cursor-pointer transition">
                            <input type="checkbox" class="auth-checkbox">
                            <span class="text-sm text-white">WeChat Pay</span>
                        </label>
                    </div>
                </div>

                <!-- Contact Info -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('reseller.contact_info'); ?></label>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-white/50 mb-1">Discord</label>
                            <input type="text" 
                                   class="app-input w-full px-4 py-2"
                                   placeholder="YourDiscord#1234"
                                   value="ResellerAdmin#0001">
                        </div>
                        <div>
                            <label class="block text-xs text-white/50 mb-1">Telegram</label>
                            <input type="text" 
                                   class="app-input w-full px-4 py-2"
                                   placeholder="@yourtelegram"
                                   value="@reseller_support">
                        </div>
                        <div>
                            <label class="block text-xs text-white/50 mb-1">Email</label>
                            <input type="email" 
                                   class="app-input w-full px-4 py-2"
                                   placeholder="support@example.com"
                                   value="support@reseller.com">
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2"><?php _e('reseller.description'); ?></label>
                    <textarea class="app-input w-full px-4 py-2 h-24 resize-none"
                              placeholder="<?php _e('reseller.description_ph'); ?>">Authorized WenKing reseller providing premium game mods and 24/7 support.</textarea>
                </div>

                <!-- Save Button -->
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold">
                        <?php _e('reseller.save_changes'); ?>
                    </button>
                    <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition">
                        <?php _e('reseller.cancel'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php renderClaimKeysModal($allocations); ?>
