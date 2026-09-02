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
    if (!empty($vis['create_license_btn'])):
        ?>
        <button type="button" id="create-license-btn" class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create License
        </button>
        <?php
    endif;
    if (!empty($vis['grant_quota_targets'])):
        ?>
        <button type="button" data-grant-quota-open class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap bg-accent-cyan/20 text-accent-cyan border border-accent-cyan/30 hover:bg-accent-cyan/30 transition">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
