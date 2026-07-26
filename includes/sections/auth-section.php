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
                <p class="text-white/50 text-sm">Sign in to your account to continue</p>
            </div>

            <!-- 表单 -->
            <form id="form-login" class="space-y-5" novalidate autocomplete="off">
                <!-- 用户名/邮箱 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        Username or Email
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
                               placeholder="Enter your username or email"
                               required
                               autocomplete="username">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="login-username-error"></p>
                </div>

                <!-- 密码 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        Password
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
                               placeholder="Enter your password"
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
                        <span class="text-sm text-white/50 select-none">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-accent-purple hover:text-accent-cyan transition">
                        Forgot password?
                    </a>
                </div>

                <!-- 登录按钮 -->
                <button type="submit" class="btn-primary w-full py-3 rounded-lg font-semibold text-center">
                    Sign In
                </button>
            </form>

            <!-- 切换到注册 -->
            <div class="mt-6 text-center">
                <span class="text-white/50 text-sm">Don't have an account?</span>
                <button type="button"
                        class="auth-switch-btn text-accent-purple hover:text-accent-cyan transition font-medium ml-1"
                        data-show="register-form">
                    Register
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
                <p class="text-white/50 text-sm">Join WenKing and get started today</p>
            </div>

            <!-- 表单 -->
            <form id="form-register" class="space-y-5" novalidate autocomplete="off">
                <!-- 用户名 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        Username
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
                               placeholder="Choose a username"
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
                        Email
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
                               placeholder="Enter your email"
                               required
                               autocomplete="email">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="register-email-error"></p>
                </div>

                <!-- 密码 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        Password
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
                               placeholder="Create a password"
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
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="register-password-error"></p>
                </div>

                <!-- 确认密码 -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </span>
                        <input type="password"
                               id="register-confirm"
                               name="confirm"
                               class="auth-input w-full pl-10 pr-4 py-3 rounded-lg"
                               placeholder="Confirm your password"
                               required
                               autocomplete="new-password">
                    </div>
                    <p class="form-error hidden text-red-400 text-xs mt-1" id="register-confirm-error"></p>
                </div>

                <!-- 注册按钮 -->
                <button type="submit" class="btn-primary w-full py-3 rounded-lg font-semibold text-center">
                    Create Account
                </button>
            </form>

            <!-- 切换到登录 -->
            <div class="mt-6 text-center">
                <span class="text-white/50 text-sm">Already have an account?</span>
                <button type="button"
                        class="auth-switch-btn text-accent-purple hover:text-accent-cyan transition font-medium ml-1"
                        data-show="login-form">
                    Sign In
                </button>
            </div>
        </div>
    </div>
</div>