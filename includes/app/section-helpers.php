<?php
/**
 * App sections 公共渲染函数
 * 由各 section（admin.php / manager.php）require_once 后调用
 */

/**
 * 渲染统一风格的分页控件（Prev / Page x of y / Next）
 * JS 侧按 id 约定读取：{prefix}-prev-page / {prefix}-current-page / {prefix}-total-pages / {prefix}-next-page
 * 对应 JS 方法：_bindPagination(prefix, opts)
 */
function renderPagination(string $prefix): void {
    $btnClass = 'px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white/60 text-sm transition disabled:opacity-30 disabled:cursor-not-allowed flex-shrink-0';
    ?>
    <div class="flex items-center gap-2 flex-shrink-0">
        <button type="button" id="<?php echo $prefix; ?>-prev-page" class="<?php echo $btnClass; ?>" disabled aria-label="Previous page">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <span class="text-sm text-white/60 whitespace-nowrap flex-shrink-0 min-w-fit">Page <span id="<?php echo $prefix; ?>-current-page">1</span> of <span id="<?php echo $prefix; ?>-total-pages">1</span></span>
        <button type="button" id="<?php echo $prefix; ?>-next-page" class="<?php echo $btnClass; ?>" disabled aria-label="Next page">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
    <?php
}
