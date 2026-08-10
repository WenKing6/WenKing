<?php
/**
 * WenKing 应用入口
 * 仪表盘、设置、下载页面的统一入口
 * 采用 PHP SSR + AJAX 无刷新切换
 */
require_once __DIR__ . '/config/config.php';

// PHP 端 SSR 路由（避免首屏闪烁）
$allowed_pages = ['dashboard', 'settings', 'downloads', 'redeem', 'reseller', 'manager', 'admin'];
$current_page = $_GET['page'] ?? 'dashboard';
$current_page = basename($current_page); // 防止目录遍历

if (!in_array($current_page, $allowed_pages)) {
    $current_page = 'dashboard';
}

// 页面标题映射
$page_titles = [
    'dashboard' => SITE_NAME . ' - Dashboard',
    'settings'  => SITE_NAME . ' - Settings',
    'downloads' => SITE_NAME . ' - Downloads',
    'redeem'    => SITE_NAME . ' - Redeem Code',
    'reseller'  => SITE_NAME . ' - Reseller',
    'manager'   => SITE_NAME . ' - Manager',
    'admin'     => SITE_NAME . ' - Admin',
];

define('PAGE_TITLE', $page_titles[$current_page]);

// 包含头部模板
require_once __DIR__ . '/includes/app/app-header.php';

// 包含侧边栏
require_once __DIR__ . '/includes/app/app-sidebar.php';
?>

<!-- 主内容区域 -->
<main>
    <div id="app-content" class="app-content">
        <?php
        // PHP 端直接渲染默认页面内容（首屏无闪烁）
        $section_file = __DIR__ . '/includes/app/sections/' . $current_page . '.php';
        if (file_exists($section_file)) {
            require_once $section_file;
        }
        ?>
    </div>
</main>

<?php
// 包含底部模板（GridScan 初始化 + app.js）
require_once __DIR__ . '/includes/app/app-footer.php';
?>
