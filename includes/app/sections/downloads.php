<?php
/**
 * 下载页面内容
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent"><?php _e('downloads.title'); ?></span>
    </h1>
    <p class="text-white/60"><?php _e('downloads.subtitle'); ?></p>
</div>

<!-- 产品下载列表 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- GTA V Menu -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-xl font-semibold text-white mb-1">GTA V Menu</h3>
                <p class="text-sm text-white/40"><?php _e('downloads.version'); ?> 3.2.1</p>
            </div>
            <span class="status-badge status-online"><?php _e('downloads.online'); ?></span>
        </div>
        <p class="text-white/60 text-sm mb-4"><?php _e('downloads.gta5_desc'); ?></p>
        <div class="flex items-center gap-4 text-xs text-white/40 mb-4">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                24.5 MB
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?php _e('downloads.updated_ago'); ?>
            </span>
        </div>
        <button class="btn-primary w-full py-3 rounded-lg font-semibold text-center">
            <?php _e('downloads.download_now'); ?>
        </button>
    </div>

    <!-- FiveM Menu -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-xl font-semibold text-white mb-1">FiveM Menu</h3>
                <p class="text-sm text-white/40"><?php _e('downloads.version'); ?> 2.1.0</p>
            </div>
            <span class="status-badge status-updating"><?php _e('downloads.updating'); ?></span>
        </div>
        <p class="text-white/60 text-sm mb-4"><?php _e('downloads.fivem_desc'); ?></p>
        <div class="flex items-center gap-4 text-xs text-white/40 mb-4">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                18.2 MB
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?php _e('downloads.updating_status'); ?>
            </span>
        </div>
        <button class="btn-secondary w-full py-3 rounded-lg font-semibold text-center" disabled>
            <?php _e('downloads.coming_soon'); ?>
        </button>
    </div>
</div>

<!-- 下载历史 -->
<div class="glass-card p-6 rounded-xl">
    <h3 class="text-lg font-semibold mb-4 text-white"><?php _e('downloads.history'); ?></h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="text-left py-3 px-4 text-white/60 font-medium"><?php _e('downloads.product'); ?></th>
                    <th class="text-left py-3 px-4 text-white/60 font-medium"><?php _e('downloads.version'); ?></th>
                    <th class="text-left py-3 px-4 text-white/60 font-medium"><?php _e('downloads.date'); ?></th>
                    <th class="text-left py-3 px-4 text-white/60 font-medium"><?php _e('downloads.size'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    <td class="py-3 px-4 text-white">GTA V Menu</td>
                    <td class="py-3 px-4 text-white/60">v3.2.1</td>
                    <td class="py-3 px-4 text-white/60">2026-07-26</td>
                    <td class="py-3 px-4 text-white/60">24.5 MB</td>
                </tr>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    <td class="py-3 px-4 text-white">GTA V Menu</td>
                    <td class="py-3 px-4 text-white/60">v3.2.0</td>
                    <td class="py-3 px-4 text-white/60">2026-07-24</td>
                    <td class="py-3 px-4 text-white/60">24.3 MB</td>
                </tr>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    <td class="py-3 px-4 text-white">GTA V Menu</td>
                    <td class="py-3 px-4 text-white/60">v3.1.9</td>
                    <td class="py-3 px-4 text-white/60">2026-07-22</td>
                    <td class="py-3 px-4 text-white/60">24.1 MB</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
