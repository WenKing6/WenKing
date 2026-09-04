<?php
/**
 * App sections 公共渲染函数
 * 由各 section（admin.php / manager.php / reseller.php）require_once 后调用
 *
 * 本文件是「许可证模块」的共享 UI 渲染层：
 *   - renderPagination        通用分页控件
 *   - licenseDurationLabel    时长显示标签
 *   - renderCustomSelect      统一风格下拉框（wk-select）
 *   - renderQuotaActions      配额操作按钮（Create License / 分配许可证，置于许可证列表头部）
 *   - renderClaimKeysModal    Create License 领取钥匙卡片（弹窗）
 *   - renderGrantQuotaModal   授权配额卡片（弹窗，admin / manager 通过配置复用）
 *
 * 页面可见性由 LicenseModule::getUiVisibility($role) 决定，渲染层不做权限判断。
 */

require_once __DIR__ . '/../services/LicenseModule.php';

/**
 * 渲染统一风格的分页控件（Prev / Page x of y / Next）
 * JS 侧按 id 约定读取：{prefix}-prev-page / {prefix}-current-page / {prefix}-total-pages / {prefix}-next-page
 * 对应 JS 方法：_bindPagination(prefix, opts)
 */
function renderPagination(string $prefix): void {
    $btnClass = 'px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white/60 text-sm transition disabled:opacity-30 disabled:cursor-not-allowed flex-shrink-0';
    ?>
    <div class="flex items-center gap-2 flex-shrink-0">
        <button type="button" id="<?php echo $prefix; ?>-prev-page" class="<?php echo $btnClass; ?>" disabled aria-label="Previous page">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <span class="text-sm text-white/60 whitespace-nowrap flex-shrink-0 min-w-fit">Page <span id="<?php echo $prefix; ?>-current-page">1</span> of <span id="<?php echo $prefix; ?>-total-pages">1</span></span>
        <button type="button" id="<?php echo $prefix; ?>-next-page" class="<?php echo $btnClass; ?>" disabled aria-label="Next page">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
    <?php
}

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

/**
 * Render a custom select dropdown (wk-select component)
 * 视觉与 .app-input 保持一致，结构支持键盘导航与选中态指示
 *
 * 注意：原生 select 为 display:none，不能添加 required ——
 * 浏览器原生校验会因控件不可聚焦而静默阻断表单提交
 * （控制台报 "An invalid form control ... is not focusable"）。
 * 必填校验由各表单的 JS 提交逻辑负责（toast 提示）。
 */
function renderCustomSelect(string $id, array $options, string $selected = '', string $widthClass = 'w-full'): void {
    $currentLabel = $options[$selected] ?? (count($options) > 0 ? array_values($options)[0] : '');
    $currentValue = $selected;

    // 当未指定选中值或选中值不存在时，默认选中第一个选项
    if (!array_key_exists($selected, $options) && count($options) > 0) {
        $currentValue = array_key_first($options);
        $currentLabel = $options[$currentValue];
    }

    echo '<div class="wk-select ' . $widthClass . '" data-wk-select>';
    echo '    <select id="' . $id . '" class="wk-select__native" aria-hidden="true" tabindex="-1">';
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

/**
 * 渲染配额操作按钮（Create License / Assign to Reseller）
 * manager.php / reseller.php 共用，置于许可证列表卡片头部；
 * 可见性由权限矩阵（LicenseModule::getUiVisibility）决定。
 *
 * @param array $vis      LicenseModule::getUiVisibility($role) 输出
 * @param array $options  可选配置：
 *                        - grant_label string 授权按钮文案
 */
function renderQuotaActions(array $vis, array $options = []): void {
    // 可选：通过 options['button_extra_class'] 传入扩展类（例如 flex-1 让整行按钮等分宽度）
    $extra = trim($options['button_extra_class'] ?? '');
    if (!empty($vis['create_license_btn'])):
        ?>
        <button type="button" id="create-license-btn" class="btn-primary <?php echo $extra; ?> whitespace-nowrap">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create License
        </button>
        <?php
    endif;
    if (!empty($vis['grant_quota_targets'])):
        ?>
        <button type="button" data-grant-quota-open class="btn-secondary <?php echo $extra; ?> whitespace-nowrap">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
            </svg>
            <?php echo htmlspecialchars($options['grant_label'] ?? 'Assign to Reseller'); ?>
        </button>
        <?php
    endif;
}

/**
 * 渲染 Create License（领取钥匙）卡片弹窗
 * 由 manager.php / reseller.php 共用；交互逻辑在 assets/js/license-module.js
 *
 * @param array $allocations 当前用户配额列表（用于「配额」下拉，仅显示剩余 > 0 的项）
 */
function renderClaimKeysModal(array $allocations): void {
    ?>
    <!-- Claim Keys Modal（Create License） -->
    <div id="claim-keys-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="glass-card p-6 rounded-xl max-w-md w-full mx-4 transform scale-95 opacity-0 transition-all duration-200" id="claim-keys-dialog">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Create License</h3>
                <button class="text-white/40 hover:text-white transition p-1" id="claim-keys-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Quota *</label>
                    <select id="ck-quota" class="app-input w-full px-4 py-2">
                        <option value="">-- Select Quota --</option>
                        <?php foreach ($allocations as $alloc):
                            $remaining = (int)$alloc['quantity'] - (int)$alloc['used_count'];
                            if ($remaining <= 0) continue;
                            $key = (int)$alloc['product_id'] . '|' . (int)$alloc['duration_days'];
                        ?>
                        <option value="<?php echo $key; ?>"
                                data-product-id="<?php echo (int)$alloc['product_id']; ?>"
                                data-duration="<?php echo (int)$alloc['duration_days']; ?>"
                                data-remaining="<?php echo $remaining; ?>"
                                data-product-name="<?php echo htmlspecialchars($alloc['product_name'] ?? ''); ?>"
                                data-duration-label="<?php echo licenseDurationLabel((int)$alloc['duration_days']); ?>">
                            <?php echo htmlspecialchars(($alloc['product_name'] ?? 'N/A') . ' - ' . licenseDurationLabel((int)$alloc['duration_days']) . ' (' . $remaining . ' left)'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2">Product</label>
                        <div class="text-white font-semibold" id="ck-product">-</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2">Duration</label>
                        <div class="text-accent-cyan font-semibold" id="ck-duration">-</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2">Quota Remaining</label>
                        <div class="text-white font-semibold" id="ck-remaining">-</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2">Inventory Available</label>
                        <div class="text-white font-semibold" id="ck-inventory">-</div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Quantity *</label>
                    <input type="number" id="ck-quantity" class="app-input w-full px-4 py-2" min="1" placeholder="How many keys to claim">
                </div>
                <p class="text-xs text-white/40" id="ck-hint"></p>
                <div class="flex gap-3 pt-2">
                    <button type="button" class="btn-primary px-6 py-2 rounded-lg font-semibold flex-1" id="claim-keys-submit">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Claim
                    </button>
                    <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition flex-1" id="claim-keys-cancel">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 渲染「授权配额」卡片弹窗（admin / manager 共用，通过配置区分形态）
 *
 * 交互逻辑在 assets/js/license-module.js，配置通过 overlay 的 data-gq-config 注入。
 *
 * @param array $config 配置项：
 *   - id_prefix        string 组件 id 前缀（默认 gq；同页面保持唯一）
 *   - title            string 弹窗标题（默认 Grant Quota）
 *   - show_role_select bool   是否显示「角色」下拉（admin: true；manager: false）
 *   - roles            array  角色下拉选项（show_role_select = true 时使用）
 *   - fixed_role       string 固定目标角色（show_role_select = false 时使用，如 'reseller'）
 *   - managers         array  经理用户列表 [['id','username']]（admin 使用）
 *   - resellers        array  经销商用户列表 [['id','username']]（admin / manager 使用）
 *   - products         array  产品列表 [['id','name']]
 *   - from_own_quota   bool   授权是否从自己剩余配额划转（manager: true）
 *   - own_quotas       array  自己的配额映射 "productId|duration" => remaining（划转提示用）
 *   - submit_label     string 提交按钮文案（默认 Grant Quota）
 */
function renderGrantQuotaModal(array $config = []): void {
    $p = $config['id_prefix'] ?? 'gq';
    $title = $config['title'] ?? 'Grant Quota';
    $showRoleSelect = !empty($config['show_role_select']);
    $fixedRole = $config['fixed_role'] ?? null;
    $fromOwnQuota = !empty($config['from_own_quota']);
    $products = $config['products'] ?? [];

    $productOptions = ['' => '-- Select Product --'];
    foreach ($products as $prod) {
        $productOptions[$prod['id']] = $prod['name'];
    }

    // 注入给 JS 的运行时配置
    $jsConfig = [
        'showRoleSelect' => $showRoleSelect,
        'fixedRole'      => $fixedRole,
        'fromOwnQuota'   => $fromOwnQuota,
        'ownQuotas'      => $config['own_quotas'] ?? [],
    ];
    ?>
    <div id="grant-quota-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center" data-gq-config="<?php echo htmlspecialchars(json_encode($jsConfig), ENT_QUOTES); ?>">
        <div class="glass-card p-6 rounded-xl max-w-lg w-full mx-4 transform scale-95 opacity-0 transition-all duration-200" id="grant-quota-dialog">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($title); ?></h3>
                <button class="text-white/40 hover:text-white transition p-1" id="grant-quota-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="grant-quota-form" class="space-y-4">
                <?php if ($showRoleSelect): ?>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Role *</label>
                    <?php renderCustomSelect($p . '-role', $config['roles'] ?? ['' => '-- Select Role --'], '', 'w-full'); ?>
                </div>
                <div id="<?php echo $p; ?>-user-container" class="hidden">
                <?php else: ?>
                <div id="<?php echo $p; ?>-user-container">
                <?php endif; ?>
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php echo $fixedRole === 'reseller' ? 'Reseller *' : 'User *'; ?>
                    </label>
                    <?php if ($showRoleSelect): ?>
                    <div id="<?php echo $p; ?>-user-manager-wrapper">
                        <?php
                        $managerOptions = ['' => '-- Select Manager --'];
                        foreach (($config['managers'] ?? []) as $mu) {
                            $managerOptions[$mu['id']] = $mu['username'];
                        }
                        renderCustomSelect($p . '-manager', $managerOptions, '', 'w-full');
                        ?>
                    </div>
                    <?php endif; ?>
                    <div id="<?php echo $p; ?>-user-reseller-wrapper" class="<?php echo ($showRoleSelect || $fixedRole !== 'reseller') ? 'hidden' : ''; ?>">
                        <?php
                        $resellerOptions = ['' => '-- Select Reseller --'];
                        foreach (($config['resellers'] ?? []) as $ru) {
                            $resellerOptions[$ru['id']] = $ru['username'];
                        }
                        renderCustomSelect($p . '-reseller', $resellerOptions, '', 'w-full');
                        ?>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Product *</label>
                    <?php renderCustomSelect($p . '-product', $productOptions, '', 'w-full'); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Duration *</label>
                    <?php renderCustomSelect($p . '-duration', [
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
                    <label class="block text-sm font-medium text-white/70 mb-2">Quantity *</label>
                    <div class="wk-input-number w-full">
                        <button type="button" class="wk-input-number__btn wk-input-number__btn--minus" aria-label="Decrease">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <input type="number" id="<?php echo $p; ?>-quantity" class="wk-input-number__input" min="1" placeholder="How many keys can this user claim?" required>
                        <button type="button" class="wk-input-number__btn wk-input-number__btn--plus" aria-label="Increase">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <?php if ($fromOwnQuota): ?>
                    <p class="text-xs text-accent-cyan mt-1" id="<?php echo $p; ?>-own-hint"></p>
                    <?php endif; ?>
                    <p class="text-xs text-white/40 mt-1" id="<?php echo $p; ?>-inventory-hint"></p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg font-semibold flex-1">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <?php echo htmlspecialchars($config['submit_label'] ?? 'Grant Quota'); ?>
                    </button>
                    <button type="button" class="px-6 py-2 rounded-lg font-semibold bg-white/5 hover:bg-white/10 text-white/80 transition flex-1" id="grant-quota-cancel">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * 渲染「用户管理」模块（Admin / Manager / Reseller 共用）
 *
 * 三页共用同一套结构与 ID 约定（user-search / user-list / user-cards / user-per-page...），
 * 交互逻辑集中在 assets/js/app.js（_initUserPagination / _initUserSearch / _initUserEditForm）。
 * 单页应用同一时间只加载一个 section，因此 ID 不会冲突。
 *
 * @param array $users 用户列表（每项含 id/username/email/role/status/created_at）
 * @param array $opts  可选配置：
 *   - show_stats        bool 是否显示统计卡片（默认 true）
 *   - show_actions      bool 是否显示编辑/切换状态按钮（默认 true）
 *   - show_role_filter  bool 是否显示角色筛选（默认 true；Reseller 客户均为同角色时传 false）
 *   - role_options      array 角色筛选选项（默认仅 All Roles）
 */
function renderUserSection(array $users, array $opts = []): void {
    $showStats       = $opts['show_stats'] ?? true;
    $showActions     = $opts['show_actions'] ?? true;
    $showRoleFilter  = $opts['show_role_filter'] ?? true;
    $roleOptions     = $opts['role_options'] ?? ['' => 'All Roles'];
    $emptyColspan    = $showActions ? 6 : 5;
    ?>
    <?php if ($showStats): ?>
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
    <?php endif; ?>

    <!-- User List -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
            <h3 class="text-lg font-semibold text-white shrink-0">User List (<span id="user-count"><?php echo count($users); ?></span>)</h3>
            <div class="relative w-full md:flex-1 md:basis-0 md:min-w-[150px] md:max-w-[15rem]">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" id="user-search" class="app-input w-full pl-9 pr-4 py-2" placeholder="Search by username...">
            </div>
            <?php if ($showRoleFilter): ?>
            <?php renderCustomSelect('user-role-filter', $roleOptions, '', 'w-full sm:w-36 md:shrink-0 md:basis-auto'); ?>
            <?php endif; ?>
        </div>

        <!-- Desktop Table -->
        <div class="hidden 2xl:block overflow-x-auto">
            <table class="w-full text-sm table-fixed">
                <colgroup>
                    <col class="w-[20%]">
                    <col class="w-[26%]">
                    <col class="w-[12%]">
                    <col class="w-[12%]">
                    <col class="w-[16%]">
                    <?php if ($showActions): ?>
                    <col class="w-[14%]">
                    <?php endif; ?>
                </colgroup>
                <thead>
                    <tr class="text-white/50 border-b border-white/10">
                        <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('username')">Username</th>
                        <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('email')">Email</th>
                        <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('role')">Role</th>
                        <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('status')">Status</th>
                        <th class="text-left py-3 px-4 cursor-pointer hover:text-white/80 transition" onclick="sortUsers('created_at')">Created</th>
                        <?php if ($showActions): ?>
                        <th class="text-left py-3 px-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="user-list">
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="<?php echo $emptyColspan; ?>" class="text-center py-12 text-white/40">
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
                            <?php if ($showActions): ?>
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
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Card List (Mobile + iPad) -->
        <div class="2xl:hidden grid grid-cols-1 md:grid-cols-2 gap-3" id="user-cards">
            <?php if (empty($users)): ?>
            <div class="text-center py-12 text-white/40 md:col-span-2">
                <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                No users found
            </div>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <div class="user-card bg-white/[0.03] hover:bg-white/[0.06] rounded-xl p-5 border border-white/5 hover:border-white/10 transition" data-id="<?php echo $u['id']; ?>" data-username="<?php echo htmlspecialchars(strtolower($u['username'])); ?>" data-email="<?php echo htmlspecialchars(strtolower($u['email'])); ?>" data-role="<?php echo $u['role']; ?>" data-status="<?php echo $u['status']; ?>" data-created="<?php echo $u['created_at']; ?>">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-accent-purple/30 to-accent-cyan/20 flex items-center justify-center text-sm font-bold text-white/80 shrink-0"><?php echo strtoupper(substr($u['username'], 0, 1)); ?></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-white truncate user-card-username"><?php echo htmlspecialchars($u['username']); ?></div>
                            <div class="text-xs text-white/40 truncate user-card-email"><?php echo htmlspecialchars($u['email']); ?></div>
                        </div>
                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            <span class="role-badge text-xs px-2 py-0.5 rounded-full border <?php echo $u['role'] === 'admin' ? 'bg-accent-purple/20 text-accent-purple border-accent-purple/30' : ($u['role'] === 'manager' ? 'bg-accent-cyan/20 text-accent-cyan border-accent-cyan/30' : ($u['role'] === 'reseller' ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-white/10 text-white/60 border-white/10')); ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                            <span class="status-badge <?php echo $u['status'] === 'active' ? 'status-online' : 'bg-red-500/20 text-red-400 border-red-500/30'; ?> text-xs user-card-status">
                                <?php echo ucfirst($u['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-white/5">
                        <span class="text-xs text-white/40 user-card-created">
                            <?php echo date('Y-m-d', strtotime($u['created_at'])); ?>
                        </span>
                        <?php if ($showActions): ?>
                        <div class="flex gap-1.5">
                            <button class="btn-user-edit text-white/40 hover:text-accent-blue transition p-2.5 rounded-lg hover:bg-white/5" title="Edit" data-user='<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>'>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="btn-user-toggle text-white/40 hover:text-accent-blue transition p-2.5 rounded-lg hover:bg-white/5" title="Toggle Status" data-id="<?php echo $u['id']; ?>" data-status="<?php echo $u['status']; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <?php endif; ?>
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
    <?php
}

/**
 * 渲染「许可证管理」模块（Admin / Manager / Reseller 共用）
 *
 * 结构统一为 Admin 样式；复选框列 / 删除 / 回收 / Add License / 角色·用户筛选
 * 由权限矩阵（LicenseModule::getUiVisibility）控制：
 *   - Admin：完整（复选框 + Add License + Grant Quota + 批量删除 + 单行删除/回收 + 角色/用户筛选）
 *   - Manager：Create License + Assign to Reseller，无复选框/删除
 *   - Reseller：Create License，无复选框/删除
 *
 * JS 侧通过 <tbody id="license-list" data-has-checkbox="0|1"> 区分列偏移：
 *   - data-has-checkbox="1"（Admin）：License Key = td:nth-child(2)、Status = td:nth-child(6)
 *   - data-has-checkbox="0"：License Key = td:nth-child(1)、Status = td:nth-child(5)
 *
 * @param array $licenses 许可证列表
 * @param array $products 产品列表（产品筛选用）
 * @param array $vis      LicenseModule::getUiVisibility($role)
 * @param array $opts     可选配置：
 *   - is_admin     bool  是否为管理员视图（显示角色/用户筛选；默认 false）
 *   - users        array 用户列表（仅 admin 需要，用于 data-manager-id / data-reseller-id）
 *   - grant_label  string Grant Quota 按钮文案（默认 Grant Quota）
 */
function renderLicenseSection(array $licenses, array $products, array $vis, array $opts = []): void {
    $isAdmin     = !empty($opts['is_admin']);
    $users       = $opts['users'] ?? [];
    $grantLabel  = $opts['grant_label'] ?? 'Grant Quota';
    $hasCheckbox = !empty($vis['delete_license']); // 复选框/删除仅 admin

    // 统计卡片初始值（JS 初始化时也会重算）
    $statTotal   = count($licenses);
    $statActive  = count(array_filter($licenses, fn($l) => $l['status'] === 'active'));
    $statUnused  = count(array_filter($licenses, fn($l) => $l['status'] === 'unused'));
    $statExpired = count(array_filter($licenses, fn($l) => in_array($l['status'], ['expired', 'disabled'])));

    // 用户角色映射（仅 admin 的角色/用户筛选需要）
    $userRoleMap = [];
    if ($isAdmin) {
        foreach ($users as $u) {
            $userRoleMap[$u['id']] = $u['role'];
        }
    }
    ?>
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
            <div class="text-3xl font-bold text-white" id="license-total"><?php echo $statTotal; ?></div>
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
            <div class="text-3xl font-bold text-white" id="license-active"><?php echo $statActive; ?></div>
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
            <div class="text-3xl font-bold text-white" id="license-unused"><?php echo $statUnused; ?></div>
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
            <div class="text-3xl font-bold text-white" id="license-expired"><?php echo $statExpired; ?></div>
        </div>
    </div>

    <!-- License List -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex flex-col md:flex-row md:flex-wrap md:items-center gap-3 mb-6">
            <h3 class="text-lg font-semibold text-white shrink-0">License Keys (<span id="license-count"><?php echo $statTotal; ?></span>)</h3>
            <div class="relative w-full md:flex-1 md:basis-0 md:min-w-[150px] 2xl:basis-56 2xl:flex-none">
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
            renderCustomSelect('license-product-filter', $licenseProductOptions, '', 'w-full sm:w-40 md:shrink-0 md:basis-auto');
            renderCustomSelect('license-status-filter', [
                '' => 'All Status',
                'unused' => 'Unused',
                'active' => 'Active',
                'expired' => 'Expired',
                'disabled' => 'Disabled'
            ], '', 'w-full sm:w-36 md:shrink-0 md:basis-auto');
            ?>
            <div class="grid grid-cols-2 items-center gap-2 md:basis-full 2xl:flex 2xl:basis-auto 2xl:ml-auto 2xl:justify-end">
                <?php if (!empty($vis['add_license'])): ?>
                <button type="button" class="btn-primary whitespace-nowrap" onclick="openLicenseModal()">
                    Add License
                </button>
                <?php endif; ?>
                <?php renderQuotaActions($vis, ['grant_label' => $grantLabel]); ?>
                <?php if (!empty($vis['delete_license'])): ?>
                <button type="button" id="license-delete-selected" class="hidden col-span-2 justify-center items-center gap-1 whitespace-nowrap btn-danger 2xl:w-auto" title="Delete selected licenses">
                    Delete Selected (<span id="license-selected-count">0</span>)
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden 2xl:block overflow-x-auto">
            <table class="w-full text-sm table-fixed">
                <colgroup>
                    <?php if ($hasCheckbox): ?>
                    <col class="w-[4%]">
                    <?php endif; ?>
                    <col class="w-[20%]">
                    <col class="w-[9%]">
                    <col class="w-[9%]">
                    <col class="w-[9%]">
                    <col class="w-[13%]">
                    <col class="w-[12%]">
                    <col class="w-[12%]">
                    <?php if ($hasCheckbox): ?>
                    <col class="w-[12%]">
                    <?php endif; ?>
                </colgroup>
                <thead>
                    <tr class="text-white/50 border-b border-white/10">
                        <?php if ($hasCheckbox): ?>
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" id="license-select-all" class="license-checkbox" title="Select all on this page" aria-label="Select all licenses on this page">
                        </th>
                        <?php endif; ?>
                        <th class="text-left py-3 px-4">License Key</th>
                        <th class="text-left py-3 px-4">Product</th>
                        <th class="text-left py-3 px-4">Duration</th>
                        <th class="text-left py-3 px-4">Assigned To</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-left py-3 px-4">Created</th>
                        <th class="text-left py-3 px-4">Expires</th>
                        <?php if ($hasCheckbox): ?>
                        <th class="text-left py-3 px-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="license-list" data-has-checkbox="<?php echo $hasCheckbox ? '1' : '0'; ?>">
                    <?php if (empty($licenses)): ?>
                    <tr>
                        <td colspan="<?php echo $hasCheckbox ? 9 : 7; ?>" class="text-center py-12 text-white/40">
                            <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            No licenses yet. Click "Add License" to create one.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($licenses as $lic): ?>
                        <?php $userRole = !empty($lic['user_id']) ? ($userRoleMap[$lic['user_id']] ?? null) : null; ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition"
                            data-id="<?php echo $lic['id']; ?>"
                            data-product-id="<?php echo $lic['product_id']; ?>"
                            <?php if ($isAdmin): ?>
                            <?php if ($userRole === 'manager'): ?>
                            data-manager-id="<?php echo $lic['user_id']; ?>"
                            <?php elseif ($userRole === 'reseller'): ?>
                            data-reseller-id="<?php echo $lic['user_id']; ?>"
                            <?php endif; ?>
                            <?php endif; ?>>
                            <?php if ($hasCheckbox): ?>
                            <td class="py-3 px-4">
                                <input type="checkbox" class="license-row-check license-checkbox" data-id="<?php echo $lic['id']; ?>" aria-label="Select license">
                            </td>
                            <?php endif; ?>
                            <td class="py-3 px-4"><span class="block font-mono text-sm text-white/80 truncate"><?php echo htmlspecialchars($lic['license_key']); ?></span></td>
                            <td class="py-3 px-4 text-white/60"><span class="block truncate"><?php echo htmlspecialchars($lic['product_name'] ?? 'N/A'); ?></span></td>
                            <td class="py-3 px-4 text-white/60"><?php echo licenseDurationLabel((int)$lic['duration_days']); ?></td>
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
                            <?php if ($hasCheckbox): ?>
                            <td class="py-3 px-2 flex gap-1.5">
                                <?php if (!empty($vis['recycle']) && $lic['user_id'] && $lic['status'] === 'unused'): ?>
                                <button class="btn-license-recycle text-white/40 hover:text-accent-cyan transition p-1.5 rounded-lg hover:bg-white/5" title="Recycle to inventory" data-id="<?php echo $lic['id']; ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                                <?php endif; ?>
                                <button class="btn-license-delete text-white/40 hover:text-red-500 transition p-1.5 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $lic['id']; ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Card List (Mobile + iPad) -->
        <div class="2xl:hidden grid grid-cols-1 md:grid-cols-2 gap-3" id="license-cards">
            <?php if (empty($licenses)): ?>
            <div class="text-center py-12 text-white/40 md:col-span-2">
                <svg class="w-16 h-16 mx-auto text-white/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                No licenses yet. Click "Add License" to create one.
            </div>
            <?php else: ?>
                <?php foreach ($licenses as $lic): ?>
                <?php $userRole = !empty($lic['user_id']) ? ($userRoleMap[$lic['user_id']] ?? null) : null; ?>
                <div class="license-card bg-white/[0.03] hover:bg-white/[0.06] rounded-xl p-5 border border-white/5 hover:border-white/10 transition"
                     data-id="<?php echo $lic['id']; ?>"
                     data-product-id="<?php echo $lic['product_id']; ?>"
                     data-key="<?php echo htmlspecialchars($lic['license_key']); ?>"
                     data-status="<?php echo $lic['status']; ?>"
                     <?php if ($isAdmin): ?>
                     <?php if ($userRole === 'manager'): ?>
                     data-manager-id="<?php echo $lic['user_id']; ?>"
                     <?php elseif ($userRole === 'reseller'): ?>
                     data-reseller-id="<?php echo $lic['user_id']; ?>"
                     <?php endif; ?>
                     <?php endif; ?>>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <?php if ($hasCheckbox): ?>
                            <input type="checkbox" class="license-row-check license-checkbox mt-1 shrink-0" data-id="<?php echo $lic['id']; ?>" aria-label="Select license">
                            <?php endif; ?>
                            <div class="flex-1 min-w-0">
                                <div class="font-mono text-sm font-medium text-white truncate"><?php echo htmlspecialchars($lic['license_key']); ?></div>
                                <div class="text-xs text-white/50 mt-1 truncate"><?php echo htmlspecialchars($lic['product_name'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <span class="status-badge <?php echo $lic['status'] === 'active' ? 'status-online' : ($lic['status'] === 'unused' ? 'status-updating' : ($lic['status'] === 'expired' || $lic['status'] === 'disabled' ? 'bg-red-500/20 text-red-400 border-red-500/30' : '')); ?> text-xs shrink-0">
                            <?php echo ucfirst($lic['status']); ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-white/40 pt-3 border-t border-white/5">
                        <div class="flex gap-3 min-w-0">
                            <span class="shrink-0"><?php echo licenseDurationLabel((int)$lic['duration_days']); ?></span>
                            <span class="truncate">
                                <?php if ($lic['user_id']): ?>
                                    <?php echo htmlspecialchars($lic['user_name'] ?? 'Unknown'); ?>
                                <?php else: ?>
                                    <span class="text-accent-purple">Admin</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if ($hasCheckbox): ?>
                        <div class="flex items-center shrink-0 gap-1">
                            <?php if (!empty($vis['recycle']) && $lic['user_id'] && $lic['status'] === 'unused'): ?>
                            <button class="btn-license-recycle text-white/40 hover:text-accent-cyan transition p-2.5 rounded-lg hover:bg-white/5" title="Recycle to inventory" data-id="<?php echo $lic['id']; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                            <?php endif; ?>
                            <button class="btn-license-delete text-white/40 hover:text-red-500 transition p-2.5 rounded-lg hover:bg-white/5" title="Delete" data-id="<?php echo $lic['id']; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6 pt-6 border-t border-white/10">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 min-w-0">
                <div class="text-sm text-white/40">
                    Showing <span id="license-showing-start">0</span>-<span id="license-showing-end">0</span> of <span id="license-total-count"><?php echo $statTotal; ?></span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm text-white/40">Per page</span>
                    <?php renderCustomSelect('license-per-page', [
                        '10' => '10',
                        '20' => '20',
                        '50' => '50',
                        '100' => '100'
                    ], '10', 'wk-select--fit'); ?>
                    <?php if ($isAdmin): ?>
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
                    <?php endif; ?>
                </div>
            </div>
            <?php renderPagination('license'); ?>
        </div>
    </div>
    <?php
}

/**
 * 渲染「编辑用户」弹窗（Admin / Manager / Reseller 共用，置于 tab-panel 之外）
 *
 * @param array $opts 可选配置：
 *   - show_role bool 是否显示角色下拉（默认 true；Reseller 隐藏，防越权改角色）
 */
function renderUserEditModal(array $opts = []): void {
    $showRole = $opts['show_role'] ?? true;
    ?>
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
                <?php if ($showRole): ?>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">Role</label>
                    <?php renderCustomSelect('ue-role', [
                        'user' => 'User',
                        'reseller' => 'Reseller',
                        'manager' => 'Manager',
                        'admin' => 'Admin'
                    ], 'user', 'w-full'); ?>
                </div>
                <?php endif; ?>
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
    <?php
}
