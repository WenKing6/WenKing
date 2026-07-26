<?php
/**
 * Atlas Menu 首页
 * 组装所有区块组件
 */
require_once __DIR__ . '/config/config.php';

// 页面标题
define('PAGE_TITLE', META_TITLE);

// 包含头部模板
require_once __DIR__ . '/includes/header.php';

// 包含导航组件
require_once __DIR__ . '/includes/nav.php';
?>

<!-- GridScan 全局背景动画 -->
<div id="grid-scan-bg" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; pointer-events: none;"></div>

<main>
    <?php
    // 包含所有首页区块
    require_once __DIR__ . '/includes/sections/hero.php';
    require_once __DIR__ . '/includes/sections/stats.php';
    require_once __DIR__ . '/includes/sections/features.php';
    require_once __DIR__ . '/includes/sections/products.php';
    // require_once __DIR__ . '/includes/sections/pricing.php'; // 暂时隐藏
    require_once __DIR__ . '/includes/sections/faq.php';
    require_once __DIR__ . '/includes/sections/cta.php';
    ?>
</main>

<?php
// 包含底部模板
require_once __DIR__ . '/includes/footer.php';
?>
