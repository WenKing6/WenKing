<?php
/**
 * 仪表盘页面内容
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">Dashboard</span>
    </h1>
    <p class="text-white/60">Welcome back! Here's your overview.</p>
</div>

<!-- 统计卡片 -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- 订阅状态 -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-accent-purple/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <span class="text-sm text-white/60">Subscription</span>
        </div>
        <div class="text-2xl font-bold text-white mb-1">Pro Plan</div>
        <span class="status-badge status-online text-xs">Active</span>
    </div>

    <!-- 到期日期 -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-accent-blue/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <span class="text-sm text-white/60">Expires</span>
        </div>
        <div class="text-2xl font-bold text-white mb-1">2026-08-25</div>
        <span class="text-xs text-status-online">30 days remaining</span>
    </div>

    <!-- 下载次数 -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-accent-cyan/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-accent-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </div>
            <span class="text-sm text-white/60">Downloads</span>
        </div>
        <div class="text-2xl font-bold text-white mb-1">12</div>
        <span class="text-xs text-white/40">This month</span>
    </div>

    <!-- 在线状态 -->
    <div class="glass-card p-6 rounded-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-status-online/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-status-online" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
                </svg>
            </div>
            <span class="text-sm text-white/60">Status</span>
        </div>
        <div class="text-2xl font-bold text-white mb-1">Online</div>
        <span class="status-badge status-online text-xs">Undetected</span>
    </div>
</div>

<!-- 已激活模组 -->
<div class="mb-8 p-6 rounded-xl" style="background-color: rgba(18, 18, 26, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1);">
    <h2 class="text-xl font-display font-bold mb-4 text-white">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">Activated Mods</span>
    </h2>
    <div class="space-y-4">
        <!-- GTA V -->
        <div class="glass-card p-4 rounded-xl" style="background-color: rgba(255, 255, 255, 0.05);">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-accent-purple/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-white">GTA V</h3>
                    <p class="text-xs text-white/40">Grand Theft Auto V</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="status-badge status-online text-xs">Active</span>
                    <span class="text-xs text-white/40">v3.2.1</span>
                </div>
            </div>
        </div>

        <!-- RDR2 -->
        <div class="glass-card p-4 rounded-xl" style="background-color: rgba(255, 255, 255, 0.05);">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-accent-blue/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-white">RDR2</h3>
                    <p class="text-xs text-white/40">Red Dead Redemption 2</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="status-badge status-online text-xs">Active</span>
                    <span class="text-xs text-white/40">v2.1.0</span>
                </div>
            </div>
        </div>

        <!-- GTA VI -->
        <div class="glass-card p-4 rounded-xl" style="background-color: rgba(255, 255, 255, 0.05);">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-accent-cyan/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-accent-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-white">GTA VI</h3>
                    <p class="text-xs text-white/40">Grand Theft Auto VI</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="status-badge status-online text-xs">Active</span>
                    <span class="text-xs text-white/40">v1.0.0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 最近活动 & 快速操作 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 最近活动 -->
    <div class="glass-card p-6 rounded-xl lg:col-span-2">
        <h3 class="text-lg font-semibold mb-4 text-white">Recent Activity</h3>
        <div class="space-y-4">
            <div class="flex items-center gap-4 p-3 bg-white/5 rounded-lg">
                <div class="w-8 h-8 rounded-full bg-accent-purple/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white font-medium">Downloaded GTA V Menu v3.2.1</p>
                    <p class="text-xs text-white/40">2 hours ago</p>
                </div>
            </div>
            <div class="flex items-center gap-4 p-3 bg-white/5 rounded-lg">
                <div class="w-8 h-8 rounded-full bg-accent-blue/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white font-medium">Updated settings</p>
                    <p class="text-xs text-white/40">Yesterday</p>
                </div>
            </div>
            <div class="flex items-center gap-4 p-3 bg-white/5 rounded-lg">
                <div class="w-8 h-8 rounded-full bg-status-online/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-status-online" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white font-medium">Subscription renewed</p>
                    <p class="text-xs text-white/40">3 days ago</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 快速操作 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold mb-4 text-white">Quick Actions</h3>
        <div class="space-y-3">
            <a href="#/downloads" class="block w-full py-3 px-4 bg-accent-purple/20 hover:bg-accent-purple/30 text-accent-purple rounded-lg font-medium text-center transition">
                Download Latest
            </a>
            <a href="#/settings" class="block w-full py-3 px-4 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg font-medium text-center transition">
                Manage Settings
            </a>
            <a href="<?php echo DISCORD_URL; ?>" target="_blank" class="block w-full py-3 px-4 bg-white/5 hover:bg-white/10 text-white/80 rounded-lg font-medium text-center transition">
                Join Discord
            </a>
        </div>
    </div>
</div>
