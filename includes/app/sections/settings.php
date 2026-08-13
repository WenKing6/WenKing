<?php
/**
 * Settings page content
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent"><?php _e('settings.title'); ?></span>
    </h1>
    <p class="text-white/60"><?php _e('settings.subtitle'); ?></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Account Information -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('settings.account_info'); ?></h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-white/60 mb-2"><?php _e('settings.username'); ?></label>
                <input type="text" value="WenKing_User" class="app-input w-full" readonly>
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2"><?php _e('settings.email'); ?></label>
                <input type="email" value="user@example.com" class="app-input w-full" readonly>
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2"><?php _e('settings.subscription'); ?></label>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-accent-purple/20 text-accent-purple rounded-full text-sm font-medium"><?php _e('settings.pro_plan'); ?></span>    
                    <span class="text-sm text-white/40"><?php _e('settings.expires'); ?> 2026-08-25</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Preferences -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('settings.preferences'); ?></h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-white/60 mb-2"><?php _e('settings.language'); ?></label>
                <select class="app-select w-full">
                    <option value="en">English</option>
                    <option value="zh">Simplified Chinese</option>
                </select>
            </div>
            <div class="flex items-center justify-between py-3 border-t border-white/10">
                <div>
                    <p class="text-white font-medium"><?php _e('settings.email_notifications'); ?></p>
                    <p class="text-sm text-white/40"><?php _e('settings.email_notifications_desc'); ?></p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox" checked>
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="flex items-center justify-between py-3 border-t border-white/10">
                <div>
                    <p class="text-white font-medium"><?php _e('settings.auto_updates'); ?></p>
                    <p class="text-sm text-white/40"><?php _e('settings.auto_updates_desc'); ?></p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox" checked>
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('settings.security'); ?></h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium"><?php _e('settings.two_factor'); ?></p>
                    <p class="text-sm text-white/40"><?php _e('settings.two_factor_desc'); ?></p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    <?php _e('settings.change_password'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('settings.security'); ?></h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium"><?php _e('settings.two_factor'); ?></p>
                    <p class="text-sm text-white/40"><?php _e('settings.two_factor_desc'); ?></p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    <?php _e('settings.change_password'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('settings.security'); ?></h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium"><?php _e('settings.two_factor'); ?></p>
                    <p class="text-sm text-white/40"><?php _e('settings.two_factor_desc'); ?></p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    <?php _e('settings.change_password'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('settings.security'); ?></h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-white font-medium"><?php _e('settings.two_factor'); ?></p>
                    <p class="text-sm text-white/40"><?php _e('settings.two_factor_desc'); ?></p>
                </div>
                <label class="app-toggle">
                    <input type="checkbox">
                    <span class="app-toggle-slider"></span>
                </label>
            </div>
            <div class="pt-3 border-t border-white/10">
                <button class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg text-sm transition">
                    <?php _e('settings.change_password'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Save Button -->
<div class="flex justify-center mt-6">
    <button class="btn-primary px-6 py-3 rounded-lg font-semibold">
        <?php _e('settings.save_changes'); ?>
    </button>
</div>
