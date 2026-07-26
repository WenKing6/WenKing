<?php
/**
 * AJAX 路由分发器
 * 处理页面请求并返回 HTML 片段
 */

// 引入配置
require_once __DIR__ . '/../config/config.php';

// 白名单验证
$allowed_pages = ['dashboard', 'settings', 'downloads', 'redeem'];
$page = $_GET['page'] ?? 'dashboard';

// 防止目录遍历
$page = basename($page);

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// 设置响应头
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// 引入对应页面内容
$section_file = __DIR__ . '/../includes/app/sections/' . $page . '.php';

if (file_exists($section_file)) {
    require_once $section_file;
} else {
    http_response_code(404);
    echo '<div class="text-center py-12"><p class="text-white/60">Page not found.</p></div>';
}
