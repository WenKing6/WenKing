/**
 * WenKing App - Core Application JavaScript
 * Includes routing management, AJAX loading, state management, sidebar control
 */

// Global modal functions - ensure availability in all cases
window.openProductModal = function(resetForm) {
    var overlay = document.getElementById('product-edit-overlay');
    var dialog = document.getElementById('product-edit-dialog');

    if (overlay && dialog) {
        // Get PageLoader instance and call reset form
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

// License modal functions
window.openLicenseModal = function() {
    var overlay = document.getElementById('license-edit-overlay');
    var dialog = document.getElementById('license-edit-dialog');
    var form = document.getElementById('license-form');

    if (overlay && dialog) {
        // Reset form
        if (form) form.reset();
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        setTimeout(function() {
            dialog.classList.remove('scale-95', 'opacity-0');
            dialog.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
};

window.closeLicenseModal = function() {
    var overlay = document.getElementById('license-edit-overlay');
    var dialog = document.getElementById('license-edit-dialog');
    
    if (overlay && dialog) {
        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');
        setTimeout(function() {
            overlay.classList.remove('flex');
            overlay.classList.add('hidden');
        }, 200);
    }
};

(function () {
    'use strict';

    // ============================================
    // 1. AppState - State Management (Publish-Subscribe Pattern)
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
    // 2. Router - Hash Route Management
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
    // 3. PageLoader - AJAX Page Loading
    // ============================================
    class PageLoader {
        constructor(container) {
            this.container = container;
            this.abortController = null;
        }

        async loadPage(page) {
            // Cancel previous request
            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();

            // Fade out current content
            this.container.classList.add('page-exit');
            await this._wait(200);

            // Show loading indicator
            this.container.innerHTML = '<div class="page-loading"><div class="spinner"></div></div>';

            try {
                // Make AJAX request
                var response = await fetch(
                    'api/router.php?page=' + page,
                    { signal: this.abortController.signal }
                );

                if (!response.ok) {
                    throw new Error('Failed to load page');
                }

                var html = await response.text();
                this.container.innerHTML = html;

                // Remove exit animation, add enter animation
                this.container.classList.remove('page-exit');
                this.container.classList.add('page-enter');

                // Reinitialize page interactions
                this._initPageScripts();

                // Remove class after animation ends
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
            // Reinitialize scroll animation observer
            this._initScrollAnimations();

            // Initialize tab switching
            this._initTabs();

            // Initialize Redeem page functionality
            this._initRedeemPage();

            // Initialize password strength detection
            this._initPasswordStrength();

            // Initialize Admin page functionality
            this._initAdminPage();
        }

        _initTabs() {
            var allPanels = document.querySelectorAll('.tab-panel');

            // Reseller page tab switching
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

            // Manager page tab switching
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

            // Skip if not on Redeem page
            if (!btn || !input || !toast) return;

            var toastTimer = null;

            // Icon SVG
            var icons = {
                success: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                error: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
            };

            // Show toast notification
            function showToast(message, type) {
                type = type || 'success';
                
                // Set display duration based on type
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

                // Remove all type classes
                toast.classList.remove('toast-success', 'toast-warning', 'toast-error');
                // Add current type class
                toast.classList.add('toast-' + type);

                // Set title and message
                toastTitle.textContent = title;
                toastMsg.textContent = message;
                
                // Set icon
                toastIcon.innerHTML = icons[type] || icons.success;

                // Set CSS variable for animation duration
                toast.style.setProperty('--toast-duration', duration + 'ms');

                // Reset animation
                toast.classList.remove('show');
                void toast.offsetWidth;

                // Set progress bar animation duration
                var progress = toast.querySelector('.redeem-toast-progress');
                if (progress) {
                    progress.style.animationDuration = duration + 'ms';
                }

                toast.classList.add('show');

                toastTimer = setTimeout(function() {
                    hideToast();
                }, duration);
            }

            // Hide toast notification
            function hideToast() {
                toast.classList.remove('show');
                if (toastTimer) {
                    clearTimeout(toastTimer);
                    toastTimer = null;
                }
            }

            // Close button
            if (toastClose) {
                toastClose.addEventListener('click', hideToast);
            }

            // Redeem button
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

            // Password strength detection function
            function checkPasswordStrength(password) {
                var strength = 0;
                var tips = [];

                if (!password) {
                    return { strength: 0, tips: [] };
                }

                // Length check
                if (password.length >= 8) {
                    strength++;
                } else {
                    tips.push('At least 8 characters');
                }

                // Lowercase letter check
                if (/[a-z]/.test(password)) {
                    strength++;
                } else {
                    tips.push('Add lowercase letters');
                }

                // Uppercase letter check
                if (/[A-Z]/.test(password)) {
                    strength++;
                } else {
                    tips.push('Add uppercase letters');
                }

                // Number check
                if (/[0-9]/.test(password)) {
                    strength++;
                } else {
                    tips.push('Add numbers');
                }

                // Special character check
                if (/[^a-zA-Z0-9]/.test(password)) {
                    strength++;
                } else {
                    tips.push('Add special characters');
                }

                // Adjust strength level based on score
                var level;
                if (strength <= 1) {
                    level = 1; // Very weak
                } else if (strength === 2) {
                    level = 2; // Weak
                } else if (strength === 3 || strength === 4) {
                    level = 3; // Medium
                } else {
                    level = 4; // Strong
                }

                return { strength: level, tips: tips };
            }

            // Update strength display
            function updateStrengthDisplay(result) {
                var level = result.strength;
                var tips = result.tips;

                // Show/hide container
                if (passwordInput.value) {
                    strengthContainer.classList.remove('hidden');
                } else {
                    strengthContainer.classList.add('hidden');
                    return;
                }

                // Update progress bars
                bars.forEach(function(bar, index) {
                    // Remove all level classes
                    bar.classList.remove('active', 'level-1', 'level-2', 'level-3', 'level-4');

                    if (index < level) {
                        bar.classList.add('active', 'level-' + level);
                    }
                });

                // Update text
                var strengthLabels = ['', 'Very Weak', 'Weak', 'Good', 'Strong'];
                strengthText.textContent = strengthLabels[level] || '';
                strengthText.className = 'password-strength-text level-' + level;

                // Update tips
                if (tips.length > 0 && level < 4) {
                    strengthTips.textContent = 'Tips: ' + tips.slice(0, 2).join(', ');
                } else {
                    strengthTips.textContent = level === 4 ? 'Password strength is good!' : '';
                }
            }

            // Password match detection function
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
                    matchText.textContent = 'Passwords match';
                    matchText.className = 'password-match-text match';
                } else {
                    matchIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                    matchIcon.className = 'password-match-icon no-match';
                    matchText.textContent = 'Passwords do not match';
                    matchText.className = 'password-match-text no-match';
                }
            }

            // Listen to password input
            passwordInput.addEventListener('input', function() {
                var password = this.value;
                var result = checkPasswordStrength(password);
                updateStrengthDisplay(result);
                checkPasswordMatch();
            });

            // Listen to confirm password input
            if (confirmInput) {
                confirmInput.addEventListener('input', function() {
                    checkPasswordMatch();
                });
            }
        }

        _initCustomSelects() {
            var self = this;
            var PAGE_SIZE = 10;

            // 点击页面其他区域关闭所有已展开的下拉
            if (!document._wkSelectClickBound) {
                document._wkSelectClickBound = true;
                document.addEventListener('click', function() {
                    document.querySelectorAll('.wk-select.is-open').forEach(function(w) {
                        self._closeCustomSelect(w);
                    });
                });
            }

            document.querySelectorAll('.wk-select').forEach(function(wrapper) {
                if (wrapper.dataset.bound) return;
                wrapper.dataset.bound = '1';

                var select = wrapper.querySelector('.wk-select__native');
                var trigger = wrapper.querySelector('.wk-select__trigger');
                var menu = wrapper.querySelector('.wk-select__menu');
                var optionsWrap = wrapper.querySelector('.wk-select__options');
                var pager = wrapper.querySelector('.wk-select__pager');
                var pagerInfo = wrapper.querySelector('.wk-select__pager-info');
                var pagerPrev = wrapper.querySelector('.wk-select__pager-prev');
                var pagerNext = wrapper.querySelector('.wk-select__pager-next');

                if (!select || !trigger || !menu || !optionsWrap) return;

                var currentPage = 1;
                var totalPages = 1;

                // 同步当前选中项到触发器显示
                var syncValue = function() {
                    var valueEl = wrapper.querySelector('.wk-select__value');
                    var selectedOption = select.options[select.selectedIndex];
                    if (valueEl && selectedOption) {
                        valueEl.textContent = selectedOption.textContent;
                    }
                };
                syncValue();

                // 分页渲染：仅显示当前页的选项
                var renderPage = function() {
                    var allOptions = Array.prototype.slice.call(optionsWrap.querySelectorAll('.wk-select__option'));
                    var total = allOptions.length;
                    totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                    if (currentPage > totalPages) currentPage = totalPages;
                    if (currentPage < 1) currentPage = 1;

                    allOptions.forEach(function(o, i) {
                        var pageIndex = Math.floor(i / PAGE_SIZE);
                        o.style.display = (pageIndex === currentPage - 1) ? '' : 'none';
                    });

                    // 分页控件仅在选项超过一页时显示
                    if (pager) {
                        var needsPager = total > PAGE_SIZE;
                        pager.style.display = needsPager ? '' : 'none';
                        if (pagerInfo) {
                            pagerInfo.textContent = currentPage + ' / ' + totalPages;
                        }
                        if (pagerPrev) pagerPrev.disabled = currentPage <= 1;
                        if (pagerNext) pagerNext.disabled = currentPage >= totalPages;
                    }
                };

                // 分页按钮事件
                if (pagerPrev) {
                    pagerPrev.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (currentPage > 1) {
                            currentPage--;
                            renderPage();
                        }
                    });
                }
                if (pagerNext) {
                    pagerNext.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (currentPage < totalPages) {
                            currentPage++;
                            renderPage();
                        }
                    });
                }

                // 设置选中项并同步原生 select、触发 change 事件
                var selectOption = function(optionBtn) {
                    var value = optionBtn.getAttribute('data-value');
                    var label = optionBtn.querySelector('.wk-select__option-label').textContent;

                    // 更新选中态样式
                    optionsWrap.querySelectorAll('.wk-select__option').forEach(function(o) {
                        o.classList.remove('is-selected');
                        o.setAttribute('aria-selected', 'false');
                    });
                    optionBtn.classList.add('is-selected');
                    optionBtn.setAttribute('aria-selected', 'true');

                    // 同步原生 select 并触发 change
                    select.value = value;
                    var valueEl = wrapper.querySelector('.wk-select__value');
                    if (valueEl) valueEl.textContent = label;
                    select.dispatchEvent(new Event('change'));

                    self._closeCustomSelect(wrapper);
                };

                // 触发器：点击展开/收起
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var isOpen = wrapper.classList.contains('is-open');

                    // 关闭其他下拉
                    document.querySelectorAll('.wk-select.is-open').forEach(function(w) {
                        if (w !== wrapper) self._closeCustomSelect(w);
                    });

                    if (isOpen) {
                        self._closeCustomSelect(wrapper);
                    } else {
                        self._openCustomSelect(wrapper);
                    }
                });

                // 选项点击（事件委托，兼容分页与动态重建）
                optionsWrap.addEventListener('click', function(e) {
                    var optionBtn = e.target.closest('.wk-select__option');
                    if (!optionBtn) return;
                    e.stopPropagation();
                    selectOption(optionBtn);
                });

                // 键盘导航
                trigger.addEventListener('keydown', function(e) {
                    var isOpen = wrapper.classList.contains('is-open');
                    var visibleOptions = Array.prototype.slice.call(optionsWrap.querySelectorAll('.wk-select__option')).filter(function(o) {
                        return o.style.display !== 'none';
                    });

                    if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        e.preventDefault();
                    }

                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        if (!isOpen) {
                            self._openCustomSelect(wrapper);
                            return;
                        }
                        var currentIdx = visibleOptions.indexOf(optionsWrap.querySelector('.wk-select__option.is-selected'));
                        var nextIdx;
                        if (e.key === 'ArrowDown') {
                            nextIdx = currentIdx < visibleOptions.length - 1 ? currentIdx + 1 : 0;
                        } else {
                            nextIdx = currentIdx > 0 ? currentIdx - 1 : visibleOptions.length - 1;
                        }
                        // 高亮当前项，同步原生 select
                        visibleOptions.forEach(function(o) { o.classList.remove('is-highlighted'); });
                        if (visibleOptions[nextIdx]) {
                            visibleOptions[nextIdx].classList.add('is-highlighted');
                            select.value = visibleOptions[nextIdx].getAttribute('data-value');
                            syncValue();
                        }
                    } else if (e.key === 'Enter' || e.key === ' ') {
                        if (isOpen) {
                            var highlighted = optionsWrap.querySelector('.wk-select__option.is-highlighted');
                            if (highlighted) {
                                selectOption(highlighted);
                            } else {
                                self._closeCustomSelect(wrapper);
                            }
                        } else {
                            self._openCustomSelect(wrapper);
                        }
                    } else if (e.key === 'Escape') {
                        if (isOpen) self._closeCustomSelect(wrapper);
                    }
                });

                // 保存渲染函数供展开时使用
                wrapper._wkRenderPage = renderPage;
                wrapper._wkCurrentPage = function() { return currentPage; };
                wrapper._wkSetCurrentPage = function(p) { currentPage = p; };
            });
        }

        _openCustomSelect(wrapper) {
            wrapper.classList.add('is-open');
            var trigger = wrapper.querySelector('.wk-select__trigger');
            var menu = wrapper.querySelector('.wk-select__menu');
            if (trigger) trigger.setAttribute('aria-expanded', 'true');
            if (menu) menu.setAttribute('aria-hidden', 'false');

            // 底部空间不足时向上展开，避免菜单被滚动容器（弹窗）底部裁剪
            if (menu) {
                var dialog = wrapper.closest('.glass-card');
                if (dialog) {
                    var dRect = dialog.getBoundingClientRect();
                    var wRect = wrapper.getBoundingClientRect();
                    var menuHeight = menu.offsetHeight || 0;
                    var spaceBelow = dRect.bottom - wRect.bottom;
                    var spaceAbove = wRect.top - dRect.top;
                    var flipUp = spaceBelow < menuHeight + 8 && spaceAbove > spaceBelow;
                    menu.classList.toggle('wk-select__menu--up', flipUp);
                }
            }

            // 展开时按分页渲染，并高亮当前选中项所在页
            if (wrapper._wkRenderPage) wrapper._wkRenderPage();
            var selected = wrapper.querySelector('.wk-select__option.is-selected');
            if (selected) {
                wrapper.querySelectorAll('.wk-select__option').forEach(function(o) {
                    o.classList.remove('is-highlighted');
                });
                selected.classList.add('is-highlighted');
                // 若选中项不在当前页，跳转到其所在页
                if (selected.style.display === 'none' && wrapper._wkSetCurrentPage) {
                    var allOptions = Array.prototype.slice.call(wrapper.querySelectorAll('.wk-select__option'));
                    var idx = allOptions.indexOf(selected);
                    if (idx >= 0) {
                        wrapper._wkSetCurrentPage(Math.floor(idx / 10) + 1);
                    }
                    if (wrapper._wkRenderPage) wrapper._wkRenderPage();
                    selected.classList.add('is-highlighted');
                }
            }
        }

        _closeCustomSelect(wrapper) {
            wrapper.classList.remove('is-open');
            var trigger = wrapper.querySelector('.wk-select__trigger');
            var menu = wrapper.querySelector('.wk-select__menu');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
            if (menu) {
                menu.setAttribute('aria-hidden', 'true');
                // 注意：不移除 wk-select__menu--up，避免关闭时菜单瞬间从上方跳回下方（先下移再消失）。
                // 下一次展开时 _openCustomSelect 会重新根据可用空间计算该类。
            }
            wrapper.querySelectorAll('.wk-select__option').forEach(function(o) {
                o.classList.remove('is-highlighted');
            });
        }

        _refreshCustomSelect(selectId) {
            var select = document.getElementById(selectId);
            if (!select) return;
            var wrapper = select.closest('.wk-select');
            if (!wrapper) return;
            var optionsWrap = wrapper.querySelector('.wk-select__options');
            var valueEl = wrapper.querySelector('.wk-select__value');
            if (!optionsWrap || !valueEl) return;

            // 重建选项按钮
            optionsWrap.innerHTML = '';
            Array.from(select.options).forEach(function(option) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'wk-select__option';
                btn.setAttribute('role', 'option');
                btn.setAttribute('aria-selected', 'false');
                btn.setAttribute('data-value', option.value);

                var label = document.createElement('span');
                label.className = 'wk-select__option-label';
                label.textContent = option.textContent;
                btn.appendChild(label);

                if (option.selected) {
                    btn.classList.add('is-selected');
                    btn.setAttribute('aria-selected', 'true');
                }
                optionsWrap.appendChild(btn);
            });

            // 重置分页到第一页并重渲染
            if (wrapper._wkSetCurrentPage) wrapper._wkSetCurrentPage(1);
            if (wrapper._wkRenderPage) wrapper._wkRenderPage();

            // 同步 value 显示
            var selectedOption = select.options[select.selectedIndex];
            valueEl.textContent = selectedOption ? selectedOption.textContent : '';
        }

        _initAdminPage() {
            var self = this;

            // 初始化自定义选择器（button 风格下拉）
            this._initCustomSelects();

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

            // License 模态框事件绑定
            var licenseCloseBtn = document.getElementById('license-edit-close');
            if (licenseCloseBtn && !licenseCloseBtn.dataset.bound) {
                licenseCloseBtn.dataset.bound = '1';
                licenseCloseBtn.addEventListener('click', window.closeLicenseModal);
            }

            var licenseCancelBtn = document.getElementById('license-cancel-btn');
            if (licenseCancelBtn && !licenseCancelBtn.dataset.bound) {
                licenseCancelBtn.dataset.bound = '1';
                licenseCancelBtn.addEventListener('click', window.closeLicenseModal);
            }

            var licenseOverlay = document.getElementById('license-edit-overlay');
            if (licenseOverlay && !licenseOverlay.dataset.bound) {
                licenseOverlay.dataset.bound = '1';
                licenseOverlay.addEventListener('click', function(e) {
                    if (e.target === licenseOverlay) {
                        window.closeLicenseModal();
                    }
                });
            }

            // 角色选择动态加载用户列表
            var roleSelect = document.getElementById('lf-role');
            var userSelect = document.getElementById('lf-user');
            var userContainer = document.getElementById('lf-user-container');
            if (roleSelect && userSelect && userContainer && !roleSelect.dataset.bound) {
                roleSelect.dataset.bound = '1';

                // 从 DOM 中读取用户数据
                var pageHeader = document.querySelector('.app-page-header[data-users-by-role]');
                var usersByRole = {};
                if (pageHeader && pageHeader.dataset.usersByRole) {
                    try {
                        usersByRole = JSON.parse(pageHeader.dataset.usersByRole);
                    } catch (e) {
                        console.error('Failed to parse usersByRole:', e);
                    }
                }

                // 根据角色加载用户
                function loadUsersByRole(role) {
                    // 清空现有选项
                    userSelect.innerHTML = '<option value="">-- Select User --</option>';

                    // Admin 角色不需要选择用户，隐藏容器
                    if (role === 'admin') {
                        userContainer.style.display = 'none';
                        userSelect.removeAttribute('required');
                        self._refreshCustomSelect('lf-user');
                        return;
                    }

                    // 其他角色需要选择用户，显示容器
                    userContainer.style.display = 'block';
                    userSelect.setAttribute('required', 'required');

                    if (!role) {
                        self._refreshCustomSelect('lf-user');
                        return;
                    }

                    var users = usersByRole[role] || [];
                    if (users.length > 0) {
                        users.forEach(function(user) {
                            var option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.username + ' (' + user.email + ')';
                            userSelect.appendChild(option);
                        });
                    }
                    self._refreshCustomSelect('lf-user');
                }

                // 监听角色选择变化
                roleSelect.addEventListener('change', function() {
                    loadUsersByRole(this.value);
                });
            }

            // License 表单提交
            var licenseForm = document.getElementById('license-form');
            if (licenseForm && !licenseForm.dataset.bound) {
                licenseForm.dataset.bound = '1';
                licenseForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    var productId = document.getElementById('lf-product').value;
                    var duration = document.getElementById('lf-duration').value;
                    var role = document.getElementById('lf-role').value;
                    var userId = document.getElementById('lf-user').value;
                    var licenseKeysText = document.getElementById('lf-license-keys').value;

                    if (!productId) {
                        if (typeof showToast !== 'undefined') {
                            showToast('Please select a product', 'error');
                        }
                        return;
                    }

                    // 非 Admin 角色需要选择用户
                    if (role !== 'admin' && !userId) {
                        if (typeof showToast !== 'undefined') {
                            showToast('Please select a user', 'error');
                        }
                        return;
                    }

                    if (!licenseKeysText || !licenseKeysText.trim()) {
                        if (typeof showToast !== 'undefined') {
                            showToast('Please enter at least one license key', 'error');
                        }
                        return;
                    }

                    // 解析多行许可证键
                    var licenseKeys = licenseKeysText.split('\n')
                        .map(function(key) { return key.trim(); })
                        .filter(function(key) { return key.length > 0; });

                    if (licenseKeys.length === 0) {
                        if (typeof showToast !== 'undefined') {
                            showToast('Please enter at least one license key', 'error');
                        }
                        return;
                    }

                    // 构建批量数据
                    var licensesData = licenseKeys.map(function(key) {
                        return {
                            license_key: key,
                            product_id: parseInt(productId),
                            user_id: role === 'admin' ? null : parseInt(userId),
                            duration_days: parseInt(duration),
                            status: 'unused'
                        };
                    });

                    console.log('Batch license data:', licensesData);

                    // 提交到 API
                    var formData = new FormData();
                    formData.append('action', 'batch_create');
                    formData.append('licenses', JSON.stringify(licensesData));

                    fetch(window.SITE_URL + '/api/licenses.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var msg = 'Successfully created ' + data.created + ' license(s)';
                            if (data.failed > 0) {
                                msg += ', ' + data.failed + ' failed';
                            }
                            if (typeof showToast !== 'undefined') {
                                showToast(msg, data.failed > 0 ? 'warning' : 'success');
                            }
                            window.closeLicenseModal();
                            // 刷新页面以显示新数据
                            setTimeout(function() { location.reload(); }, 1000);
                        } else {
                            if (typeof showToast !== 'undefined') {
                                showToast(data.message || 'Failed to create licenses', 'error');
                            }
                        }
                    })
                    .catch(function(err) {
                        console.error('Error:', err);
                        if (typeof showToast !== 'undefined') {
                            showToast('Network error', 'error');
                        }
                    });
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

            // 初始化 Status 切换自动更新 Button Text
            self._bindStatusChange();

            // 初始化用户分页
            self._initUserPagination();

            // 初始化用户搜索
            self._initUserSearch();

            // 初始化用户编辑表单
            self._initUserEditForm();

            // 初始化产品分页
            self._initProductPagination();

            // 初始化产品状态筛选
            self._initProductFilters();

            // 初始化许可证搜索/筛选/分页
            self._initLicenseFilters();
        }

        _initLicenseFilters() {
            // 检查是否在 License 页面
            var searchInput = document.getElementById('license-search');
            if (!searchInput) return;

            var self = this;
            var productFilter = document.getElementById('license-product-filter');
            var statusFilter = document.getElementById('license-status-filter');
            var perPageSelect = document.getElementById('license-per-page');
            var prevBtn = document.getElementById('license-prev-page');
            var nextBtn = document.getElementById('license-next-page');

            // 当前页码
            var currentPage = 1;
            var perPage = perPageSelect ? parseInt(perPageSelect.value) : 10;

            // 获取所有许可证行
            var allRows = [];
            var tbody = document.getElementById('license-list');
            if (tbody) {
                allRows = Array.from(tbody.querySelectorAll('tr[data-id]'));
            }

            // 更新统计数字
            self._updateLicenseStats();

            // 搜索功能
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentPage = 1;
                    self._filterLicenses();
                });
            }

            // 产品筛选
            if (productFilter) {
                productFilter.addEventListener('change', function() {
                    currentPage = 1;
                    self._filterLicenses();
                });
            }

            // 状态筛选
            if (statusFilter) {
                statusFilter.addEventListener('change', function() {
                    currentPage = 1;
                    self._filterLicenses();
                });
            }

            // 每页显示数量
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    perPage = parseInt(this.value);
                    currentPage = 1;
                    self._filterLicenses();
                });
            }

            // 上一页
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        self._filterLicenses();
                    }
                });
            }

            // 下一页
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    var totalPages = Math.ceil(self._getFilteredLicenseCount() / perPage);
                    if (currentPage < totalPages) {
                        currentPage++;
                        self._filterLicenses();
                    }
                });
            }

            // ===== 勾选与批量删除 =====
            var selectedIds = new Set();
            var selectAllEl = document.getElementById('license-select-all');
            var deleteSelectedBtn = document.getElementById('license-delete-selected');

            // 更新已选数量与批量删除按钮显示状态
            function updateSelectedUI() {
                var count = selectedIds.size;
                var countEl = document.getElementById('license-selected-count');
                if (countEl) countEl.textContent = count;
                if (deleteSelectedBtn) {
                    if (count > 0) {
                        deleteSelectedBtn.classList.remove('hidden');
                        deleteSelectedBtn.classList.add('flex');
                    } else {
                        deleteSelectedBtn.classList.add('hidden');
                        deleteSelectedBtn.classList.remove('flex');
                    }
                }
            }

            // 同步表头全选框状态（仅针对当前页可见行）
            function syncSelectAllState() {
                if (!selectAllEl) return;
                var visibleChecks = self._getVisibleLicenseChecks();
                var checkedCount = visibleChecks.filter(function(cb) { return cb.checked; }).length;
                selectAllEl.checked = visibleChecks.length > 0 && checkedCount === visibleChecks.length;
                selectAllEl.indeterminate = checkedCount > 0 && checkedCount < visibleChecks.length;
            }

            // 获取当前页可见的行勾选框（桌面表格）
            self._getVisibleLicenseChecks = function() {
                var tbody = document.getElementById('license-list');
                if (!tbody) return [];
                return Array.from(tbody.querySelectorAll('.license-row-check')).filter(function(cb) {
                    var tr = cb.closest('tr');
                    return tr && tr.style.display !== 'none';
                });
            };

            // 勾选/取消单行（同时更新选中集合与行高亮）
            self._toggleLicenseRowSelection = function(cb) {
                var id = cb.getAttribute('data-id');
                // 从父元素开始查找行容器（桌面 tr / 移动卡片 div），避免命中勾选框自身
                var row = cb.parentElement ? cb.parentElement.closest('[data-id]') : null;
                if (cb.checked) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                if (row) {
                    row.classList.toggle('license-row-selected', cb.checked);
                }
            };

            // 表头全选框（作用于当前页可见行）
            if (selectAllEl) {
                selectAllEl.addEventListener('change', function() {
                    var visibleChecks = self._getVisibleLicenseChecks();
                    visibleChecks.forEach(function(cb) {
                        cb.checked = selectAllEl.checked;
                        self._toggleLicenseRowSelection(cb);
                    });
                    updateSelectedUI();
                    syncSelectAllState();
                });
            }

            // 行勾选（事件委托，覆盖桌面表格与移动卡片）
            // 每次重新初始化时移除旧监听器再绑定新的，避免重复绑定或闭包捕获旧 self
            if (self._licenseRowChangeHandler) {
                document.removeEventListener('change', self._licenseRowChangeHandler);
            }
            self._licenseRowChangeHandler = function(e) {
                var cb = e.target.closest('.license-row-check');
                if (!cb) return;
                self._toggleLicenseRowSelection(cb);
                updateSelectedUI();
                syncSelectAllState();
            };
            document.addEventListener('change', self._licenseRowChangeHandler);

            // 批量删除已选
            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', function() {
                    var ids = Array.from(selectedIds);
                    if (ids.length === 0) return;
                    self._deleteLicenses(ids);
                });
            }

            // 初始筛选
            self._filterLicenses();

            // 保存引用供后续使用
            this._licenseCurrentPage = function() { return currentPage; };
            this._licenseSetCurrentPage = function(page) { currentPage = page; };
            this._licensePerPage = function() { return perPage; };
            this._syncLicenseSelectAll = syncSelectAllState;
            updateSelectedUI();
        }

        _filterLicenses() {
            var searchInput = document.getElementById('license-search');
            var productFilter = document.getElementById('license-product-filter');
            var statusFilter = document.getElementById('license-status-filter');
            var tbody = document.getElementById('license-list');

            if (!tbody) return;

            var searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            var productValue = productFilter ? productFilter.value : '';
            var statusValue = statusFilter ? statusFilter.value : '';

            var allRows = Array.from(tbody.querySelectorAll('tr[data-id]'));
            var visibleRows = [];

            // 筛选行
            allRows.forEach(function(row) {
                var licenseKey = row.querySelector('td:nth-child(1)');
                var productCell = row.querySelector('td:nth-child(2)');
                var statusCell = row.querySelector('td:nth-child(5)');

                var keyText = licenseKey ? licenseKey.textContent.toLowerCase() : '';
                var productId = productFilter ? productFilter.options[productFilter.selectedIndex].value : '';
                var statusText = statusCell ? statusCell.textContent.toLowerCase() : '';

                var matchSearch = !searchTerm || keyText.includes(searchTerm);
                var matchProduct = !productValue || row.getAttribute('data-product-id') === productValue;
                var matchStatus = !statusValue || statusText.includes(statusValue);

                if (matchSearch && matchProduct && matchStatus) {
                    visibleRows.push(row);
                }
            });

            // 分页
            var currentPage = this._licenseCurrentPage ? this._licenseCurrentPage() : 1;
            var perPage = this._licensePerPage ? this._licensePerPage() : 10;
            var startIndex = (currentPage - 1) * perPage;
            var endIndex = startIndex + perPage;

            // 隐藏所有行
            allRows.forEach(function(row) {
                row.style.display = 'none';
            });

            // 显示当前页的行
            var pageRows = visibleRows.slice(startIndex, endIndex);
            pageRows.forEach(function(row) {
                row.style.display = '';
            });

            // 更新分页信息
            this._updateLicensePagination(visibleRows.length, currentPage, perPage);

            // 同步表头全选框状态
            if (this._syncLicenseSelectAll) {
                this._syncLicenseSelectAll();
            }
        }

        _getFilteredLicenseCount() {
            var tbody = document.getElementById('license-list');
            if (!tbody) return 0;

            var searchInput = document.getElementById('license-search');
            var productFilter = document.getElementById('license-product-filter');
            var statusFilter = document.getElementById('license-status-filter');

            var searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            var productValue = productFilter ? productFilter.value : '';
            var statusValue = statusFilter ? statusFilter.value : '';

            var allRows = Array.from(tbody.querySelectorAll('tr[data-id]'));
            var count = 0;

            allRows.forEach(function(row) {
                var licenseKey = row.querySelector('td:nth-child(1)');
                var statusCell = row.querySelector('td:nth-child(5)');

                var keyText = licenseKey ? licenseKey.textContent.toLowerCase() : '';
                var statusText = statusCell ? statusCell.textContent.toLowerCase() : '';

                var matchSearch = !searchTerm || keyText.includes(searchTerm);
                var matchProduct = !productValue || row.getAttribute('data-product-id') === productValue;
                var matchStatus = !statusValue || statusText.includes(statusValue);

                if (matchSearch && matchProduct && matchStatus) {
                    count++;
                }
            });

            return count;
        }

        _updateLicensePagination(totalCount, currentPage, perPage) {
            var totalPages = Math.ceil(totalCount / perPage);
            var startIndex = (currentPage - 1) * perPage + 1;
            var endIndex = Math.min(currentPage * perPage, totalCount);

            // 更新显示信息
            var showingStart = document.getElementById('license-showing-start');
            var showingEnd = document.getElementById('license-showing-end');
            var totalCountEl = document.getElementById('license-total-count');
            var currentPageEl = document.getElementById('license-current-page');
            var totalPagesEl = document.getElementById('license-total-pages');
            var prevBtn = document.getElementById('license-prev-page');
            var nextBtn = document.getElementById('license-next-page');

            if (showingStart) showingStart.textContent = totalCount > 0 ? startIndex : 0;
            if (showingEnd) showingEnd.textContent = endIndex;
            if (totalCountEl) totalCountEl.textContent = totalCount;
            if (currentPageEl) currentPageEl.textContent = currentPage;
            if (totalPagesEl) totalPagesEl.textContent = totalPages || 1;

            // 更新按钮状态
            if (prevBtn) {
                prevBtn.disabled = currentPage <= 1;
            }
            if (nextBtn) {
                nextBtn.disabled = currentPage >= totalPages;
            }

            // 更新计数
            var countEl = document.getElementById('license-count');
            if (countEl) {
                countEl.textContent = totalCount;
            }
        }

        _updateLicenseStats() {
            var tbody = document.getElementById('license-list');
            if (!tbody) return;

            var allRows = Array.from(tbody.querySelectorAll('tr[data-id]'));
            var total = allRows.length;
            var active = 0;
            var unused = 0;
            var expired = 0;

            allRows.forEach(function(row) {
                var statusCell = row.querySelector('td:nth-child(5)');
                if (statusCell) {
                    var statusText = statusCell.textContent.toLowerCase();
                    if (statusText.includes('active')) active++;
                    else if (statusText.includes('unused')) unused++;
                    else if (statusText.includes('expired') || statusText.includes('disabled')) expired++;
                }
            });

            // 更新统计卡片
            var totalEl = document.getElementById('license-total');
            var activeEl = document.getElementById('license-active');
            var unusedEl = document.getElementById('license-unused');
            var expiredEl = document.getElementById('license-expired');

            if (totalEl) totalEl.textContent = total;
            if (activeEl) activeEl.textContent = active;
            if (unusedEl) unusedEl.textContent = unused;
            if (expiredEl) expiredEl.textContent = expired;
        }

        _deleteLicenses(ids) {
            var self = this;
            var count = ids.length;
            this._showConfirmDialog('Are you sure you want to delete ' + count + ' selected license(s)? This action cannot be undone.', function() {
                fetch(window.SITE_URL + '/api/licenses.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=batch_delete&ids=' + encodeURIComponent(JSON.stringify(ids))
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (typeof showToast !== 'undefined') {
                            showToast(data.message || 'Licenses deleted successfully', 'success');
                        }
                        setTimeout(function() { location.reload(); }, 800);
                    } else {
                        if (typeof showToast !== 'undefined') {
                            showToast(data.message || 'Delete failed', 'error');
                        } else {
                            alert(data.message || 'Delete failed');
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
            this._showConfirmDialog('Are you sure you want to delete this user?', function() {
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

            this._refreshCustomSelect('ue-status');
            this._refreshCustomSelect('ue-role');

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
                    self._showConfirmDialog('Are you sure you want to change the role from ' + currentRole + ' to ' + newRole + '?', function() {
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

            var self = this;

            function applyFilters() {
                // 搜索/筛选变化时回到第一页
                if (self._userSetCurrentPage) self._userSetCurrentPage(1);
                self._filterUsers();
            }

            searchInput.addEventListener('input', applyFilters);
            if (roleFilter) {
                roleFilter.addEventListener('change', applyFilters);
            }
        }

        _initUserPagination() {
            var self = this;
            var perPageSelect = document.getElementById('user-per-page');
            if (!perPageSelect) return;

            var currentPage = 1;
            var perPage = parseInt(perPageSelect.value) || 10;
            var prevBtn = document.getElementById('user-prev-page');
            var nextBtn = document.getElementById('user-next-page');

            // 每页显示数量
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    perPage = parseInt(this.value) || 10;
                    currentPage = 1;
                    self._filterUsers();
                });
            }

            // 上一页
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        self._filterUsers();
                    }
                });
            }

            // 下一页
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    var totalPages = Math.ceil(self._getFilteredUserCount() / perPage);
                    if (currentPage < totalPages) {
                        currentPage++;
                        self._filterUsers();
                    }
                });
            }

            // 保存状态供其他方法读取
            this._userCurrentPage = function() { return currentPage; };
            this._userSetCurrentPage = function(p) { currentPage = p; };
            this._userPerPage = function() { return perPage; };

            // 初始筛选
            self._filterUsers();
        }

        _getFilteredUserCount() {
            var searchInput = document.getElementById('user-search');
            var roleFilter = document.getElementById('user-role-filter');
            var keyword = searchInput ? searchInput.value.toLowerCase() : '';
            var role = roleFilter ? roleFilter.value : '';

            var rows = document.querySelectorAll('.user-row');
            var count = 0;
            rows.forEach(function(row) {
                var username = row.getAttribute('data-username');
                var email = row.getAttribute('data-email');
                var rowRole = row.getAttribute('data-role');
                var matchSearch = username.includes(keyword) || email.includes(keyword);
                var matchRole = !role || rowRole === role;
                if (matchSearch && matchRole) count++;
            });
            return count;
        }

        _filterUsers() {
            var searchInput = document.getElementById('user-search');
            var roleFilter = document.getElementById('user-role-filter');
            var rows = Array.prototype.slice.call(document.querySelectorAll('.user-row'));
            var cards = Array.prototype.slice.call(document.querySelectorAll('.user-card'));

            var keyword = searchInput ? searchInput.value.toLowerCase() : '';
            var role = roleFilter ? roleFilter.value : '';

            // 筛选出符合条件的行与卡片
            var visibleRows = [];
            var visibleCards = [];

            rows.forEach(function(row) {
                var username = row.getAttribute('data-username');
                var email = row.getAttribute('data-email');
                var rowRole = row.getAttribute('data-role');
                var matchSearch = username.includes(keyword) || email.includes(keyword);
                var matchRole = !role || rowRole === role;
                if (matchSearch && matchRole) {
                    visibleRows.push(row);
                }
            });
            cards.forEach(function(card) {
                var username = card.getAttribute('data-username');
                var email = card.getAttribute('data-email');
                var cardRole = card.getAttribute('data-role');
                var matchSearch = username.includes(keyword) || email.includes(keyword);
                var matchRole = !role || cardRole === role;
                if (matchSearch && matchRole) {
                    visibleCards.push(card);
                }
            });

            // 分页
            var currentPage = this._userCurrentPage ? this._userCurrentPage() : 1;
            var perPage = this._userPerPage ? this._userPerPage() : 10;
            var totalPages = Math.max(1, Math.ceil(visibleRows.length / perPage));
            if (currentPage > totalPages) {
                currentPage = totalPages;
                if (this._userSetCurrentPage) this._userSetCurrentPage(currentPage);
            }

            var startIndex = (currentPage - 1) * perPage;
            var endIndex = Math.min(startIndex + perPage, visibleRows.length);

            // 隐藏所有行/卡片
            rows.forEach(function(row) { row.style.display = 'none'; });
            cards.forEach(function(card) { card.style.display = 'none'; });

            // 显示当前页的行/卡片
            visibleRows.slice(startIndex, endIndex).forEach(function(row) { row.style.display = ''; });
            visibleCards.slice(startIndex, endIndex).forEach(function(card) { card.style.display = ''; });

            // 更新计数
            var countEl = document.getElementById('user-count');
            if (countEl) countEl.textContent = visibleRows.length;

            // 更新分页信息
            this._updateUserPagination(visibleRows.length, currentPage, perPage);
        }

        _updateUserPagination(totalCount, currentPage, perPage) {
            var totalPages = Math.ceil(totalCount / perPage);
            var startIndex = (currentPage - 1) * perPage + 1;
            var endIndex = Math.min(currentPage * perPage, totalCount);

            // 更新显示信息
            var showingStart = document.getElementById('user-showing-start');
            var showingEnd = document.getElementById('user-showing-end');
            var totalCountEl = document.getElementById('user-total-count');
            var currentPageEl = document.getElementById('user-current-page');
            var totalPagesEl = document.getElementById('user-total-pages');
            var prevBtn = document.getElementById('user-prev-page');
            var nextBtn = document.getElementById('user-next-page');

            if (showingStart) showingStart.textContent = totalCount > 0 ? startIndex : 0;
            if (showingEnd) showingEnd.textContent = endIndex;
            if (totalCountEl) totalCountEl.textContent = totalCount;
            if (currentPageEl) currentPageEl.textContent = currentPage;
            if (totalPagesEl) totalPagesEl.textContent = totalPages || 1;
            if (prevBtn) prevBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        }

        _initProductPagination() {
            var self = this;
            var perPageSelect = document.getElementById('product-per-page');
            if (!perPageSelect) return;

            var currentPage = 1;
            var perPage = parseInt(perPageSelect.value) || 10;
            var prevBtn = document.getElementById('product-prev-page');
            var nextBtn = document.getElementById('product-next-page');

            // 每页显示数量
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    perPage = parseInt(this.value) || 10;
                    currentPage = 1;
                    self._filterProducts();
                });
            }

            // 上一页
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        self._filterProducts();
                    }
                });
            }

            // 下一页
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    var totalPages = Math.ceil(self._getProductCount() / perPage);
                    if (currentPage < totalPages) {
                        currentPage++;
                        self._filterProducts();
                    }
                });
            }

            // 保存状态供其他方法读取
            this._productCurrentPage = function() { return currentPage; };
            this._productSetCurrentPage = function(p) { currentPage = p; };
            this._productPerPage = function() { return perPage; };

            // 初始分页
            self._filterProducts();
        }

        _getProductCount() {
            var tbody = document.getElementById('product-list');
            if (!tbody) return 0;
            var statusFilter = document.getElementById('product-status-filter');
            var status = statusFilter ? statusFilter.value : '';
            return Array.prototype.filter.call(tbody.querySelectorAll('tr[data-id]'), function(row) {
                return !status || row.getAttribute('data-status') === status;
            }).length;
        }

        _filterProducts() {
            var tbody = document.getElementById('product-list');
            if (!tbody) return;

            var statusFilter = document.getElementById('product-status-filter');
            var status = statusFilter ? statusFilter.value : '';

            var cardsContainer = document.getElementById('product-list-mobile');
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-id]'));
            var cards = cardsContainer ? Array.prototype.slice.call(cardsContainer.querySelectorAll('.product-card')) : [];

            // 筛选出符合条件的行与卡片
            var visibleRows = [];
            var visibleCards = [];
            rows.forEach(function(row) {
                if (!status || row.getAttribute('data-status') === status) visibleRows.push(row);
            });
            cards.forEach(function(card) {
                if (!status || card.getAttribute('data-status') === status) visibleCards.push(card);
            });
            var total = visibleRows.length;

            // 分页
            var currentPage = this._productCurrentPage ? this._productCurrentPage() : 1;
            var perPage = this._productPerPage ? this._productPerPage() : 10;
            var totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) {
                currentPage = totalPages;
                if (this._productSetCurrentPage) this._productSetCurrentPage(currentPage);
            }

            var startIndex = (currentPage - 1) * perPage;
            var endIndex = Math.min(startIndex + perPage, total);

            // 隐藏所有行/卡片
            rows.forEach(function(row) { row.style.display = 'none'; });
            cards.forEach(function(card) { card.style.display = 'none'; });

            // 仅显示当前页的行/卡片
            visibleRows.slice(startIndex, endIndex).forEach(function(row) { row.style.display = ''; });
            visibleCards.slice(startIndex, endIndex).forEach(function(card) { card.style.display = ''; });

            // 更新计数
            var countEl = document.getElementById('product-count');
            if (countEl) countEl.textContent = total;

            // 更新分页信息
            this._updateProductPagination(total, currentPage, perPage);
        }

        _updateProductPagination(totalCount, currentPage, perPage) {
            var totalPages = Math.ceil(totalCount / perPage);
            var startIndex = (currentPage - 1) * perPage + 1;
            var endIndex = Math.min(currentPage * perPage, totalCount);

            // 更新显示信息
            var showingStart = document.getElementById('product-showing-start');
            var showingEnd = document.getElementById('product-showing-end');
            var totalCountEl = document.getElementById('product-total-count');
            var currentPageEl = document.getElementById('product-current-page');
            var totalPagesEl = document.getElementById('product-total-pages');
            var prevBtn = document.getElementById('product-prev-page');
            var nextBtn = document.getElementById('product-next-page');

            if (showingStart) showingStart.textContent = totalCount > 0 ? startIndex : 0;
            if (showingEnd) showingEnd.textContent = endIndex;
            if (totalCountEl) totalCountEl.textContent = totalCount;
            if (currentPageEl) currentPageEl.textContent = currentPage;
            if (totalPagesEl) totalPagesEl.textContent = totalPages || 1;
            if (prevBtn) prevBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        }

        _initProductFilters() {
            var statusFilter = document.getElementById('product-status-filter');
            if (!statusFilter) return;
            var self = this;
            statusFilter.addEventListener('change', function() {
                // 筛选变化时回到第一页
                if (self._productSetCurrentPage) self._productSetCurrentPage(1);
                self._filterProducts();
            });
        }

        _resetAdminForm() {
            var form = document.getElementById('product-form');
            var statusSelect = document.getElementById('f-status');
            if (!form) return;
            form.reset();
            document.getElementById('edit-id').value = '';
            // 更新模态框标题
            var modalTitle = document.getElementById('product-edit-title');
            if (modalTitle) {
                modalTitle.textContent = 'Add New Product';
            }
            document.getElementById('submit-btn').innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>Add Product';
            document.getElementById('f-is-visible').checked = true;
            // 根据默认 status 设置 button text
            this._updateDefaultButtonText(statusSelect ? statusSelect.value : 'online');
            this._bindStatusChange();
            this._refreshCustomSelect('f-status');
        }

        _updateDefaultButtonText(status) {
            var buttonTextInput = document.getElementById('f-button-text');
            if (!buttonTextInput) return;
            var map = {
                online: 'Now Buy',
                updating: 'Coming Soon',
                development: 'In Development'
            };
            buttonTextInput.value = map[status] || 'Now Buy';
        }

        _bindStatusChange() {
            var statusSelect = document.getElementById('f-status');
            if (!statusSelect || statusSelect.dataset.changeBound) return;
            statusSelect.dataset.changeBound = '1';
            var self = this;
            statusSelect.addEventListener('change', function() {
                // 新增与编辑模式保持一致：切换状态时同步 Button Text
                self._updateDefaultButtonText(this.value);
            });
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
            this._refreshCustomSelect('f-status');
            // 打开模态框，传递 false 表示不重置表单
            window.openProductModal(false);
        }

        _deleteAdminProduct(id, triggerBtn) {
            var self = this;
            // 显示自定义确认对话框
            this._showConfirmDialog('Are you sure you want to delete this product?', function() {
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
                '<div class="confirm-dialog-title">Confirm</div>' +
                '<div class="confirm-dialog-message">' + message + '</div>' +
                '<div class="confirm-dialog-actions">' +
                    '<button class="confirm-btn-cancel">Cancel</button>' +
                    '<button class="confirm-btn-confirm">Confirm</button>' +
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
                confirmBtn.textContent = 'Processing...';
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
    var currentSortField = 'username';
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
    };

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
