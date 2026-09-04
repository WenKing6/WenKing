<?php
/**
 * Reseller Page - Reseller Management Panel
 * 用户（客户）与许可证管理与 Admin / Manager 保持一致（共享渲染组件）：
 *   - Customers Tab：renderUserSection（客户列表；只读，防止越权管理系统用户）
 *   - Licenses Tab：renderLicenseSection（My Licenses 真实数据 + Create License 领取）
 *   - Create License 领取卡片弹窗（renderClaimKeysModal）
 * 页面可见性由 LicenseModule::getUiVisibility($role) 决定。
 */
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/License.php';
require_once __DIR__ . '/../section-helpers.php';
$productModel = new Product();
$userModel = new User();
$licenseModel = new License();
$products = $productModel->getAll();

// 当前用户识别：Session + 数据库角色校验（不信任 URL 参数，防篡改）
$sessionUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUser = $sessionUserId > 0 ? $userModel->findById($sessionUserId) : null;
$currentRole = $currentUser ? $currentUser['role'] : '';

// 页面可见性配置（权限矩阵唯一来源：LicenseModule）
$vis = LicenseModule::getUiVisibility($currentRole);

// 经理/经销商只看自己的钥匙；管理员可查看全部（审计）
if ($vis['license_scope'] === 'own') {
    $myLicenses  = $licenseModel->getByUser($sessionUserId);
    $allocations = $licenseModel->getAllocations($sessionUserId);
} elseif ($currentRole === 'admin') {
    $myLicenses  = $licenseModel->getAll();
    $allocations = $licenseModel->getAllocations();
} else {
    $myLicenses  = [];
    $allocations = [];
}

// 客户列表：经销商面向的终端用户（role = user）
$customers = $userModel->getByRole('user');
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent"><?php _e('reseller.title'); ?></span>
    </h1>
    <p class="text-white/60"><?php _e('reseller.subtitle'); ?></p>
    <?php if ($currentUser): ?>
    <p class="text-xs text-white/40 mt-2">Signed in as <span class="text-accent-cyan"><?php echo htmlspecialchars($currentUser['username']); ?></span> (<?php echo ucfirst($currentRole); ?>)</p>
    <?php endif; ?>
</div>

<!-- Tab Navigation -->
<div class="reseller-tabs mb-6 grid grid-cols-3 gap-2 border-b border-white/10">
    <button class="reseller-tab active flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="customers">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <?php _e('reseller.customers'); ?>
    </button>
    <button class="reseller-tab flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="licenses">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <?php _e('reseller.licenses'); ?>
    </button>
    <button class="reseller-tab flex items-center justify-center gap-2 px-4 py-2 text-white/70 hover:text-white transition border-b-2 border-transparent" data-tab="settings">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <?php _e('reseller.settings'); ?>
    </button>
</div>

<!-- Tab Content Area -->
<div class="reseller-tab-content">
    <!-- Customers Tab（共享 User 组件；只读，防越权） -->
    <div id="customers-tab" class="tab-panel active">
        <?php
        renderUserSection($customers, [
            'show_role_filter' => false, // 客户均为 user 角色，无需角色筛选
            'show_actions'     => false, // 经销商只读，不能编辑/切换系统用户状态
        ]);
        ?>
    </div>

    <!-- Licenses Tab（共享 License 组件，My Licenses 真实数据） -->
    <div id="licenses-tab" class="tab-panel">
        <?php
        renderLicenseSection($myLicenses, $products, $vis);
        ?>
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

<!-- Create License（领取钥匙）弹窗 -->
<?php renderClaimKeysModal($allocations); ?>
