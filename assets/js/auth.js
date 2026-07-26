/**
 * 用户认证页面交互脚本
 * 功能：表单切换、实时验证、密码显示/隐藏、提交处理
 */
(function() {
    'use strict';

    // ===== DOM 元素 =====
    const loginForm    = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const formLogin    = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');

    // ===== 表单切换 =====
    function switchForm(showId) {
        const allForms = document.querySelectorAll('.auth-form');
        allForms.forEach(function(f) { f.classList.remove('active'); });

        const target = document.getElementById(showId);
        if (target) {
            target.classList.add('active');
        }
    }

    // 绑定切换按钮
    document.querySelectorAll('.auth-switch-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            switchForm(this.dataset.show);
        });
    });

    // ===== 密码显示/隐藏切换 =====
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            // 切换图标
            const svg = this.querySelector('svg');
            if (isPassword) {
                // 隐藏密码时显示闭眼图标
                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        });
    });

    // ===== 输入框实时验证 =====

    /** 显示/隐藏错误 */
    function showError(inputId, message) {
        const el = document.getElementById(inputId + '-error');
        const input = document.getElementById(inputId);
        if (el) {
            el.textContent = message;
            el.classList.remove('hidden');
        }
        if (input) {
            input.classList.remove('valid');
            input.classList.add('invalid');
        }
    }

    function clearError(inputId) {
        const el = document.getElementById(inputId + '-error');
        const input = document.getElementById(inputId);
        if (el) {
            el.classList.add('hidden');
        }
        if (input) {
            input.classList.remove('invalid');
            input.classList.add('valid');
        }
    }

    /** 验证邮箱格式 */
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    /** 验证用户名 */
    function validateUsername(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return false;
        const val = input.value.trim();
        if (val === '') {
            showError(inputId, 'This field is required');
            return false;
        }
        if (val.length < 3) {
            showError(inputId, 'Username must be at least 3 characters');
            return false;
        }
        if (val.length > 20) {
            showError(inputId, 'Username must be at most 20 characters');
            return false;
        }
        clearError(inputId);
        return true;
    }

    /** 验证邮箱 */
    function validateEmail(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return false;
        const val = input.value.trim();
        if (val === '') {
            showError(inputId, 'This field is required');
            return false;
        }
        if (!isValidEmail(val)) {
            showError(inputId, 'Please enter a valid email address');
            return false;
        }
        clearError(inputId);
        return true;
    }

    /** 验证密码 */
    function validatePassword(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return false;
        const val = input.value;
        if (val === '') {
            showError(inputId, 'This field is required');
            return false;
        }
        if (val.length < 6) {
            showError(inputId, 'Password must be at least 6 characters');
            return false;
        }
        clearError(inputId);
        return true;
    }

    /** 验证确认密码 */
    function validateConfirm(inputId, passwordId) {
        const input = document.getElementById(inputId);
        const password = document.getElementById(passwordId);
        if (!input || !password) return false;
        const val = input.value;
        if (val === '') {
            showError(inputId, 'This field is required');
            return false;
        }
        if (val !== password.value) {
            showError(inputId, 'Passwords do not match');
            return false;
        }
        clearError(inputId);
        return true;
    }

    /** 验证必填 */
    function validateRequired(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return false;
        if (input.value.trim() === '') {
            showError(inputId, 'This field is required');
            return false;
        }
        clearError(inputId);
        return true;
    }

    // ===== 登录表单实时验证 =====
    document.getElementById('login-username').addEventListener('input', function() {
        if (this.value.trim() !== '') {
            clearError('login-username');
        }
    });

    document.getElementById('login-password').addEventListener('input', function() {
        if (this.value !== '') {
            clearError('login-password');
        }
    });

    // ===== 注册表单实时验证 =====
    document.getElementById('register-username').addEventListener('input', function() {
        if (this.value.trim().length >= 3) {
            clearError('register-username');
        }
    });

    document.getElementById('register-email').addEventListener('input', function() {
        if (isValidEmail(this.value.trim())) {
            clearError('register-email');
        }
    });

    document.getElementById('register-password').addEventListener('input', function() {
        if (this.value.length >= 6) {
            clearError('register-password');
        }
        // 同时检查确认密码
        const confirm = document.getElementById('register-confirm');
        if (confirm && confirm.value !== '') {
            if (confirm.value === this.value) {
                clearError('register-confirm');
            } else {
                showError('register-confirm', 'Passwords do not match');
            }
        }
    });

    document.getElementById('register-confirm').addEventListener('input', function() {
        const password = document.getElementById('register-password');
        if (password && this.value === password.value) {
            clearError('register-confirm');
        }
    });

    // ===== Toast 提示 =====
    function showToast(message, type) {
        // 移除已有 toast
        const existing = document.querySelector('.auth-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'auth-toast ' + (type || 'success');
        toast.textContent = message;
        document.body.appendChild(toast);

        // 3 秒后自动移除
        setTimeout(function() {
            if (toast.parentNode) toast.remove();
        }, 3000);
    }

    // ===== 登录表单提交 =====
    formLogin.addEventListener('submit', function(e) {
        e.preventDefault();

        let valid = true;
        valid = validateRequired('login-username') && valid;
        valid = validatePassword('login-password') && valid;

        if (!valid) return;

        // 模拟登录（后续接入后端 API）
        const username = document.getElementById('login-username').value.trim();
        const remember = document.getElementById('remember-me').checked;

        showToast('Logged in as ' + username + ' (demo)', 'success');

        // 跳转到仪表盘
        setTimeout(function() {
            window.location.href = '/app.php';
        }, 1000);
    });

    // ===== 注册表单提交 =====
    formRegister.addEventListener('submit', function(e) {
        e.preventDefault();

        let valid = true;
        valid = validateUsername('register-username') && valid;
        valid = validateEmail('register-email') && valid;
        valid = validatePassword('register-password') && valid;
        valid = validateConfirm('register-confirm', 'register-password') && valid;

        if (!valid) return;

        // 模拟注册（后续接入后端 API）
        const username = document.getElementById('register-username').value.trim();

        showToast('Account created for ' + username + '! (demo)', 'success');

        // 切换到登录表单
        setTimeout(function() {
            switchForm('login-form');
            document.getElementById('login-username').value = username;
        }, 1000);
    });

})();