<?php
/**
 * Partners / Authorized Resellers Page
 */
require_once __DIR__ . '/config/config.php';

define('PAGE_TITLE', 'Partners - ' . SITE_NAME);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<!-- GridScan 全局背景动画 -->
<div id="grid-scan-bg" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; pointer-events: none;"></div>

<main>
    <!-- Page Header -->
    <section class="relative pt-32 pb-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-transparent"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-h1 font-display font-bold mb-6 fade-in-up">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    Authorized Resellers
                </span>
            </h1>
            <p class="text-xl text-white/70 max-w-3xl mx-auto fade-in-up delay-100">
                Browse our trusted network of official partners and resellers.
            </p>
        </div>
    </section>

    <!-- Partners Grid -->
    <?php require_once __DIR__ . '/includes/sections/partners-grid.php'; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
