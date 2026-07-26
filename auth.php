<?php
/**
 * WenKing 用户认证页面
 * 包含登录和注册功能
 */
require_once __DIR__ . '/config/config.php';

define('PAGE_TITLE', SITE_NAME . ' - Login / Register');

// 包含头部模板（<head> 和 <body> 开始标签）
require_once __DIR__ . '/includes/header.php';

// 注入认证页面专属 CSS（在 header.php 输出后添加）
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/auth.css">

<!-- GridScan 全局背景动画 -->
<div id="grid-scan-bg" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; pointer-events: none;"></div>

<!-- 顶部导航（简化版） -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-bg-primary/80 backdrop-blur-md border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex-shrink-0">
                <a href="<?php echo SITE_URL; ?>/index.php" class="hover:opacity-80 transition">
                    <img src="<?php echo SITE_URL; ?>/assets/images/gta-v-logo-transparent-free-png.webp" alt="WenKing" class="h-16 w-auto">
                </a>
            </div>
            <a href="<?php echo SITE_URL; ?>/index.php" class="text-white/70 hover:text-accent-purple transition font-medium text-sm">
                ← Back to Home
            </a>
        </div>
    </div>
</nav>

<main class="min-h-screen flex items-center justify-center pt-16">
    <?php require_once __DIR__ . '/includes/sections/auth-section.php'; ?>
</main>

<!-- 认证页面专属 JS -->
<script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>