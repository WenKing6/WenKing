<?php
/**
 * 应用页面侧边栏导航组件
 * 响应式设计：桌面端展开，移动端抽屉式
 */
$current_page = $current_page ?? 'dashboard';
?>

<!-- 移动端汉堡按钮 -->
<button id="sidebar-toggle" class="sidebar-toggle lg:hidden fixed top-4 left-4 z-50 p-2 bg-bg-secondary/80 backdrop-blur-md border border-white/10 rounded-lg hover:bg-white/5 transition">
    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<!-- 移动端遮罩层 -->
<div id="sidebar-overlay" class="sidebar-overlay fixed inset-0 bg-black/50 z-40 lg:hidden opacity-0 invisible transition-opacity duration-300"></div>

<!-- 侧边栏 -->
<aside id="app-sidebar" class="app-sidebar fixed left-0 top-0 h-full w-64 bg-bg-secondary/80 backdrop-blur-md border-r border-white/10 z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <!-- Logo 区域 -->
    <div class="p-6 border-b border-white/10">
        <a href="<?php echo SITE_URL; ?>/index.php" class="flex items-center gap-3 hover:opacity-80 transition">
            <img src="<?php echo SITE_URL; ?>/assets/images/gta-v-logo-transparent-free-png.webp" alt="WenKing" class="h-10 w-auto">
            <span class="text-xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                WenKing
            </span>
        </a>
    </div>

    <!-- 导航链接 -->
    <nav class="p-4 space-y-2">
        <a href="#/dashboard" data-page="dashboard" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition <?php echo $current_page === 'dashboard' ? 'active text-accent-purple bg-accent-purple/10 border-l-2 border-accent-purple' : ''; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="#/redeem" data-page="redeem" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition <?php echo $current_page === 'redeem' ? 'active text-accent-purple bg-accent-purple/10 border-l-2 border-accent-purple' : ''; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
            <span>Redeem</span>
        </a>

        <a href="#/downloads" data-page="downloads" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition <?php echo $current_page === 'downloads' ? 'active text-accent-purple bg-accent-purple/10 border-l-2 border-accent-purple' : ''; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            <span>Downloads</span>
        </a>

        <a href="#/reseller" data-page="reseller" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition <?php echo $current_page === 'reseller' ? 'active text-accent-purple bg-accent-purple/10 border-l-2 border-accent-purple' : ''; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
            </svg>
            <span>Reseller</span>
        </a>

        <a href="#/manager" data-page="manager" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition <?php echo $current_page === 'manager' ? 'active text-accent-purple bg-accent-purple/10 border-l-2 border-accent-purple' : ''; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span>Manager</span>
        </a>

        <a href="#/settings" data-page="settings" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition <?php echo $current_page === 'settings' ? 'active text-accent-purple bg-accent-purple/10 border-l-2 border-accent-purple' : ''; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Settings</span>
        </a>
    </nav>

    <!-- 底部区域 -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
        <a href="<?php echo SITE_URL; ?>/index.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-white/5 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Back to Home</span>
        </a>
    </div>
</aside>
