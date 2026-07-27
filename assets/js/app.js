/**
 * WenKing App - 应用核心 JavaScript
 * 包含路由管理、AJAX 加载、状态管理、侧边栏控制
 */

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
        }

        _initTabs() {
            // Reseller 页面 Tab 切换
            var resellerTabs = document.querySelectorAll('.reseller-tab');
            var resellerPanels = document.querySelectorAll('.tab-panel');

            resellerTabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var targetTab = this.getAttribute('data-tab');

                    // 更新 tab 激活状态
                    resellerTabs.forEach(function(t) {
                        t.classList.remove('active', 'text-white', 'border-accent-purple');
                        t.classList.add('text-white/70', 'border-transparent');
                    });
                    this.classList.add('active', 'text-white', 'border-accent-purple');
                    this.classList.remove('text-white/70', 'border-transparent');

                    // 更新 panel 显示
                    resellerPanels.forEach(function(panel) {
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
                tab.addEventListener('click', function() {
                    var targetTab = this.getAttribute('data-tab');

                    // 更新 tab 激活状态
                    managerTabs.forEach(function(t) {
                        t.classList.remove('active', 'text-white', 'border-accent-purple');
                        t.classList.add('text-white/70', 'border-transparent');
                    });
                    this.classList.add('active', 'text-white', 'border-accent-purple');
                    this.classList.remove('text-white/70', 'border-transparent');

                    // 更新 panel 显示
                    resellerPanels.forEach(function(panel) {
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
    // 启动应用
    // ============================================
    document.addEventListener('DOMContentLoaded', function () {
        var app = new App();
        app.init();
    });

})();
