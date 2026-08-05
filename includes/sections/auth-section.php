<?php
/**
 * 用户认证区块
 * 包含登录表单和注册表单，支持切换
 */
?>

<!-- 认证表单容器 -->
<div class="w-full max-w-md mx-auto px-4 py-8">
    <div class="auth-card glass-card p-8 rounded-2xl">

        <!-- ===== 登录表单 ===== -->
        <div id="login-form" class="auth-form active">
            <!-- 标题 -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-display font-bold mb-2">
                    <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                        Welcome Back
                    </span>
                </h2>
                <p class="text-white/50 text-sm"><?php _e('auth.login_subtitle'); ?></p>
            </div>

            <!-- 表单 -->
            <form id="form-login" class="space-y-5" novalidate autocomplete="off">
                <!-- 用户名/邮箱 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.username_email'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <input type="text"
                               id="login-username"
                               name="username"
                               class="auth-input w-full pl-10 pr-4 py-3 rounded-lg"
                               placeholder="<?php _e('auth.username_email_ph'); ?>"
                               required
                               autocomplete="username">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="login-username-error"></p>
                </div>

                <!-- 密码 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.password'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input type="password"
                               id="login-password"
                               name="password"
                               class="auth-input w-full pl-10 pr-12 py-3 rounded-lg"
                               placeholder="<?php _e('auth.password_ph'); ?>"
                               required
                               autocomplete="current-password">
                        <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition"
                                data-target="login-password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="login-password-error"></p>
                </div>

                <!-- 记住我 + 忘记密码 -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               id="remember-me"
                               name="remember"
                               class="auth-checkbox">
                        <span class="text-sm text-white/50 select-none"><?php _e('auth.remember_me'); ?></span>
                    </label>
                    <a href="#" class="auth-switch-btn text-sm text-accent-purple hover:text-accent-cyan transition" data-show="forgot-form">
                        <?php _e('auth.forgot_password'); ?>
                    </a>
                </div>

                <!-- 登录按钮 -->
                <button type="submit" class="btn-primary w-full">
                    <?php _e('auth.sign_in'); ?>
                </button>
            </form>

            <!-- 切换到注册 -->
            <div class="mt-6 text-center">
                <span class="text-white/50 text-sm"><?php _e('auth.no_account'); ?></span>
                <button type="button"
                        class="auth-switch-btn text-accent-purple hover:text-accent-cyan transition font-medium ml-1"
                        data-show="register-form">
                    <?php _e('auth.register'); ?>
                </button>
            </div>
        </div>

        <!-- ===== 注册表单 ===== -->
        <div id="register-form" class="auth-form">
            <!-- 标题 -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-display font-bold mb-2">
                    <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                        Create Account
                    </span>
                </h2>
                <p class="text-white/50 text-sm"><?php _e('auth.register_subtitle'); ?></p>
            </div>

            <!-- 表单 -->
            <form id="form-register" class="space-y-5" novalidate autocomplete="off">
                <!-- 用户名 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.username'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <input type="text"
                               id="register-username"
                               name="username"
                               class="auth-input w-full pl-10 pr-4 py-3 rounded-lg"
                               placeholder="<?php _e('auth.username_ph'); ?>"
                               required
                               minlength="3"
                               maxlength="20"
                               autocomplete="username">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="register-username-error"></p>
                </div>

                <!-- 邮箱 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.email'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </span>
                        <input type="email"
                               id="register-email"
                               name="email"
                               class="auth-input w-full pl-10 pr-4 py-3 rounded-lg"
                               placeholder="<?php _e('auth.email_ph'); ?>"
                               required
                               autocomplete="email">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="register-email-error"></p>
                </div>

                <!-- 密码 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.password'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input type="password"
                               id="register-password"
                               name="password"
                               class="auth-input w-full pl-10 pr-12 py-3 rounded-lg"
                               placeholder="<?php _e('auth.create_password'); ?>"
                               required
                               minlength="6"
                               autocomplete="new-password">
                        <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition"
                                data-target="register-password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <!-- 密码强度指示器 -->
                    <div id="password-strength" class="password-strength-container hidden">
                        <div class="password-strength-bars">
                            <div class="password-strength-bar" id="strength-bar-1"></div>
                            <div class="password-strength-bar" id="strength-bar-2"></div>
                            <div class="password-strength-bar" id="strength-bar-3"></div>
                            <div class="password-strength-bar" id="strength-bar-4"></div>
                        </div>
                        <div class="password-strength-info">
                            <span class="password-strength-text" id="strength-text">Weak</span>
                            <span class="password-strength-tips" id="strength-tips"></span>
                        </div>
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="register-password-error"></p>
                </div>

                <!-- 确认密码 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.confirm_password'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input type="password"
                               id="register-confirm"
                               name="confirm"
                               class="auth-input w-full pl-10 pr-12 py-3 rounded-lg"
                               placeholder="<?php _e('auth.confirm_password_ph'); ?>"
                               required
                               autocomplete="new-password">
                        <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition"
                                data-target="register-confirm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <!-- 密码匹配指示器 -->
                    <div id="password-match" class="password-match-container hidden">
                        <span class="password-match-icon" id="match-icon"></span>
                        <span class="password-match-text" id="match-text"></span>
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="register-confirm-error"></p>
                </div>

                <!-- 注册按钮 -->
                <button type="submit" class="btn-primary w-full">
                    <?php _e('auth.create_account'); ?>
                </button>
            </form>

            <!-- 切换到登录 -->
            <div class="mt-6 text-center">
                <span class="text-white/50 text-sm"><?php _e('auth.has_account'); ?></span>
                <button type="button"
                        class="auth-switch-btn text-accent-purple hover:text-accent-cyan transition font-medium ml-1"
                        data-show="login-form">
                    <?php _e('auth.sign_in'); ?>
                </button>
            </div>
        </div>

        <!-- ===== 忘记密码表单 ===== -->
        <div id="forgot-form" class="auth-form">
            <!-- 标题 -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-display font-bold mb-2">
                    <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                        Forgot Password
                    </span>
                </h2>
                <p class="text-white/50 text-sm"><?php _e('auth.forgot_subtitle'); ?></p>
            </div>

            <!-- 步骤指示器 -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center justify-center">
                    <div class="step-indicator active" id="step-1-indicator">
                        <span class="step-number">1</span>
                        <span class="step-label"><?php _e('auth.step1'); ?></span>
                    </div>
                    <div class="step-connector" id="step-connector"></div>
                    <div class="step-indicator" id="step-2-indicator">
                        <span class="step-number">2</span>
                        <span class="step-label"><?php _e('auth.step2'); ?></span>
                    </div>
                </div>
            </div>

            <!-- 步骤 1: 验证邮箱 -->
            <form id="form-forgot-step1" class="space-y-5">
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.email_address'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </span>
                        <input type="email"
                               id="forgot-email"
                               name="email"
                               class="auth-input w-full pl-10 pr-4 py-3 rounded-lg"
                               placeholder="<?php _e('auth.email_registered_ph'); ?>"
                               required
                               autocomplete="email">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="forgot-email-error"></p>
                </div>

                <button type="submit" class="btn-primary w-full">
                    <?php _e('auth.send_code'); ?>
                </button>
            </form>

            <!-- 步骤 2: 重置密码 -->
            <form id="form-forgot-step2" class="space-y-5 hidden">
                <div class="bg-accent-purple/10 border border-accent-purple/30 rounded-lg p-4 mb-4">
                    <p class="text-sm text-white/80">
                        <svg class="w-5 h-5 inline mr-2 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php _e('auth.code_sent'); ?> <strong id="display-email" class="text-accent-cyan"></strong>
                    </p>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.verification_code'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </span>
                        <input type="text"
                               id="forgot-code"
                               name="code"
                               class="auth-input w-full px-10 py-3 rounded-lg text-center tracking-widest"
                               placeholder="<?php _e('auth.code_ph'); ?>"
                               required
                               maxlength="6"
                               pattern="[0-9]{6}">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="forgot-code-error"></p>
                    <p class="text-xs text-white/50 mt-2">
                        <?php _e('auth.no_code'); ?> 
                        <button type="button" id="resend-code-btn" class="text-accent-purple hover:text-accent-cyan transition">
                            <?php _e('auth.resend'); ?>
                        </button>
                    </p>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.new_password'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input type="password"
                               id="forgot-new-password"
                               name="new-password"
                               class="auth-input w-full pl-10 pr-12 py-3 rounded-lg"
                               placeholder="<?php _e('auth.new_password_ph'); ?>"
                               required
                               minlength="6"
                               autocomplete="new-password">
                        <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition"
                                data-target="forgot-new-password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <!-- 密码强度指示器 -->
                    <div id="forgot-password-strength" class="password-strength-container hidden">
                        <div class="password-strength-bars">
                            <div class="password-strength-bar" id="forgot-strength-bar-1"></div>
                            <div class="password-strength-bar" id="forgot-strength-bar-2"></div>
                            <div class="password-strength-bar" id="forgot-strength-bar-3"></div>
                            <div class="password-strength-bar" id="forgot-strength-bar-4"></div>
                        </div>
                        <div class="password-strength-info">
                            <span class="password-strength-text" id="forgot-strength-text">Weak</span>
                            <span class="password-strength-tips" id="forgot-strength-tips"></span>
                        </div>
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="forgot-new-password-error"></p>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        <?php _e('auth.confirm_new_password'); ?>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input type="password"
                               id="forgot-confirm-password"
                               name="confirm-password"
                               class="auth-input w-full pl-10 pr-12 py-3 rounded-lg"
                               placeholder="<?php _e('auth.confirm_new_ph'); ?>"
                               required
                               autocomplete="new-password">
                        <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition"
                                data-target="forgot-confirm-password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <!-- 密码匹配指示器 -->
                    <div id="forgot-password-match" class="password-match-container hidden">
                        <span class="password-match-icon" id="forgot-match-icon"></span>
                        <span class="password-match-text" id="forgot-match-text"></span>
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="forgot-confirm-password-error"></p>
                </div>

                <button type="submit" class="btn-primary w-full">
                    <?php _e('auth.reset_password'); ?>
                </button>
            </form>

            <!-- 切换到登录 -->
            <div class="mt-6 text-center">
                <span class="text-white/50 text-sm"><?php _e('auth.remember_pwd'); ?></span>
                <button type="button"
                        class="auth-switch-btn text-accent-purple hover:text-accent-cyan transition font-medium ml-1"
                        data-show="login-form">
                    <?php _e('auth.back_to_login'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* 认证表单提交按钮统一高度 */
.auth-form button[type="submit"].btn-primary {
    height: 50px;
}

/* 步骤指示器样式 */
.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    width: 95px;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.step-indicator.active {
    opacity: 1;
}

.step-indicator.completed {
    opacity: 1;
}

.step-number {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.5);
    transition: all 0.3s ease;
}

.step-indicator.active .step-number {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(6, 182, 212, 0.2));
    border-color: #8b5cf6;
    color: #8b5cf6;
    box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
}

.step-indicator.completed .step-number {
    background: linear-gradient(135deg, #10b981, #34d399);
    border-color: #10b981;
    color: white;
}

.step-label {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
    font-weight: 500;
    text-align: center;
}

.step-indicator.active .step-label,
.step-indicator.completed .step-label {
    color: rgba(255, 255, 255, 0.9);
}

.step-connector {
    width: 4rem;
    height: 2px;
    background: rgba(255, 255, 255, 0.1);
    margin: 0 1rem;
    position: relative;
    overflow: hidden;
    align-self: center;
}

.step-connector.active::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, #10b981, #8b5cf6);
    animation: connectorFill 0.5s ease forwards;
}

@keyframes connectorFill {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(0);
    }
}

/* 密码强度指示器样式 */
.password-strength-container {
    margin-top: 0.75rem;
    animation: fadeIn 0.3s ease;
}

.password-strength-bars {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}

.password-strength-bar {
    flex: 1;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.password-strength-bar.active::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 2px;
    animation: barFill 0.4s ease forwards;
}

/* 强度等级颜色 */
.password-strength-bar.level-1::after {
    background: linear-gradient(90deg, #ef4444, #f87171);
    box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
}

.password-strength-bar.level-2::after {
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
    box-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
}

.password-strength-bar.level-3::after {
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.4);
}

.password-strength-bar.level-4::after {
    background: linear-gradient(90deg, #10b981, #34d399);
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
}

.password-strength-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
}

.password-strength-text {
    font-weight: 600;
    transition: color 0.3s ease;
}

.password-strength-text.level-0 { color: rgba(255, 255, 255, 0.4); }
.password-strength-text.level-1 { color: #ef4444; }
.password-strength-text.level-2 { color: #f59e0b; }
.password-strength-text.level-3 { color: #3b82f6; }
.password-strength-text.level-4 { color: #10b981; }

.password-strength-tips {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.7rem;
}

@keyframes barFill {
    from {
        transform: scaleX(0);
    }
    to {
        transform: scaleX(1);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 密码匹配指示器样式 */
.password-match-container {
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: fadeIn 0.3s ease;
}

.password-match-icon {
    width: 1.25rem;
    height: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-match-icon.match {
    color: #10b981;
}

.password-match-icon.no-match {
    color: #ef4444;
}

.password-match-text {
    font-size: 0.75rem;
    font-weight: 500;
}

.password-match-text.match {
    color: #10b981;
}

.password-match-text.no-match {
    color: #ef4444;
}

/* 现代化渐变按钮 */
.btn-uiverse {
    position: relative;
    cursor: pointer;
    padding: 0.875rem 2rem;
    text-align: center;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    font-size: 0.875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: white;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
    border: 2px solid transparent;
    border-radius: 0.75rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.btn-uiverse::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #8b5cf6 0%, #06b6d4 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 0;
}

.btn-uiverse::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
    transform: scale(0);
    transition: transform 0.6s ease;
    z-index: 1;
}

.btn-uiverse:hover::before {
    opacity: 1;
}

.btn-uiverse:hover::after {
    transform: scale(1);
}

.btn-uiverse:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 40px rgba(139, 92, 246, 0.4), 0 0 60px rgba(6, 182, 212, 0.3);
    border-color: rgba(139, 92, 246, 0.5);
}

.btn-uiverse:active {
    transform: translateY(0);
}

.btn-uiverse-text {
    position: relative;
    z-index: 10;
    background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    transition: all 0.3s ease;
}

.btn-uiverse:hover .btn-uiverse-text {
    background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
}

/* 动态边框效果 */
.btn-uiverse-border {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 0.75rem;
    padding: 2px;
    background: linear-gradient(135deg, #8b5cf6, #06b6d4, #8b5cf6);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 2;
}

.btn-uiverse:hover .btn-uiverse-border {
    opacity: 1;
    animation: borderRotate 3s linear infinite;
}

@keyframes borderRotate {
    0% {
        background: linear-gradient(0deg, #8b5cf6, #06b6d4, #8b5cf6);
    }
    25% {
        background: linear-gradient(90deg, #8b5cf6, #06b6d4, #8b5cf6);
    }
    50% {
        background: linear-gradient(180deg, #8b5cf6, #06b6d4, #8b5cf6);
    }
    75% {
        background: linear-gradient(270deg, #8b5cf6, #06b6d4, #8b5cf6);
    }
    100% {
        background: linear-gradient(360deg, #8b5cf6, #06b6d4, #8b5cf6);
    }
}

/* 光效扫过动画 */
.btn-uiverse-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.8s ease;
    z-index: 5;
}

.btn-uiverse:hover .btn-uiverse-shine {
    left: 100%;
}
</style>