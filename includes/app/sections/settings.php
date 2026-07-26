<?php
/**
 * 设置页面内容
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">Settings</span>
    </h1>
    <p class="text-white/60">Manage your account and preferences.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- 账户信息 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white">Account Information</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-white/60 mb-2">Username</label>
                <input type="text" value="WenKing_User" class="app-input w-full" readonly>
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2">Email</label>
                <input type="email" value="user@example.com" class="app-input w-full" readonly>
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2">Subscription</label>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-accent-purple/20 text-accent-purple rounded-full text-sm font-medium">Pro Plan</span>
                    <span class="text-sm text-white/40">Expires 2026-08-25</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 偏好设置 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white">Preferences</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-white/60 mb-2">Language</label>
                <select class="app-select w-full">
                    <option value="en">English</option>
                    <option value="zh">简体中文</option>
                </select>
            </div>
            <div class="flex items-center justify-between py-3 border-t border-white/10">
                <div>
                    <p class="text-white font-medium">Email Notifications</p>
                    <p class="text-sm text-white/40">Receive updates about your subscription</p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox" checked>
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="flex items-center justify-between py-3 border-t border-white/10">
                <div>
                    <p class="text-white font-medium">Auto Updates</p>
                    <p class="text-sm text-white/40">Automatically download latest versions</p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox" checked>
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- 安全设置 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white">Security</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium">Two-Factor Authentication</p>
                    <p class="text-sm text-white/40">Add extra security to your account</p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    Change Password
                </button>
            </div>
        </div>
    </div>

    <!-- 安全设置 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white">Security</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium">Two-Factor Authentication</p>
                    <p class="text-sm text-white/40">Add extra security to your account</p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    Change Password
                </button>
            </div>
        </div>
    </div>

    <!-- 安全设置 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white">Security</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium">Two-Factor Authentication</p>
                    <p class="text-sm text-white/40">Add extra security to your account</p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    Change Password
                </button>
            </div>
        </div>
    </div>

    <!-- 安全设置 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white">Security</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium">Two-Factor Authentication</p>
                    <p class="text-sm text-white/40">Add extra security to your account</p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    Change Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 保存按钮 -->
<div class="flex justify-center mt-6">
    <button class="btn-primary px-6 py-3 rounded-lg font-semibold">
        Save Changes
    </button>
</div>
