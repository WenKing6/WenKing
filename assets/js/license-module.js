/**
 * ============================================================
 * LicenseModule UI - 许可证模块前端交互层（可复用）
 * ============================================================
 * 与后端 includes/services/LicenseModule.php 配套，负责两类共享弹窗：
 *   1. Create License（领取钥匙）卡片 —— manager.php / reseller.php 共用
 *      - 入口：配额卡片上的 .btn-claim-keys 按钮、面板上的 #create-license-btn
 *      - 弹窗 DOM 由 section-helpers.php 的 renderClaimKeysModal() 渲染
 *   2. Grant Quota（授权配额）卡片 —— admin.php / manager.php 通过 data-gq-config 配置复用
 *      - 入口：带 data-grant-quota-open 属性的按钮
 *      - 弹窗 DOM 由 renderGrantQuotaModal() 渲染，运行时配置注入在 overlay 的 data-gq-config
 *
 * 设计约定：
 *   - 本模块不做权限判断（权限由后端权限矩阵决定），只做交互与即时反馈
 *   - 所有提交走 postWithFeedback：按钮 loading + 防重复点击 + 30s 超时提示
 *   - 依赖 window.showToast（app.js 提供）；缺失时静默降级
 */
(function () {
    'use strict';

    // 当前模块版本（用户可在浏览器控制台输入 window.__lmVersion 自查加载的是否为新版）
    window.__lmVersion = '20260902';

    var LicenseModuleUI = {};

    /**
     * 通用提交请求：带 loading / 防重复点击 / 超时提示
     * @param {HTMLElement|null} btn  提交按钮（提交期间禁用并显示 Submitting...）
     * @param {string}           body x-www-form-urlencoded 请求体
     * @returns {Promise<Object|null>} 解析后的 JSON；网络错误/超时/解析失败返回 null
     */
    LicenseModuleUI.postWithFeedback = function (btn, body) {
        var origHtml = btn ? btn.innerHTML : '';
        var controller = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        var timedOut = false;
        var timer = null;

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Submitting...';
        }

        if (controller) {
            timer = setTimeout(function () {
                timedOut = true;
                controller.abort();
            }, 30000);
        }

        function restore() {
            if (timer) clearTimeout(timer);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }

        return fetch(window.SITE_URL + '/api/licenses.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
            signal: controller ? controller.signal : undefined
        })
            .then(function (r) { return r.json(); })
            .catch(function () {
                restore();
                toast(timedOut ? 'Request timed out, the server may be busy. Please try again later.' : 'Network error, please try again.', 'error');
                return null;
            })
            .then(function (data) {
                restore();
                return data;
            });
    };

    function toast(message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type || 'info');
        }
    }

    function openOverlay(overlay, dialog) {
        if (!overlay || !dialog) return;
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        setTimeout(function () {
            dialog.classList.remove('scale-95', 'opacity-0');
            dialog.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeOverlay(overlay, dialog) {
        if (!overlay || !dialog) return;
        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');
        overlay.classList.remove('flex');
        overlay.classList.add('hidden');
    }

    /**
     * 查询某产品 + 时长的库存可用钥匙数
     * @param {Function} cb (count:number|null)=>void
     */
    function fetchInventory(productId, durationDays, cb) {
        fetch(window.SITE_URL + '/api/licenses.php?action=inventory_count&product_id=' + productId + '&duration_days=' + durationDays)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                cb(data && data.success ? parseInt(data.count, 10) || 0 : null);
            })
            .catch(function () { cb(null); });
    }

    // ============================================================
    // Grant Quota（授权配额）弹窗 —— admin / manager 通过 data-gq-config 复用
    // ============================================================
    function initGrantQuotaModal() {
        var overlay = document.getElementById('grant-quota-overlay');
        if (!overlay || overlay.dataset.lmBound) return;
        overlay.dataset.lmBound = '1';
        var dialog = document.getElementById('grant-quota-dialog');

        // 读取服务端注入的运行时配置
        var config = { showRoleSelect: false, fixedRole: null, fromOwnQuota: false, ownQuotas: {} };
        try {
            var raw = overlay.getAttribute('data-gq-config');
            if (raw) {
                var parsed = JSON.parse(raw);
                Object.keys(parsed).forEach(function (k) { config[k] = parsed[k]; });
            }
        } catch (e) { /* 配置缺失时按默认行为运行 */ }

        function open() {
            // 每次打开重置角色联动与提示
            var roleEl = document.getElementById('gq-role');
            if (config.showRoleSelect && roleEl) {
                applyRoleVisibility(roleEl.value);
            }
            refreshHints();
            openOverlay(overlay, dialog);
        }

        // 多入口打开（admin: #grant-quota-btn；manager: 面板按钮）
        // 使用 document 级事件委托：不依赖按钮在初始化时已存在，
        // 对动态渲染的按钮和任何脚本加载时序都免疫
        if (!LicenseModuleUI._openersDelegated) {
            LicenseModuleUI._openersDelegated = true;
            document.addEventListener('click', function (e) {
                var opener = e.target.closest('[data-grant-quota-open]');
                if (!opener) return;
                e.preventDefault();
                open();
            });
        }

        var closeBtn = document.getElementById('grant-quota-close');
        if (closeBtn && !closeBtn.dataset.lmBound) {
            closeBtn.dataset.lmBound = '1';
            closeBtn.addEventListener('click', function () { closeOverlay(overlay, dialog); });
        }
        var cancelBtn = document.getElementById('grant-quota-cancel');
        if (cancelBtn && !cancelBtn.dataset.lmBound) {
            cancelBtn.dataset.lmBound = '1';
            cancelBtn.addEventListener('click', function () { closeOverlay(overlay, dialog); });
        }
        if (!overlay.dataset.lmBackdrop) {
            overlay.dataset.lmBackdrop = '1';
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeOverlay(overlay, dialog);
            });
        }

        // 角色选择联动用户下拉（仅 admin 形态）
        var roleEl = document.getElementById('gq-role');
        var userContainer = document.getElementById('gq-user-container');

        function applyRoleVisibility(role) {
            if (!userContainer) return;
            var managerWrapper = document.getElementById('gq-user-manager-wrapper');
            var resellerWrapper = document.getElementById('gq-user-reseller-wrapper');
            if (!role) {
                userContainer.classList.add('hidden');
                return;
            }
            userContainer.classList.remove('hidden');
            if (managerWrapper) managerWrapper.classList.toggle('hidden', role !== 'manager');
            if (resellerWrapper) resellerWrapper.classList.toggle('hidden', role !== 'reseller');
        }

        if (config.showRoleSelect && roleEl && !roleEl.dataset.lmBound) {
            roleEl.dataset.lmBound = '1';
            roleEl.addEventListener('change', function () { applyRoleVisibility(roleEl.value); });
        }

        // 数量输入加减按钮（限定在本弹窗内）
        var qtyInput = document.getElementById('gq-quantity');
        if (dialog && qtyInput && !dialog.dataset.lmQty) {
            dialog.dataset.lmQty = '1';
            var minus = dialog.querySelector('.wk-input-number__btn--minus');
            var plus = dialog.querySelector('.wk-input-number__btn--plus');
            if (minus) {
                minus.addEventListener('click', function () {
                    var val = parseInt(qtyInput.value, 10) || 1;
                    var min = parseInt(qtyInput.getAttribute('min'), 10) || 1;
                    if (val > min) qtyInput.value = val - 1;
                });
            }
            if (plus) {
                plus.addEventListener('click', function () {
                    var val = parseInt(qtyInput.value, 10) || 1;
                    qtyInput.value = val + 1;
                });
            }
        }

        // 产品/时长变化 → 库存提示 + （manager 形态）自身剩余配额提示
        var productEl = document.getElementById('gq-product');
        var durationEl = document.getElementById('gq-duration');
        var inventoryHint = document.getElementById('gq-inventory-hint');
        var ownHint = document.getElementById('gq-own-hint');

        function refreshHints() {
            if (!productEl || !durationEl) return;
            var pid = productEl.value;
            var dur = durationEl.value;
            if (!pid || !dur) {
                if (inventoryHint) inventoryHint.textContent = '';
                if (ownHint) ownHint.textContent = '';
                return;
            }
            fetchInventory(pid, dur, function (count) {
                if (!inventoryHint) return;
                inventoryHint.textContent = count === null
                    ? ''
                    : 'Inventory available: ' + count + ' key(s) for this product & duration.';
            });
            if (ownHint) {
                var ownRemaining = config.ownQuotas ? config.ownQuotas[pid + '|' + dur] : undefined;
                if (typeof ownRemaining === 'undefined') {
                    ownHint.textContent = 'You have no quota for this product & duration.';
                } else {
                    ownHint.textContent = 'Your remaining quota: ' + ownRemaining;
                }
            }
        }

        [productEl, durationEl].forEach(function (el) {
            if (el && !el.dataset.lmBound) {
                el.dataset.lmBound = '1';
                el.addEventListener('change', refreshHints);
            }
        });

        // 提交 → create_allocation（权限与划转由后端权限矩阵决定）
        var form = document.getElementById('grant-quota-form');
        if (form && !form.dataset.lmBound) {
            form.dataset.lmBound = '1';
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var role = config.showRoleSelect && roleEl ? roleEl.value : (config.fixedRole || '');
                var userId = '';
                if (role === 'manager') {
                    var m = document.getElementById('gq-manager');
                    userId = m ? m.value : '';
                } else if (role === 'reseller') {
                    var r = document.getElementById('gq-reseller');
                    userId = r ? r.value : '';
                }
                var productId = productEl ? productEl.value : '';
                var duration = durationEl ? durationEl.value : '';
                var quantity = qtyInput ? qtyInput.value : '';

                if (!role || !userId || !productId || !duration || !quantity || parseInt(quantity, 10) <= 0) {
                    toast('Please fill in all fields', 'error');
                    return;
                }

                // manager 划转形态：客户端先行校验自身剩余配额（服务端仍会强制校验）
                if (config.fromOwnQuota && config.ownQuotas) {
                    var ownRemaining = config.ownQuotas[productId + '|' + duration];
                    if (typeof ownRemaining === 'undefined' || parseInt(quantity, 10) > ownRemaining) {
                        toast('Quantity exceeds your remaining quota for this product & duration', 'error');
                        return;
                    }
                }

                var body = 'action=create_allocation' +
                    '&user_id=' + encodeURIComponent(userId) +
                    '&product_id=' + encodeURIComponent(productId) +
                    '&duration_days=' + encodeURIComponent(duration) +
                    '&quantity=' + encodeURIComponent(quantity);

                var submitBtn = form.querySelector('button[type="submit"]');
                LicenseModuleUI.postWithFeedback(submitBtn, body).then(function (data) {
                    if (!data) return;
                    if (data.success) {
                        toast(data.message || 'Quota granted successfully', 'success');
                        closeOverlay(overlay, dialog);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toast(data.message || 'Failed to grant quota', 'error');
                    }
                });
            });
        }
    }

    // ============================================================
    // Create License（领取钥匙）弹窗 —— manager / reseller 共用
    // ============================================================
    function initClaimModal() {
        var overlay = document.getElementById('claim-keys-overlay');
        if (!overlay || overlay.dataset.lmBound) return;
        overlay.dataset.lmBound = '1';
        var dialog = document.getElementById('claim-keys-dialog');

        var quotaSelect = document.getElementById('ck-quota');
        var prodEl = document.getElementById('ck-product');
        var durEl = document.getElementById('ck-duration');
        var remEl = document.getElementById('ck-remaining');
        var invEl = document.getElementById('ck-inventory');
        var qtyEl = document.getElementById('ck-quantity');
        var hintEl = document.getElementById('ck-hint');

        var state = { productId: 0, duration: 0, remaining: 0 };

        function applySelectedOption(option) {
            if (!option || !option.value) {
                state = { productId: 0, duration: 0, remaining: 0 };
                if (prodEl) prodEl.textContent = '-';
                if (durEl) durEl.textContent = '-';
                if (remEl) remEl.textContent = '-';
                if (invEl) invEl.textContent = '-';
                if (hintEl) hintEl.textContent = 'No available quota. Contact your admin to grant a quota.';
                return;
            }
            state.productId = parseInt(option.getAttribute('data-product-id'), 10) || 0;
            state.duration = parseInt(option.getAttribute('data-duration'), 10) || 0;
            state.remaining = parseInt(option.getAttribute('data-remaining'), 10) || 0;

            if (prodEl) prodEl.textContent = option.getAttribute('data-product-name') || '-';
            if (durEl) durEl.textContent = option.getAttribute('data-duration-label') || '-';
            if (remEl) remEl.textContent = state.remaining;
            if (invEl) invEl.textContent = '-';
            if (hintEl) hintEl.textContent = '';
            if (qtyEl) qtyEl.value = '';

            if (state.productId && state.duration) {
                fetchInventory(state.productId, state.duration, function (count) {
                    if (count === null) return;
                    if (invEl) invEl.textContent = count;
                    if (hintEl) hintEl.textContent = 'You can claim up to ' + Math.min(state.remaining, count) + ' key(s).';
                });
            }
        }

        function findOption(productId, duration) {
            if (!quotaSelect) return null;
            var target = productId + '|' + duration;
            var options = quotaSelect.querySelectorAll('option');
            for (var i = 0; i < options.length; i++) {
                if (options[i].value === target) return options[i];
            }
            return null;
        }

        function open(preselectProductId, preselectDuration) {
            var option = null;
            if (preselectProductId && preselectDuration) {
                option = findOption(preselectProductId, preselectDuration);
            }
            if (!option && quotaSelect) {
                // 默认选第一个剩余量 > 0 的配额
                option = quotaSelect.querySelector('option[value]:not([value=""])');
            }
            if (quotaSelect) quotaSelect.value = option ? option.value : '';
            applySelectedOption(option);
            openOverlay(overlay, dialog);
        }

        // 入口：配额卡片上的 Claim Keys 按钮 + 面板上的 Create License 按钮（document 级委托）
        if (!LicenseModuleUI._claimDelegated) {
            LicenseModuleUI._claimDelegated = true;
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.btn-claim-keys, #create-license-btn');
                if (!btn || btn.disabled) return;
                e.preventDefault();
                if (btn.id === 'create-license-btn') {
                    open();
                } else {
                    open(btn.getAttribute('data-product-id'), btn.getAttribute('data-duration'));
                }
            });
        }

        var closeBtn = document.getElementById('claim-keys-close');
        if (closeBtn && !closeBtn.dataset.lmBound) {
            closeBtn.dataset.lmBound = '1';
            closeBtn.addEventListener('click', function () { closeOverlay(overlay, dialog); });
        }
        var cancelBtn = document.getElementById('claim-keys-cancel');
        if (cancelBtn && !cancelBtn.dataset.lmBound) {
            cancelBtn.dataset.lmBound = '1';
            cancelBtn.addEventListener('click', function () { closeOverlay(overlay, dialog); });
        }
        if (!overlay.dataset.lmBackdrop) {
            overlay.dataset.lmBackdrop = '1';
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeOverlay(overlay, dialog);
            });
        }

        // 配额切换
        if (quotaSelect && !quotaSelect.dataset.lmBound) {
            quotaSelect.dataset.lmBound = '1';
            quotaSelect.addEventListener('change', function () {
                applySelectedOption(quotaSelect.options[quotaSelect.selectedIndex]);
            });
        }

        // 提交 → claim_keys
        var submitBtn = document.getElementById('claim-keys-submit');
        if (submitBtn && !submitBtn.dataset.lmBound) {
            submitBtn.dataset.lmBound = '1';
            submitBtn.addEventListener('click', function () {
                var qty = qtyEl ? parseInt(qtyEl.value, 10) : 0;
                if (!state.productId || !state.duration) {
                    toast('Please select a quota first', 'error');
                    return;
                }
                if (!qty || qty <= 0) {
                    toast('Please enter a valid quantity', 'error');
                    return;
                }
                if (qty > state.remaining) {
                    toast('Quantity exceeds your remaining quota', 'error');
                    return;
                }

                var body = 'action=claim_keys' +
                    '&product_id=' + state.productId +
                    '&duration_days=' + state.duration +
                    '&quantity=' + qty;

                LicenseModuleUI.postWithFeedback(submitBtn, body).then(function (data) {
                    if (!data) return;
                    if (data.success) {
                        toast(data.message || 'Keys claimed successfully', 'success');
                        closeOverlay(overlay, dialog);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toast(data.message || 'Claim failed', 'error');
                    }
                });
            });
        }
    }

    /** 初始化全部许可证模块交互（幂等，可重复调用） */
    LicenseModuleUI.initAll = function () {
        initGrantQuotaModal();
        initClaimModal();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { LicenseModuleUI.initAll(); });
    } else {
        LicenseModuleUI.initAll();
    }

    window.LicenseModuleUI = LicenseModuleUI;
})();
