/**
 * WenKing App - 应用核心 JavaScript
 * 包含路由管理、AJAX 加载、状态管理、侧边栏控制
 */

// 全局模态框函数 - 确保在任何情况下都可用
window.openProductModal = function(resetForm) {
    var overlay = document.getElementById('product-edit-overlay');
    var dialog = document.getElementById('product-edit-dialog');

    if (overlay && dialog) {
        // 获取 PageLoader 实例并调用重置表单
        var loader = window.appInstance ? window.appInstance.pageLoader : null;
        if (resetForm !== false && loader && loader._resetAdminForm) {
            loader._resetAdminForm();
        }
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        setTimeout(function() {
            dialog.classList.remove('scale-95', 'opacity-0');
            dialog.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
};

window.closeProductModal = function() {
    var overlay = document.getElementById('product-edit-overlay');
    var dialog = document.getElementById('product-edit-dialog');
    
    if (overlay && dialog) {
        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');
        overlay.classList.remove('flex');
        overlay.classList.add('hidden');
    }
};

(function () {
    'use strict';

    // ============================================
    // 1. AppState - 状态管理（发布-订阅模式）
    // ============================================
    class AppState {
        constructor() {
            this.currentPage = 'dashboard';
            this.isLoading = false;
            this.sidebarOpen = false;
            this._listeners = [];
        }

        subscribe(callback) {
            this._listeners.push(callback);
        }

        setState(updates) {
            Object.assign(this, updates);
            this._listeners.forEach(function (cb) {
                cb(this);
            }.bind(this));
        }
    }

    // ============================================
    // 2. Router - Hash 路由管理
    // ============================================
    class Router {
        constructor() {
            this.routes = {
                '/dashboard': 'dashboard',
                '/settings': 'settings',
                '/downloads': 'downloads',
                '/redeem': 'redeem',
                '/reseller': 'reseller',
                '/manager': 'manager',
                '/admin': 'admin',
            };
            this.defaultRoute = 'dashboard';
        }

        getPageFromHash() {
            var hash = window.location.hash.slice(1) || '/dashboard';
            return this.routes[hash] || this.defaultRoute;
        }

        navigateTo(page) {
            window.location.hash = '#/' + page;
        }
    }

    // ============================================
    // 3. PageLoader - AJAX 页面加载
    // ============================================
    class PageLoader {
        constructor(container) {
            this.container = container;
            this.abortController = null;
        }

        async loadPage(page) {
            // 取消之前的请求
            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();

            // 淡出当前内容
            this.container.classList.add('page-exit');
            await this._wait(200);

            // 显示加载指示器
            this.container.innerHTML = '<div class="page-loading"><div class="spinner"></div></div>';

            try {
                // 发起 AJAX 请求
                var response = await fetch(
                    'api/router.php?page=' + page,
                    { signal: this.abortController.signal }
                );

                if (!response.ok) {
                    throw new Error('Failed to load page');
                }

                var html = await response.text();
                this.container.innerHTML = html;

                // 移除退出动画，添加进入动画
                this.container.classList.remove('page-exit');
                this.container.classList.add('page-enter');

                // 重新初始化页面内的交互
                this._initPageScripts();

                // 动画结束后移除类名
                var self = this;
                setTimeout(function () {
                    self.container.classList.remove('page-enter');
                }, 300);

            } catch (error) {
                if (error.name !== 'AbortError') {
                    this.container.innerHTML = '<div class="text-center py-12"><p class="text-white/60">Failed to load page. Please try again.</p></div>';
                }
            }
        }

        _initPageScripts() {
            // 重新初始化滚动动画观察器
            this._initScrollAnimations();

            // 初始化 Tab 切换功能
            this._initTabs();

            // 初始化 Redeem 页面功能
            this._initRedeemPage();

            // 初始化密码强度检测
            this._initPasswordStrength();

            // 初始化 Admin 页面功能
            this._initAdminPage();
        }

        _initTabs() {
            var allPanels = document.querySelectorAll('.tab-panel');

            // Reseller 页面 Tab 切换
            var resellerTabs = document.querySelectorAll('.reseller-tab');
            resellerTabs.forEach(function(tab) {
                if (tab.dataset.tabBound) return;
                tab.dataset.tabBound = '1';
                tab.addEventListener('click', function() {
                    var targetTab = this.getAttribute('data-tab');
                    resellerTabs.forEach(function(t) {
                        t.classList.remove('active', 'text-white', 'border-accent-purple');
                        t.classList.add('text-white/70', 'border-transparent');
                    });
                    this.classList.add('active', 'text-white', 'border-accent-purple');
                    this.classList.remove('text-white/70', 'border-transparent');
                    allPanels.forEach(function(panel) {
                        panel.classList.remove('active');
                    });
                    var targetPanel = document.getElementById(targetTab + '-tab');
                    if (targetPanel) {
                        targetPanel.classList.add('active');
                    }
                });
            });

            // Manager 页面 Tab 切换
            var managerTabs = document.querySelectorAll('.manager-tab');
            managerTabs.forEach(function(tab) {
                if (tab.dataset.tabBound) return;
                tab.dataset.tabBound = '1';
                tab.addEventListener('click', function() {
                    var targetTab = this.getAttribute('data-tab');
                    managerTabs.forEach(function(t) {
                        t.classList.remove('active', 'text-white', 'border-accent-purple');
                        t.classList.add('text-white/70', 'border-transparent');
                    });
                    this.classList.add('active', 'text-white', 'border-accent-purple');
                    this.classList.remove('text-white/70', 'border-transparent');
                    allPanels.forEach(function(panel) {
                        panel.classList.remove('active');
                    });
                    var targetPanel = document.getElementById(targetTab + '-tab');
                    if (targetPanel) {
                        targetPanel.classList.add('active');
                    }
                });
            });
        }

        _initRedeemPage() {
            var btn = document.getElementById('redeem-btn');
            var input = document.getElementById('redeem-code-input');
            var toast = document.getElementById('redeem-toast');
            var toastMsg = document.getElementById('redeem-toast-message');
            var toastTitle = document.getElementById('redeem-toast-title');
            var toastIcon = document.getElementById('redeem-toast-icon');
            var toastClose = document.getElementById('redeem-toast-close');

            // 如果不在 Redeem 页面，跳过
            if (!btn || !input || !toast) return;

            var toastTimer = null;

            // 图标 SVG
            var icons = {
                success: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                error: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
            };

            // 显示提示框
            function showToast(message, type) {
                type = type || 'success';
                
                // 根据类型设置显示时间
                var duration;
                var title;
                switch(type) {
                    case 'success':
                        duration = 5000;
                        title = 'Success';
                        break;
                    case 'warning':
                        duration = 5000;
                        title = 'Warning';
                        break;
                    case 'error':
                        duration = 5000;
                        title = 'Error';
                        break;
                    default:
                        duration = 5000;
                        title = 'Success';
                }

                if (toastTimer) {
                    clearTimeout(toastTimer);
                }

                // 移除所有类型类
                toast.classList.remove('toast-success', 'toast-warning', 'toast-error');
                // 添加当前类型类
                toast.classList.add('toast-' + type);

                // 设置标题和消息
                toastTitle.textContent = title;
                toastMsg.textContent = message;
                
                // 设置图标
                toastIcon.innerHTML = icons[type] || icons.success;

                // 设置 CSS 变量用于动画时长
                toast.style.setProperty('--toast-duration', duration + 'ms');

                // 重置动画
                toast.classList.remove('show');
                void toast.offsetWidth;

                // 设置进度条动画时长
                var progress = toast.querySelector('.redeem-toast-progress');
                if (progress) {
                    progress.style.animationDuration = duration + 'ms';
                }

                toast.classList.add('show');

                toastTimer = setTimeout(function() {
                    hideToast();
                }, duration);
            }

            // 隐藏提示框
            function hideToast() {
                toast.classList.remove('show');
                if (toastTimer) {
                    clearTimeout(toastTimer);
                    toastTimer = null;
                }
            }

            // 关闭按钮
            if (toastClose) {
                toastClose.addEventListener('click', hideToast);
            }

            // Redeem 按钮
            btn.addEventListener('click', function() {
                var code = input.value.trim();

                if (!code) {
                    showToast('Please enter a redemption code.', 'warning');
                    return;
                }

                if (code === '1') {
                    showToast('Code "1" redeemed successfully! Your subscription is now active.', 'success');
                    input.value = '';
                } else {
                    showToast('Invalid code. Please try again.', 'error');
                }
            });
        }

        _initPasswordStrength() {
            var passwordInput = document.getElementById('register-password');
            var confirmInput = document.getElementById('register-confirm');
            var strengthContainer = document.getElementById('password-strength');
            var strengthText = document.getElementById('strength-text');
            var strengthTips = document.getElementById('strength-tips');
            var matchContainer = document.getElementById('password-match');
            var matchIcon = document.getElementById('match-icon');
            var matchText = document.getElementById('match-text');
            
            var bars = [
                document.getElementById('strength-bar-1'),
                document.getElementById('strength-bar-2'),
                document.getElementById('strength-bar-3'),
                document.getElementById('strength-bar-4')
            ];

            if (!passwordInput || !strengthContainer) return;

            // 密码强度检测函数
            function checkPasswordStrength(password) {
                var strength = 0;
                var tips = [];

                if (!password) {
                    return { strength: 0, tips: [] };
                }

                // 长度检查
                if (password.length >= 8) {
                    strength++;
                } else {
                    tips.push('至少8个字符');
                }

                // 小写字母检查
                if (/[a-z]/.test(password)) {
                    strength++;
                } else {
                    tips.push('添加小写字母');
                }

                // 大写字母检查
                if (/[A-Z]/.test(password)) {
                    strength++;
                } else {
                    tips.push('添加大写字母');
                }

                // 数字检查
                if (/[0-9]/.test(password)) {
                    strength++;
                } else {
                    tips.push('添加数字');
                }

                // 特殊字符检查
                if (/[^a-zA-Z0-9]/.test(password)) {
                    strength++;
                } else {
                    tips.push('添加特殊字符');
                }

                // 根据得分调整强度等级
                var level;
                if (strength <= 1) {
                    level = 1; // 非常弱
                } else if (strength === 2) {
                    level = 2; // 弱
                } else if (strength === 3 || strength === 4) {
                    level = 3; // 中等
                } else {
                    level = 4; // 强
                }

                return { strength: level, tips: tips };
            }

            // 更新强度显示
            function updateStrengthDisplay(result) {
                var level = result.strength;
                var tips = result.tips;

                // 显示/隐藏容器
                if (passwordInput.value) {
                    strengthContainer.classList.remove('hidden');
                } else {
                    strengthContainer.classList.add('hidden');
                    return;
                }

                // 更新进度条
                bars.forEach(function(bar, index) {
                    // 移除所有等级类
                    bar.classList.remove('active', 'level-1', 'level-2', 'level-3', 'level-4');

                    if (index < level) {
                        bar.classList.add('active', 'level-' + level);
                    }
                });

                // 更新文本
                var strengthLabels = ['', 'Very Weak', 'Weak', 'Good', 'Strong'];
                strengthText.textContent = strengthLabels[level] || '';
                strengthText.className = 'password-strength-text level-' + level;

                // 更新提示
                if (tips.length > 0 && level < 4) {
                    strengthTips.textContent = '建议: ' + tips.slice(0, 2).join(', ');
                } else {
                    strengthTips.textContent = level === 4 ? '密码强度很好!' : '';
                }
            }

            // 密码匹配检测函数
            function checkPasswordMatch() {
                if (!confirmInput || !matchContainer) return;

                var password = passwordInput.value;
                var confirm = confirmInput.value;

                if (!confirm) {
                    matchContainer.classList.add('hidden');
                    return;
                }

                matchContainer.classList.remove('hidden');

                if (password === confirm) {
                    matchIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                    matchIcon.className = 'password-match-icon match';
                    matchText.textContent = '密码匹配';
                    matchText.className = 'password-match-text match';
                } else {
                    matchIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                    matchIcon.className = 'password-match-icon no-match';
                    matchText.textContent = '密码不匹配';
                    matchText.className = 'password-match-text no-match';
                }
            }

            // 监听密码输入
            passwordInput.addEventListener('input', function() {
                var password = this.value;
                var result = checkPasswordStrength(password);
                updateStrengthDisplay(result);
                checkPasswordMatch();
            });

            // 监听确认密码输入
            if (confirmInput) {
                confirmInput.addEventListener('input', function() {
                    checkPasswordMatch();
                });
            }
        }

        _initAdminPage() {
            var self = this;
            var form = document.getElementById('product-form');

            // 绑定模态框关闭按钮事件（防止重复绑定）
            var closeBtn = document.getElementById('product-edit-close');
            if (closeBtn && !closeBtn.dataset.bound) {
                closeBtn.dataset.bound = '1';
                closeBtn.addEventListener('click', window.closeProductModal);
            }

            // 绑定取消按钮事件（防止重复绑定）
            var cancelBtn = document.getElementById('cancel-btn');
            if (cancelBtn && !cancelBtn.dataset.bound) {
                cancelBtn.dataset.bound = '1';
                cancelBtn.addEventListener('click', window.closeProductModal);
            }

            // 点击遮罩层关闭（防止重复绑定）
            var overlay = document.getElementById('product-edit-overlay');
            if (overlay && !overlay.dataset.bound) {
                overlay.dataset.bound = '1';
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        window.closeProductModal();
                    }
                });
            }

            // 如果没有 form，直接返回（但上面的模态框事件已经绑定）
            if (!form) return;

            // 表单提交（防止重复绑定）
            if (!form.dataset.bound) {
                form.dataset.bound = '1';
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var id = document.getElementById('edit-id').value;
                    var action = id ? 'update' : 'create';
                    var body = new URLSearchParams({
                        action: action,
                        name: document.getElementById('f-name').value,
                        tagline: document.getElementById('f-tagline').value,
                        description: document.getElementById('f-description').value,
                        status: document.getElementById('f-status').value,
                        image: document.getElementById('f-image').value,
                        button_text: document.getElementById('f-button-text').value,
                        button_link: document.getElementById('f-button-link').value,
                        features: document.getElementById('f-features').value,
                        sort_order: document.getElementById('f-sort-order').value,
                        is_visible: document.getElementById('f-is-visible').checked ? 1 : 0,
                    });
                    if (id) body.append('id', id);

                    fetch(window.SITE_URL + '/api/products.php', {
                        method: 'POST',
                        body: body
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            if (typeof showToast !== 'undefined') {
                                showToast(id ? 'Product updated successfully' : 'Product added successfully', 'success');
                            }
                            setTimeout(function() { location.reload(); }, 800);
                        } else {
                            if (typeof showToast !== 'undefined') {
                                showToast(data.message || 'Operation failed', 'error');
                            } else {
                                alert(data.message || 'Operation failed');
                            }
                        }
                    })
                    .catch(function() {
                        if (typeof showToast !== 'undefined') {
                            showToast('Network error', 'error');
                        } else {
                            alert('Network error');
                        }
                    });
                });
            }

            // 事件委托：只绑定一次到 document（全局标志）
            if (!window._adminDocEventsBound) {
                window._adminDocEventsBound = true;
                document.addEventListener('click', function(e) {
                    var appLoader = window.appInstance ? window.appInstance.pageLoader : null;
                    var editBtn = e.target.closest('.btn-admin-edit');
                    if (editBtn) {
                        e.preventDefault();
                        var productData = JSON.parse(editBtn.getAttribute('data-product'));
                        if (appLoader && appLoader._editAdminProduct) {
                            appLoader._editAdminProduct(productData);
                        }
                    }

                    var deleteBtn = e.target.closest('.btn-admin-delete');
                    if (deleteBtn) {
                        e.preventDefault();
                        var id = deleteBtn.getAttribute('data-id');
                        if (appLoader && appLoader._deleteAdminProduct) {
                            appLoader._deleteAdminProduct(id, deleteBtn);
                        }
                    }

                    var cancelBtnEl = e.target.closest('#cancel-btn');
                    if (cancelBtnEl) {
                        e.preventDefault();
                        if (appLoader && appLoader._resetAdminForm) {
                            appLoader._resetAdminForm();
                        }
                    }

                    // 用户状态切换
                    var toggleBtn = e.target.closest('.btn-user-toggle');
                    if (toggleBtn) {
                        e.preventDefault();
                        var uid = toggleBtn.getAttribute('data-id');
                        var curStatus = toggleBtn.getAttribute('data-status');
                        var newStatus = curStatus === 'active' ? 'inactive' : 'active';
                        if (appLoader && appLoader._toggleUserStatus) {
                            appLoader._toggleUserStatus(uid, newStatus, toggleBtn);
                        }
                    }

                    // 用户删除
                    var userDeleteBtn = e.target.closest('.btn-user-delete');
                    if (userDeleteBtn) {
                        e.preventDefault();
                        var uid2 = userDeleteBtn.getAttribute('data-id');
                        if (appLoader && appLoader._deleteUser) {
                            appLoader._deleteUser(uid2, userDeleteBtn);
                        }
                    }

                    // 用户编辑
                    var userEditBtn = e.target.closest('.btn-user-edit');
                    if (userEditBtn) {
                        e.preventDefault();
                        var userData = JSON.parse(userEditBtn.getAttribute('data-user'));
                        if (appLoader && appLoader._openUserEditModal) {
                            appLoader._openUserEditModal(userData);
                        }
                    }

                    // 关闭编辑弹窗
                    var closeBtnEl = e.target.closest('#user-edit-close') || e.target.closest('#user-edit-cancel');
                    if (closeBtnEl) {
                        e.preventDefault();
                        if (appLoader && appLoader._closeUserEditModal) {
                            appLoader._closeUserEditModal();
                        }
                    }
                });
            }

            // 初始化用户搜索
            self._initUserSearch();

            // 初始化用户排序（默认按 ID 升序）
            self._initUserSort();

            // 初始化用户编辑表单
            self._initUserEditForm();
        }

        _toggleUserStatus(id, newStatus, triggerBtn) {
            if (triggerBtn) {
                triggerBtn.classList.add('is-loading');
            }

            fetch(window.SITE_URL + '/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=update_user_status&id=' + id + '&status=' + newStatus
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    if (typeof showToast !== 'undefined') {
                        showToast('User status updated to ' + newStatus, 'success');
                    }
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    if (triggerBtn) {
                        triggerBtn.classList.remove('is-loading');
                    }
                    if (typeof showToast !== 'undefined') {
                        showToast(data.message || 'Update failed', 'error');
                    } else {
                        alert(data.message || 'Update failed');
                    }
                }
            })
            .catch(function() {
                if (triggerBtn) {
                    triggerBtn.classList.remove('is-loading');
                }
                if (typeof showToast !== 'undefined') {
                    showToast('Network error', 'error');
                } else {
                    alert('Network error');
                }
            });
        }

        _deleteUser(id, triggerBtn) {
            var self = this;
            this._showConfirmDialog('是否要删除这个用户？', function() {
                if (triggerBtn) {
                    triggerBtn.classList.add('is-loading');
                }

                fetch(window.SITE_URL + '/api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=delete_user&id=' + id
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (typeof showToast !== 'undefined') {
                            showToast('User deleted successfully', 'success');
                        }
                        setTimeout(function() { location.reload(); }, 800);
                    } else {
                        if (triggerBtn) {
                            triggerBtn.classList.remove('is-loading');
                        }
                        if (typeof showToast !== 'undefined') {
                            showToast(data.message || 'Delete failed', 'error');
                        } else {
                            alert(data.message || 'Delete failed');
                        }
                    }
                })
                .catch(function() {
                    if (triggerBtn) {
                        triggerBtn.classList.remove('is-loading');
                    }
                    if (typeof showToast !== 'undefined') {
                        showToast('Network error', 'error');
                    } else {
                        alert('Network error');
                    }
                });
            });
        }

        _openUserEditModal(user) {
            document.getElementById('ue-id').value = user.id;
            document.getElementById('ue-username').value = user.username;
            document.getElementById('ue-email').value = user.email;
            document.getElementById('ue-status').value = user.status;
            document.getElementById('ue-role').value = user.role || 'user';
            document.getElementById('ue-role').setAttribute('data-current-role', user.role || 'user');
            document.getElementById('ue-password').value = '';
            document.getElementById('user-edit-title').textContent = 'Edit User: ' + user.username;

            var overlay = document.getElementById('user-edit-overlay');
            var dialog = document.getElementById('user-edit-dialog');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            void overlay.offsetWidth;
            dialog.classList.remove('scale-95', 'opacity-0');
            dialog.classList.add('scale-100', 'opacity-100');
        }

        _closeUserEditModal() {
            var overlay = document.getElementById('user-edit-overlay');
            var dialog = document.getElementById('user-edit-dialog');
            dialog.classList.remove('scale-100', 'opacity-100');
            dialog.classList.add('scale-95', 'opacity-0');
            setTimeout(function() {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 200);
        }

        _initUserEditForm() {
            var self = this;
            var form = document.getElementById('user-edit-form');
            if (!form || form.dataset.bound) return;
            form.dataset.bound = '1';

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var id = document.getElementById('ue-id').value;
                var newRole = document.getElementById('ue-role').value;
                var currentRole = document.getElementById('ue-role').getAttribute('data-current-role') || 'user';

                // 如果角色发生变化，需要二次确认
                if (newRole !== currentRole) {
                    self._showConfirmDialog('确定要将角色从 ' + currentRole + ' 更改为 ' + newRole + ' 吗？', function() {
                        self._submitUserUpdate(id);
                    });
                } else {
                    self._submitUserUpdate(id);
                }
            });
        }

        _submitUserUpdate(id) {
            var self = this;
            var body = new URLSearchParams({
                action: 'update_user',
                id: id,
                username: document.getElementById('ue-username').value,
                email: document.getElementById('ue-email').value,
                status: document.getElementById('ue-status').value,
                role: document.getElementById('ue-role').value,
                password: document.getElementById('ue-password').value,
            });

            fetch(window.SITE_URL + '/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    if (typeof showToast !== 'undefined') {
                        showToast('User updated successfully', 'success');
                    }
                    self._closeUserEditModal();
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    if (typeof showToast !== 'undefined') {
                        showToast(data.message || 'Update failed', 'error');
                    } else {
                        alert(data.message || 'Update failed');
                    }
                }
            })
            .catch(function() {
                if (typeof showToast !== 'undefined') {
                    showToast('Network error', 'error');
                } else {
                    alert('Network error');
                }
            });
        }

        _initUserSearch() {
            var searchInput = document.getElementById('user-search');
            var roleFilter = document.getElementById('user-role-filter');
            if (!searchInput) return;

            function applyFilters() {
                var keyword = searchInput.value.toLowerCase();
                var role = roleFilter ? roleFilter.value : '';
                var rows = document.querySelectorAll('.user-row');
                var cards = document.querySelectorAll('.user-card');
                var visibleCount = 0;
                var index = 1;

                rows.forEach(function(row) {
                    var username = row.getAttribute('data-username');
                    var email = row.getAttribute('data-email');
                    var rowRole = row.getAttribute('data-role');
                    var matchSearch = username.includes(keyword) || email.includes(keyword);
                    var matchRole = !role || rowRole === role;
                    if (matchSearch && matchRole) {
                        row.style.display = '';
                        var idCell = row.querySelector('.user-cell-id');
                        if (idCell) {
                            idCell.textContent = index++;
                        }
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // 同步移动端卡片
                var cardIndex = 1;
                cards.forEach(function(card) {
                    var username = card.getAttribute('data-username');
                    var email = card.getAttribute('data-email');
                    var cardRole = card.getAttribute('data-role');
                    var matchSearch = username.includes(keyword) || email.includes(keyword);
                    var matchRole = !role || cardRole === role;
                    if (matchSearch && matchRole) {
                        card.style.display = '';
                        var idxEl = card.querySelector('.user-card-index');
                        if (idxEl) {
                            idxEl.textContent = cardIndex++;
                        }
                    } else {
                        card.style.display = 'none';
                    }
                });

                var countEl = document.getElementById('user-count');
                if (countEl) {
                    countEl.textContent = visibleCount;
                }
            }

            searchInput.addEventListener('input', applyFilters);
            if (roleFilter) {
                roleFilter.addEventListener('change', applyFilters);
            }
        }

        _initUserSort() {
            // 默认按 ID 升序排列
            this._sortUsersBy('id', true);
        }

        _sortUsersBy(field, ascending) {
            var tbody = document.getElementById('user-list');
            if (!tbody) return;

            var rows = Array.from(tbody.querySelectorAll('.user-row'));

            rows.sort(function(a, b) {
                var aVal = a.getAttribute('data-' + field) || '';
                var bVal = b.getAttribute('data-' + field) || '';

                // 数字比较
                if (field === 'id') {
                    aVal = parseInt(aVal) || 0;
                    bVal = parseInt(bVal) || 0;
                    return ascending ? aVal - bVal : bVal - aVal;
                }

                // 字符串比较
                aVal = aVal.toLowerCase();
                bVal = bVal.toLowerCase();
                if (aVal < bVal) return ascending ? -1 : 1;
                if (aVal > bVal) return ascending ? 1 : -1;
                return 0;
            });

            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        }

        _resetAdminForm() {
            var form = document.getElementById('product-form');
            if (!form) return;
            form.reset();
            document.getElementById('edit-id').value = '';
            // 更新模态框标题
            var modalTitle = document.getElementById('product-edit-title');
            if (modalTitle) {
                modalTitle.textContent = 'Add New Product';
            }
            document.getElementById('submit-btn').innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>Add Product';
            document.getElementById('f-button-text').value = 'Now Buy';
            document.getElementById('f-is-visible').checked = true;
        }

        _editAdminProduct(p) {
            document.getElementById('edit-id').value = p.id;
            document.getElementById('f-name').value = p.name;
            document.getElementById('f-tagline').value = p.tagline;
            document.getElementById('f-description').value = p.description || '';
            document.getElementById('f-status').value = p.status;
            document.getElementById('f-image').value = p.image;
            document.getElementById('f-button-text').value = p.button_text;
            document.getElementById('f-button-link').value = p.button_link;
            document.getElementById('f-features').value = p.features;
            document.getElementById('f-sort-order').value = p.sort_order;
            document.getElementById('f-is-visible').checked = p.is_visible == 1;
            // 更新模态框标题
            var modalTitle = document.getElementById('product-edit-title');
            if (modalTitle) {
                modalTitle.textContent = 'Edit Product: ' + p.name;
            }
            document.getElementById('submit-btn').innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Save Changes';
            // 打开模态框，传递 false 表示不重置表单
            window.openProductModal(false);
        }

        _deleteAdminProduct(id, triggerBtn) {
            var self = this;
            // 显示自定义确认对话框
            this._showConfirmDialog('是否要删除这个产品？', function() {
                // 用户点击确定，开始删除
                if (triggerBtn) {
                    triggerBtn.classList.add('is-loading');
                }

                fetch(window.SITE_URL + '/api/products.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=delete&id=' + id
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (typeof showToast !== 'undefined') {
                            showToast('Product deleted successfully', 'success');
                        }
                        setTimeout(function() { location.reload(); }, 800);
                    } else {
                        if (triggerBtn) {
                            triggerBtn.classList.remove('is-loading');
                        }
                        if (typeof showToast !== 'undefined') {
                            showToast(data.message || 'Delete failed', 'error');
                        } else {
                            alert(data.message || 'Delete failed');
                        }
                    }
                })
                .catch(function() {
                    if (triggerBtn) {
                        triggerBtn.classList.remove('is-loading');
                    }
                    if (typeof showToast !== 'undefined') {
                        showToast('Network error', 'error');
                    } else {
                        alert('Network error');
                    }
                });
            });
        }

        _showConfirmDialog(message, onConfirm) {
            // 如果已有对话框，先移除
            var existing = document.querySelector('.confirm-overlay');
            if (existing) {
                existing.remove();
            }

            // 创建对话框
            var overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.innerHTML = '<div class="confirm-dialog">' +
                '<div class="confirm-dialog-icon">' +
                    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>' +
                    '</svg>' +
                '</div>' +
                '<div class="confirm-dialog-title">确认删除</div>' +
                '<div class="confirm-dialog-message">' + message + '</div>' +
                '<div class="confirm-dialog-actions">' +
                    '<button class="confirm-btn-cancel">取消</button>' +
                    '<button class="confirm-btn-confirm">确定</button>' +
                '</div>' +
            '</div>';

            document.body.appendChild(overlay);

            // 强制回流后添加 show 类以触发过渡动画
            void overlay.offsetWidth;
            overlay.classList.add('show');

            var confirmBtn = overlay.querySelector('.confirm-btn-confirm');
            var cancelBtn = overlay.querySelector('.confirm-btn-cancel');

            function closeDialog() {
                overlay.classList.remove('show');
                setTimeout(function() {
                    if (overlay.parentNode) {
                        overlay.remove();
                    }
                }, 200);
            }

            cancelBtn.addEventListener('click', closeDialog);

            confirmBtn.addEventListener('click', function() {
                confirmBtn.disabled = true;
                confirmBtn.textContent = '删除中...';
                onConfirm();
                // 注意：不在这里关闭对话框，等 API 返回后再关闭
            });

            // 点击遮罩层关闭
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeDialog();
                }
            });

            // ESC 键关闭
            function handleEsc(e) {
                if (e.key === 'Escape') {
                    closeDialog();
                    document.removeEventListener('keydown', handleEsc);
                }
            }
            document.addEventListener('keydown', handleEsc);
        }

        _initScrollAnimations() {
            // 重新绑定 fade-in-up 动画
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            var elements = this.container.querySelectorAll('.fade-in-up');
            elements.forEach(function (el) {
                observer.observe(el);
            });
        }

        _wait(ms) {
            return new Promise(function (resolve) {
                setTimeout(resolve, ms);
            });
        }
    }

    // ============================================
    // 4. SidebarController - 侧边栏交互控制
    // ============================================
    class SidebarController {
        constructor(state, router) {
            this.state = state;
            this.router = router;
            this.sidebar = document.getElementById('app-sidebar');
            this.overlay = document.getElementById('sidebar-overlay');
            this.toggle = document.getElementById('sidebar-toggle');
            this.links = document.querySelectorAll('[data-page]');
        }

        init() {
            var self = this;

            // 汉堡按钮点击
            if (this.toggle) {
                this.toggle.addEventListener('click', function () {
                    self.toggleSidebar();
                });
            }

            // 遮罩层点击关闭
            if (this.overlay) {
                this.overlay.addEventListener('click', function () {
                    self.closeSidebar();
                });
            }

            // 导航链接点击
            this.links.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var page = link.getAttribute('data-page');
                    self.router.navigateTo(page);
                    self.closeSidebar();
                });
            });

            // ESC 键关闭侧边栏
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    self.closeSidebar();
                }
            });
        }

        toggleSidebar() {
            this.state.sidebarOpen = !this.state.sidebarOpen;
            this._updateSidebar();
        }

        closeSidebar() {
            this.state.sidebarOpen = false;
            this._updateSidebar();
        }

        updateActiveLink(page) {
            var self = this;
            this.links.forEach(function (link) {
                var linkPage = link.getAttribute('data-page');
                if (linkPage === page) {
                    link.classList.add('active', 'text-accent-purple', 'bg-accent-purple/10');
                    link.classList.remove('text-white/70');
                } else {
                    link.classList.remove('active', 'text-accent-purple', 'bg-accent-purple/10');
                    link.classList.add('text-white/70');
                    link.style.paddingLeft = '';
                }
            });
        }

        _updateSidebar() {
            if (this.state.sidebarOpen) {
                this.sidebar.classList.remove('-translate-x-full');
                this.sidebar.classList.add('translate-x-0');
                this.overlay.classList.add('visible');
            } else {
                this.sidebar.classList.add('-translate-x-full');
                this.sidebar.classList.remove('translate-x-0');
                this.overlay.classList.remove('visible');
            }
        }
    }

    // ============================================
    // 5. App - 主应用类
    // ============================================
    class App {
        constructor() {
            this.state = new AppState();
            this.router = new Router();
            this.contentEl = document.getElementById('app-content');
            this.pageLoader = new PageLoader(this.contentEl);
            this.sidebar = new SidebarController(this.state, this.router);
        }

        init() {
            var self = this;

            // 初始化侧边栏
            this.sidebar.init();

            // 初始化首屏页面脚本（PHP SSR 渲染的内容）
            this.pageLoader._initPageScripts();

            // 监听路由变化
            window.addEventListener('hashchange', function () {
                self._handleRouteChange();
            });

            // 处理初始路由
            var initialPage = this.router.getPageFromHash();
            this.sidebar.updateActiveLink(initialPage);

            // 如果 hash 不是默认页面，加载对应页面
            if (window.location.hash && window.location.hash !== '#/dashboard') {
                this._handleRouteChange();
            }
        }

        async _handleRouteChange() {
            var page = this.router.getPageFromHash();
            if (page === this.state.currentPage) return;

            this.state.setState({ currentPage: page, isLoading: true });
            await this.pageLoader.loadPage(page);
            this.sidebar.updateActiveLink(page);
            this.state.setState({ isLoading: false });

            // 滚动到顶部
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // ============================================
    // 全局排序函数（供 onclick 调用）
    // ============================================
    var currentSortField = 'id';
    var currentSortAsc = true;

    window.sortUsers = function(field) {
        if (currentSortField === field) {
            currentSortAsc = !currentSortAsc;
        } else {
            currentSortField = field;
            currentSortAsc = true;
        }

        var tbody = document.getElementById('user-list');
        if (!tbody) return;

        var rows = Array.from(tbody.querySelectorAll('.user-row'));

        rows.sort(function(a, b) {
            var aVal = a.getAttribute('data-' + field) || '';
            var bVal = b.getAttribute('data-' + field) || '';

            if (field === 'id') {
                aVal = parseInt(aVal) || 0;
                bVal = parseInt(bVal) || 0;
                return currentSortAsc ? aVal - bVal : bVal - aVal;
            }

            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
            if (aVal < bVal) return currentSortAsc ? -1 : 1;
            if (aVal > bVal) return currentSortAsc ? 1 : -1;
            return 0;
        });

        rows.forEach(function(row) {
            tbody.appendChild(row);
        });

        // 同步排序移动端卡片
        var cardsContainer = document.getElementById('user-cards');
        if (cardsContainer) {
            var cards = Array.from(cardsContainer.querySelectorAll('.user-card'));
            cards.sort(function(a, b) {
                var aVal = a.getAttribute('data-' + field) || '';
                var bVal = b.getAttribute('data-' + field) || '';

                if (field === 'id') {
                    aVal = parseInt(aVal) || 0;
                    bVal = parseInt(bVal) || 0;
                    return currentSortAsc ? aVal - bVal : bVal - aVal;
                }

                aVal = aVal.toLowerCase();
                bVal = bVal.toLowerCase();
                if (aVal < bVal) return currentSortAsc ? -1 : 1;
                if (aVal > bVal) return currentSortAsc ? 1 : -1;
                return 0;
            });

            cards.forEach(function(card) {
                cardsContainer.appendChild(card);
            });
        }

        // 重新编号
        renumberRows();
    };

    function renumberRows() {
        var rows = document.querySelectorAll('.user-row');
        var index = 1;
        rows.forEach(function(row) {
            var idCell = row.querySelector('.user-cell-id');
            if (idCell) {
                idCell.textContent = index++;
            }
        });

        // 同步移动端卡片编号
        var cards = document.querySelectorAll('.user-card');
        var cardIndex = 1;
        cards.forEach(function(card) {
            var idxEl = card.querySelector('.user-card-index');
            if (idxEl) {
                idxEl.textContent = cardIndex++;
            }
        });
    }

    // ============================================
    // 启动应用
    // ============================================
    document.addEventListener('DOMContentLoaded', function () {
        var app = new App();
        app.init();
        // 保存实例到全局，供模态框函数调用
        window.appInstance = app;
    });

})();
