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

    /** 重置字段状态（清除验证样式） */
    function resetField(inputId) {
        const el = document.getElementById(inputId + '-error');
        const input = document.getElementById(inputId);
        if (el) {
            el.classList.add('hidden');
        }
        if (input) {
            input.classList.remove('valid', 'invalid');
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
        if (this.value.trim() === '') {
            resetField('login-username');
        } else {
            clearError('login-username');
        }
    });

    document.getElementById('login-password').addEventListener('input', function() {
        if (this.value === '') {
            resetField('login-password');
        } else {
            clearError('login-password');
        }
    });

    // ===== 注册表单实时验证 =====
    document.getElementById('register-username').addEventListener('input', function() {
        if (this.value.trim() === '') {
            resetField('register-username');
        } else if (this.value.trim().length >= 3) {
            clearError('register-username');
        }
    });

    document.getElementById('register-email').addEventListener('input', function() {
        if (this.value.trim() === '') {
            resetField('register-email');
        } else if (isValidEmail(this.value.trim())) {
            clearError('register-email');
        }
    });

    document.getElementById('register-password').addEventListener('input', function() {
        if (this.value === '') {
            resetField('register-password');
        } else if (this.value.length >= 6) {
            clearError('register-password');
        }
        // 同时检查确认密码
        const confirm = document.getElementById('register-confirm');
        if (confirm) {
            if (confirm.value === '') {
                resetField('register-confirm');
            } else if (confirm.value === this.value) {
                clearError('register-confirm');
            } else {
                showError('register-confirm', 'Passwords do not match');
            }
        }
    });

    document.getElementById('register-confirm').addEventListener('input', function() {
        const password = document.getElementById('register-password');
        if (this.value === '') {
            resetField('register-confirm');
        } else if (password && this.value === password.value) {
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

        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;

        // 禁用按钮防止重复提交
        const submitBtn = formLogin.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';

        fetch('/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=login&username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';

            if (data.success) {
                showToast('Logged in as ' + data.user.username, 'success');
                setTimeout(function() {
                    window.location.href = '/app.php';
                }, 1000);
            } else {
                showToast(data.message || 'Login failed', 'error');
            }
        })
        .catch(function(err) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';
            showToast('Network error, please try again', 'error');
        });
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

        const username = document.getElementById('register-username').value.trim();
        const email = document.getElementById('register-email').value.trim();
        const password = document.getElementById('register-password').value;

        // 禁用按钮防止重复提交
        const submitBtn = formRegister.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';

        fetch('/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=register&username=' + encodeURIComponent(username) + '&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';

            if (data.success) {
                showToast('Account created for ' + username + '!', 'success');
                setTimeout(function() {
                    switchForm('login-form');
                    document.getElementById('login-username').value = username;
                }, 1000);
            } else {
                showToast(data.message || 'Registration failed', 'error');
            }
        })
        .catch(function(err) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
            showToast('Network error, please try again', 'error');
        });
    });

    // ===== 密码强度检测 =====
    const passwordInput = document.getElementById('register-password');
    const confirmInput = document.getElementById('register-confirm');
    const strengthContainer = document.getElementById('password-strength');
    const strengthText = document.getElementById('strength-text');
    const strengthTips = document.getElementById('strength-tips');
    const matchContainer = document.getElementById('password-match');
    const matchIcon = document.getElementById('match-icon');
    const matchText = document.getElementById('match-text');
    
    const bars = [
        document.getElementById('strength-bar-1'),
        document.getElementById('strength-bar-2'),
        document.getElementById('strength-bar-3'),
        document.getElementById('strength-bar-4')
    ];

    // 密码强度检测函数
    function checkPasswordStrength(password) {
        let strength = 0;
        let tips = [];

        if (!password) {
            return { strength: 0, tips: [] };
        }

        // Length check
        if (password.length >= 8) {
            strength++;
        } else {
            tips.push('At least 8 characters');
        }

        // Lowercase check
        if (/[a-z]/.test(password)) {
            strength++;
        } else {
            tips.push('Add lowercase letter');
        }

        // Uppercase check
        if (/[A-Z]/.test(password)) {
            strength++;
        } else {
            tips.push('Add uppercase letter');
        }

        // Number check
        if (/[0-9]/.test(password)) {
            strength++;
        } else {
            tips.push('Add a number');
        }

        // Special character check
        if (/[^a-zA-Z0-9]/.test(password)) {
            strength++;
        } else {
            tips.push('Add special character');
        }

        // 根据得分调整强度等级
        let level;
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
        const level = result.strength;
        const tips = result.tips;

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
        const strengthLabels = ['', 'Very Weak', 'Weak', 'Good', 'Strong'];
        strengthText.textContent = strengthLabels[level] || '';
        strengthText.className = 'password-strength-text level-' + level;

        // Update tips
        if (tips.length > 0 && level < 4) {
            strengthTips.textContent = 'Tip: ' + tips.slice(0, 2).join(', ');
        } else {
            strengthTips.textContent = level === 4 ? 'Password is strong!' : '';
        }
    }

    // 密码匹配检测函数
    function checkPasswordMatch() {
        if (!confirmInput || !matchContainer) return;

        const password = passwordInput.value;
        const confirm = confirmInput.value;

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

    // 监听密码输入（密码强度）
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const result = checkPasswordStrength(password);
            updateStrengthDisplay(result);
            checkPasswordMatch();
        });
    }

    // 监听确认密码输入（密码匹配）
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            checkPasswordMatch();
        });
    }

    // ===== 忘记密码表单逻辑 =====
    const forgotStep1 = document.getElementById('form-forgot-step1');
    const forgotStep2 = document.getElementById('form-forgot-step2');
    const step1Indicator = document.getElementById('step-1-indicator');
    const step2Indicator = document.getElementById('step-2-indicator');
    const stepConnector = document.getElementById('step-connector');
    const displayEmail = document.getElementById('display-email');
    const resendBtn = document.getElementById('resend-code-btn');
    let verificationEmail = '';

    // 忘记密码表单实时验证
    const forgotEmailInput = document.getElementById('forgot-email');
    const forgotCodeInput = document.getElementById('forgot-code');
    const forgotNewPasswordInput = document.getElementById('forgot-new-password');
    const forgotConfirmPasswordInput = document.getElementById('forgot-confirm-password');

    if (forgotEmailInput) {
        forgotEmailInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                resetField('forgot-email');
            } else if (isValidEmail(this.value.trim())) {
                clearError('forgot-email');
            }
        });
    }

    if (forgotCodeInput) {
        forgotCodeInput.addEventListener('input', function() {
            // 只允许输入数字
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value === '') {
                resetField('forgot-code');
            } else if (this.value.length === 6) {
                clearError('forgot-code');
            }
        });
    }

    // ===== 忘记密码密码强度检测 =====
    const forgotPasswordInput = document.getElementById('forgot-new-password');
    const forgotConfirmInput = document.getElementById('forgot-confirm-password');
    const forgotStrengthContainer = document.getElementById('forgot-password-strength');
    const forgotStrengthText = document.getElementById('forgot-strength-text');
    const forgotStrengthTips = document.getElementById('forgot-strength-tips');
    const forgotMatchContainer = document.getElementById('forgot-password-match');
    const forgotMatchIcon = document.getElementById('forgot-match-icon');
    const forgotMatchText = document.getElementById('forgot-match-text');

    const forgotBars = [
        document.getElementById('forgot-strength-bar-1'),
        document.getElementById('forgot-strength-bar-2'),
        document.getElementById('forgot-strength-bar-3'),
        document.getElementById('forgot-strength-bar-4')
    ];

    // 更新忘记密码密码强度显示
    function updateForgotStrengthDisplay(result) {
        const level = result.strength;
        const tips = result.tips;

        // 显示/隐藏容器
        if (forgotPasswordInput.value) {
            forgotStrengthContainer.classList.remove('hidden');
        } else {
            forgotStrengthContainer.classList.add('hidden');
            return;
        }

        // 更新进度条
        forgotBars.forEach(function(bar, index) {
            // 移除所有等级类
            bar.classList.remove('active', 'level-1', 'level-2', 'level-3', 'level-4');

            if (index < level) {
                bar.classList.add('active', 'level-' + level);
            }
        });

        // 更新文本
        const strengthLabels = ['', 'Very Weak', 'Weak', 'Good', 'Strong'];
        forgotStrengthText.textContent = strengthLabels[level] || '';
        forgotStrengthText.className = 'password-strength-text level-' + level;

        // Update tips
        if (tips.length > 0 && level < 4) {
            forgotStrengthTips.textContent = 'Tip: ' + tips.slice(0, 2).join(', ');
        } else {
            forgotStrengthTips.textContent = level === 4 ? 'Password is strong!' : '';
        }
    }

    // 忘记密码密码匹配检测
    function checkForgotPasswordMatch() {
        if (!forgotConfirmInput || !forgotMatchContainer) return;

        const password = forgotPasswordInput.value;
        const confirm = forgotConfirmInput.value;

        if (!confirm) {
            forgotMatchContainer.classList.add('hidden');
            return;
        }

        forgotMatchContainer.classList.remove('hidden');

        if (password === confirm) {
            forgotMatchIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            forgotMatchIcon.className = 'password-match-icon match';
            forgotMatchText.textContent = 'Passwords match';
            forgotMatchText.className = 'password-match-text match';
        } else {
            forgotMatchIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            forgotMatchIcon.className = 'password-match-icon no-match';
            forgotMatchText.textContent = 'Passwords do not match';
            forgotMatchText.className = 'password-match-text no-match';
        }
    }

    if (forgotNewPasswordInput) {
        forgotNewPasswordInput.addEventListener('input', function() {
            if (this.value === '') {
                resetField('forgot-new-password');
            } else if (this.value.length >= 6) {
                clearError('forgot-new-password');
            }

            // 密码强度检测
            const password = this.value;
            const result = checkPasswordStrength(password);
            updateForgotStrengthDisplay(result);

            // 同时检查确认密码
            if (forgotConfirmPasswordInput) {
                if (forgotConfirmPasswordInput.value === '') {
                    resetField('forgot-confirm-password');
                } else if (forgotConfirmPasswordInput.value === this.value) {
                    clearError('forgot-confirm-password');
                } else {
                    showError('forgot-confirm-password', 'Passwords do not match');
                }
            }

            // 更新匹配显示
            checkForgotPasswordMatch();
        });
    }

    if (forgotConfirmPasswordInput) {
        forgotConfirmPasswordInput.addEventListener('input', function() {
            if (this.value === '') {
                resetField('forgot-confirm-password');
            } else if (forgotNewPasswordInput && this.value === forgotNewPasswordInput.value) {
                clearError('forgot-confirm-password');
            }

            // 更新匹配显示
            checkForgotPasswordMatch();
        });
    }

    // 更新步骤指示器
    function updateStepIndicator(step) {
        if (step === 1) {
            step1Indicator.classList.add('active');
            step1Indicator.classList.remove('completed');
            step2Indicator.classList.remove('active', 'completed');
            stepConnector.classList.remove('active');
        } else if (step === 2) {
            step1Indicator.classList.remove('active');
            step1Indicator.classList.add('completed');
            step2Indicator.classList.add('active');
            step2Indicator.classList.remove('completed');
            stepConnector.classList.add('active');
        }
    }

    // 步骤1表单提交 - 发送验证码
    if (forgotStep1) {
        forgotStep1.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateEmail('forgot-email')) return;

            verificationEmail = forgotEmailInput.value.trim();

            // 禁用按钮
            const submitBtn = forgotStep1.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            fetch('/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=check_email&email=' + encodeURIComponent(verificationEmail)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Verification Code';

                if (data.success) {
                    showToast('Verification code sent to ' + verificationEmail, 'success');
                    if (displayEmail) {
                        displayEmail.textContent = verificationEmail;
                    }
                    setTimeout(function() {
                        forgotStep1.classList.add('hidden');
                        forgotStep2.classList.remove('hidden');
                        updateStepIndicator(2);
                    }, 500);
                } else {
                    showToast(data.message || 'Email not found', 'error');
                    showError('forgot-email', data.message || 'This email is not registered');
                }
            })
            .catch(function(err) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Verification Code';
                showToast('Network error, please try again', 'error');
            });
        });
    }

    // 步骤2表单提交 - 重置密码
    if (forgotStep2) {
        forgotStep2.addEventListener('submit', function(e) {
            e.preventDefault();

            let valid = true;

            // 验证码验证
            if (forgotCodeInput.value.trim() === '') {
                showError('forgot-code', 'Please enter the verification code');
                valid = false;
            } else if (forgotCodeInput.value.length !== 6) {
                showError('forgot-code', 'Code must be 6 digits');
                valid = false;
            }

            // 新密码验证
            if (!validatePassword('forgot-new-password')) valid = false;

            // 确认密码验证
            if (!validateConfirm('forgot-confirm-password', 'forgot-new-password')) valid = false;

            if (!valid) return;

            // 禁用按钮
            const submitBtn = forgotStep2.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Resetting...';

            fetch('/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=reset_password&code=' + encodeURIComponent(forgotCodeInput.value.trim()) + '&new_password=' + encodeURIComponent(forgotNewPasswordInput.value)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Reset Password';

                if (data.success) {
                    showToast('Password reset successfully!', 'success');
                    setTimeout(function() {
                        forgotStep1.classList.remove('hidden');
                        forgotStep2.classList.add('hidden');
                        forgotStep1.reset();
                        forgotStep2.reset();
                        updateStepIndicator(1);
                        switchForm('login-form');
                    }, 1500);
                } else {
                    showToast(data.message || 'Password reset failed', 'error');
                    if (data.message && data.message.includes('验证码')) {
                        showError('forgot-code', data.message);
                    }
                }
            })
            .catch(function(err) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Reset Password';
                showToast('Network error, please try again', 'error');
            });
        });
    }

    // 重发验证码
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            if (!verificationEmail) return;

            fetch('/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=check_email&email=' + encodeURIComponent(verificationEmail)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('Verification code resent to ' + verificationEmail, 'success');
                } else {
                    showToast(data.message || 'Failed to resend code', 'error');
                }
            })
            .catch(function(err) {
                showToast('Network error, please try again', 'error');
            });
        });
    }

})();