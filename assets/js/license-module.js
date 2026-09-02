/**
 * ============================================================
 * LicenseModule UI - 许可证模块前端交互层（可复用）
 * ============================================================
 * 与后端 includes/services/LicenseModule.php 配套，负责两类共享弹窗：
 *   1. Create License（领取钥匙）卡片 —— manager.php / reseller.php 共用
 *      - 入口：配额卡片上的 .btn-claim-keys 按钮、许可证列表头部的 #create-license-btn
 *      - 弹窗 DOM 由 section-helpers.php 的 renderClaimKeysModal() 渲染
 *   2. Grant Quota（授权配额）卡片 —— admin.php / manager.php 通过 data-gq-config 配置复用
 *      - 入口：带 data-grant-quota-open 属性的按钮（许可证列表头部）
 *      - 弹窗 DOM 由 renderGrantQuotaModal() 渲染，运行时配置注入在 overlay 的 data-gq-config
 *
 * 设计约定：
 *   - 本模块不做权限判断（权限由后端权限矩阵决定），只做交互与即时反馈
 *   - 所有提交走 postWithFeedback：按钮 loading + 防重复点击 + 30s 超时提示
 *   - 所有事件处理器在触发时实时解析 DOM 元素（resolve*Els），
 *     不闭包持有节点引用 —— hash 路由重复注入页面片段、节点被替换后依然有效
 *   - 依赖 window.showToast（app.js 提供）；缺失时静默降级
 */
(function () {
    'use strict';

    // 当前模块版本（用户可在浏览器控制台输入 window.__lmVersion 自查加载的是否为新版）
    window.__lmVersion = '20260903';

    var LicenseModuleUI = {};

    // ---------- 表单状态（模块级单例，跨节点替换保持一致） ----------
    var claimState = { productId: 0, duration: 0, remaining: 0 };
    var grantState = { config: { showRoleSelect: false, fixedRole: null, fromOwnQuota: false, ownQuotas: {} } };

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
    function resolveGrantEls() {
        return {
            overlay: document.getElementById('grant-quota-overlay'),
            dialog: document.getElementById('grant-quota-dialog'),
            form: document.getElementById('grant-quota-form'),
            roleEl: document.getElementById('gq-role'),
            productEl: document.getElementById('gq-product'),
            durationEl: document.getElementById('gq-duration'),
            inventoryHint: document.getElementById('gq-inventory-hint'),
            ownHint: document.getElementById('gq-own-hint'),
            qtyInput: document.getElementById('gq-quantity')
        };
    }

    /** 读取服务端注入在 overlay 上的运行时配置（data-gq-config） */
    function readGrantConfig(overlay) {
        var config = { showRoleSelect: false, fixedRole: null, fromOwnQuota: false, ownQuotas: {} };
        try {
            var raw = overlay ? overlay.getAttribute('data-gq-config') : null;
            if (raw) {
                var parsed = JSON.parse(raw);
                Object.keys(parsed).forEach(function (k) { config[k] = parsed[k]; });
            }
        } catch (e) { /* 配置缺失时按默认行为运行 */ }
        return config;
    }

    function applyGrantRoleVisibility(role) {
        var userContainer = document.getElementById('gq-user-container');
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

    function refreshGrantHints() {
        var els = resolveGrantEls();
        var config = grantState.config;
        if (!els.productEl || !els.durationEl) return;
        var pid = els.productEl.value;
        var dur = els.durationEl.value;
        if (!pid || !dur) {
            if (els.inventoryHint) els.inventoryHint.textContent = '';
            if (els.ownHint) els.ownHint.textContent = '';
            return;
        }
        fetchInventory(pid, dur, function (count) {
            if (!els.inventoryHint) return;
            els.inventoryHint.textContent = count === null
                ? ''
                : 'Inventory available: ' + count + ' key(s) for this product & duration.';
        });
        if (els.ownHint) {
            var ownRemaining = config.ownQuotas ? config.ownQuotas[pid + '|' + dur] : undefined;
            if (typeof ownRemaining === 'undefined') {
                els.ownHint.textContent = 'You have no quota for this product & duration.';
            } else {
                els.ownHint.textContent = 'Your remaining quota: ' + ownRemaining;
            }
        }
    }

    /** 打开授权弹窗（实时解析 DOM，可安全重复调用） */
    LicenseModuleUI.openGrantModal = function () {
        var overlay = document.getElementById('grant-quota-overlay');
        var dialog = document.getElementById('grant-quota-dialog');
        if (!overlay || !dialog) return;
        grantState.config = readGrantConfig(overlay);
        var roleEl = document.getElementById('gq-role');
        if (grantState.config.showRoleSelect && roleEl) {
            applyGrantRoleVisibility(roleEl.value);
        }
        refreshGrantHints();
        openOverlay(overlay, dialog);
    };

    function initGrantQuotaModal() {
        var overlay = document.getElementById('grant-quota-overlay');
        if (!overlay || overlay.dataset.lmBound) return;
        overlay.dataset.lmBound = '1';
        var dialog = document.getElementById('grant-quota-dialog');

        // 多入口打开（admin: #grant-quota-btn；manager: 许可证列表头部按钮）
        // document 级事件委托只注册一次；打开时通过 openGrantModal 实时解析节点，
        // 对动态渲染的按钮、脚本加载时序、hash 路由重复注入都免疫
        if (!LicenseModuleUI._openersDelegated) {
            LicenseModuleUI._openersDelegated = true;
            document.addEventListener('click', function (e) {
                var opener = e.target.closest('[data-grant-quota-open]');
                if (!opener) return;
                e.preventDefault();
                LicenseModuleUI.openGrantModal();
            });
        }

        var closeBtn = document.getElementById('grant-quota-close');
        if (closeBtn && !closeBtn.dataset.lmBound) {
            closeBtn.dataset.lmBound = '1';
            closeBtn.addEventListener('click', function () {
                closeOverlay(document.getElementById('grant-quota-overlay'), document.getElementById('grant-quota-dialog'));
            });
        }
        var cancelBtn = document.getElementById('grant-quota-cancel');
        if (cancelBtn && !cancelBtn.dataset.lmBound) {
            cancelBtn.dataset.lmBound = '1';
            cancelBtn.addEventListener('click', function () {
                closeOverlay(document.getElementById('grant-quota-overlay'), document.getElementById('grant-quota-dialog'));
            });
        }
        if (!overlay.dataset.lmBackdrop) {
            overlay.dataset.lmBackdrop = '1';
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    closeOverlay(document.getElementById('grant-quota-overlay'), document.getElementById('grant-quota-dialog'));
                }
            });
        }

        // 角色选择联动用户下拉（仅 admin 形态）
        var roleEl = document.getElementById('gq-role');
        if (roleEl && !roleEl.dataset.lmBound) {
            roleEl.dataset.lmBound = '1';
            roleEl.addEventListener('change', function () {
                applyGrantRoleVisibility(roleEl.value);
            });
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
        [productEl, durationEl].forEach(function (el) {
            if (el && !el.dataset.lmBound) {
                el.dataset.lmBound = '1';
                el.addEventListener('change', refreshGrantHints);
            }
        });

        // 提交 → create_allocation（权限与划转由后端权限矩阵决定）
        var form = document.getElementById('grant-quota-form');
        if (form && !form.dataset.lmBound) {
            form.dataset.lmBound = '1';
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var els = resolveGrantEls();
                var config = grantState.config;

                var role = config.showRoleSelect && els.roleEl ? els.roleEl.value : (config.fixedRole || '');
                var userId = '';
                if (role === 'manager') {
                    var m = document.getElementById('gq-manager');
                    userId = m ? m.value : '';
                } else if (role === 'reseller') {
                    var r = document.getElementById('gq-reseller');
                    userId = r ? r.value : '';
                }
                var productId = els.productEl ? els.productEl.value : '';
                var duration = els.durationEl ? els.durationEl.value : '';
                var quantity = els.qtyInput ? els.qtyInput.value : '';

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

                var submitBtn = els.form ? els.form.querySelector('button[type="submit"]') : null;
                LicenseModuleUI.postWithFeedback(submitBtn, body).then(function (data) {
                    if (!data) return;
                    if (data.success) {
                        toast(data.message || 'Quota granted successfully', 'success');
                        closeOverlay(document.getElementById('grant-quota-overlay'), document.getElementById('grant-quota-dialog'));
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
    function resolveClaimEls() {
        return {
            overlay: document.getElementById('claim-keys-overlay'),
            dialog: document.getElementById('claim-keys-dialog'),
            quotaSelect: document.getElementById('ck-quota'),
            prodEl: document.getElementById('ck-product'),
            durEl: document.getElementById('ck-duration'),
            remEl: document.getElementById('ck-remaining'),
            invEl: document.getElementById('ck-inventory'),
            qtyEl: document.getElementById('ck-quantity'),
            hintEl: document.getElementById('ck-hint')
        };
    }

    function findClaimOption(quotaSelect, productId, durationDays) {
        if (!quotaSelect) return null;
        var target = productId + '|' + durationDays;
        var options = quotaSelect.querySelectorAll('option');
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === target) return options[i];
        }
        return null;
    }

    function applyClaimSelection(option) {
        var els = resolveClaimEls();
        if (!option || !option.value) {
            claimState = { productId: 0, duration: 0, remaining: 0 };
            if (els.prodEl) els.prodEl.textContent = '-';
            if (els.durEl) els.durEl.textContent = '-';
            if (els.remEl) els.remEl.textContent = '-';
            if (els.invEl) els.invEl.textContent = '-';
            if (els.hintEl) els.hintEl.textContent = 'No available quota. Contact your admin to grant a quota.';
            return;
        }
        claimState.productId = parseInt(option.getAttribute('data-product-id'), 10) || 0;
        claimState.duration = parseInt(option.getAttribute('data-duration'), 10) || 0;
        claimState.remaining = parseInt(option.getAttribute('data-remaining'), 10) || 0;

        if (els.prodEl) els.prodEl.textContent = option.getAttribute('data-product-name') || '-';
        if (els.durEl) els.durEl.textContent = option.getAttribute('data-duration-label') || '-';
        if (els.remEl) els.remEl.textContent = claimState.remaining;
        if (els.invEl) els.invEl.textContent = '-';
        if (els.hintEl) els.hintEl.textContent = '';
        if (els.qtyEl) els.qtyEl.value = '';

        if (claimState.productId && claimState.duration) {
            fetchInventory(claimState.productId, claimState.duration, function (count) {
                if (count === null) return;
                if (els.invEl) els.invEl.textContent = count;
                if (els.hintEl) els.hintEl.textContent = 'You can claim up to ' + Math.min(claimState.remaining, count) + ' key(s).';
            });
        }
    }

    /** 打开领取弹窗（实时解析 DOM，可安全重复调用） */
    LicenseModuleUI.openClaimModal = function (preselectProductId, preselectDuration) {
        var els = resolveClaimEls();
        if (!els.overlay || !els.dialog) return;
        var option = null;
        if (preselectProductId && preselectDuration) {
            option = findClaimOption(els.quotaSelect, preselectProductId, preselectDuration);
        }
        if (!option && els.quotaSelect) {
            // 默认选第一个剩余量 > 0 的配额
            option = els.quotaSelect.querySelector('option[value]:not([value=""])');
        }
        if (els.quotaSelect) els.quotaSelect.value = option ? option.value : '';
        applyClaimSelection(option);
        openOverlay(els.overlay, els.dialog);
    };

    function initClaimModal() {
        var overlay = document.getElementById('claim-keys-overlay');
        if (!overlay || overlay.dataset.lmBound) return;
        overlay.dataset.lmBound = '1';
        var dialog = document.getElementById('claim-keys-dialog');

        // 入口：许可证列表头部的 Create License 按钮 + （若渲染）配额卡片上的 Claim Keys
        // document 级委托只注册一次；打开时通过 openClaimModal 实时解析节点
        if (!LicenseModuleUI._claimDelegated) {
            LicenseModuleUI._claimDelegated = true;
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.btn-claim-keys, #create-license-btn');
                if (!btn || btn.disabled) return;
                e.preventDefault();
                LicenseModuleUI.openClaimModal(btn.getAttribute('data-product-id'), btn.getAttribute('data-duration'));
            });
        }

        var closeBtn = document.getElementById('claim-keys-close');
        if (closeBtn && !closeBtn.dataset.lmBound) {
            closeBtn.dataset.lmBound = '1';
            closeBtn.addEventListener('click', function () {
                closeOverlay(document.getElementById('claim-keys-overlay'), document.getElementById('claim-keys-dialog'));
            });
        }
        var cancelBtn = document.getElementById('claim-keys-cancel');
        if (cancelBtn && !cancelBtn.dataset.lmBound) {
            cancelBtn.dataset.lmBound = '1';
            cancelBtn.addEventListener('click', function () {
                closeOverlay(document.getElementById('claim-keys-overlay'), document.getElementById('claim-keys-dialog'));
            });
        }
        if (!overlay.dataset.lmBackdrop) {
            overlay.dataset.lmBackdrop = '1';
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    closeOverlay(document.getElementById('claim-keys-overlay'), document.getElementById('claim-keys-dialog'));
                }
            });
        }

        // 配额切换
        var quotaSelect = document.getElementById('ck-quota');
        if (quotaSelect && !quotaSelect.dataset.lmBound) {
            quotaSelect.dataset.lmBound = '1';
            quotaSelect.addEventListener('change', function () {
                applyClaimSelection(quotaSelect.options[quotaSelect.selectedIndex]);
            });
        }

        // 提交 → claim_keys
        var submitBtn = document.getElementById('claim-keys-submit');
        if (submitBtn && !submitBtn.dataset.lmBound) {
            submitBtn.dataset.lmBound = '1';
            submitBtn.addEventListener('click', function () {
                var qtyEl = document.getElementById('ck-quantity');
                var qty = qtyEl ? parseInt(qtyEl.value, 10) : 0;
                if (!claimState.productId || !claimState.duration) {
                    toast('Please select a quota first', 'error');
                    return;
                }
                if (!qty || qty <= 0) {
                    toast('Please enter a valid quantity', 'error');
                    return;
                }
                if (qty > claimState.remaining) {
                    toast('Quantity exceeds your remaining quota', 'error');
                    return;
                }

                var body = 'action=claim_keys' +
                    '&product_id=' + claimState.productId +
                    '&duration_days=' + claimState.duration +
                    '&quantity=' + qty;

                LicenseModuleUI.postWithFeedback(submitBtn, body).then(function (data) {
                    if (!data) return;
                    if (data.success) {
                        toast(data.message || 'Keys claimed successfully', 'success');
                        closeOverlay(document.getElementById('claim-keys-overlay'), document.getElementById('claim-keys-dialog'));
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
