<?php
/**
 * 兑换码页面内容
 */
?>
<div class="app-page-header mb-8">
    <h1 class="text-3xl font-display font-bold mb-2">
        <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent"><?php _e('redeem.title'); ?></span>
    </h1>
    <p class="text-white/60"><?php _e('redeem.subtitle'); ?></p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
    <!-- 兑换表单 -->
    <div class="glass-card p-8 rounded-xl">
        <div class="space-y-6">
            <!-- 输入框 -->
            <div>
                <label class="block text-sm text-white/60 mb-2"><?php _e('redeem.code_label'); ?></label>
                <input type="text" placeholder="<?php _e('redeem.code_ph'); ?>" class="app-input w-full text-center text-lg tracking-widest uppercase" id="redeem-code-input">
            </div>

            <!-- 按钮 -->
            <button class="btn-primary w-full py-3 rounded-lg font-semibold text-center" id="redeem-btn">
                <?php _e('redeem.redeem_btn'); ?>
            </button>
        </div>
    </div>

    <!-- 使用说明 -->
    <div class="glass-card p-6 rounded-xl">
        <h3 class="text-sm font-semibold text-white/80 mb-3"><?php _e('redeem.how_to'); ?></h3>
        <ol class="space-y-2 text-sm text-white/50 list-decimal list-inside">
            <li><?php _e('redeem.step1'); ?></li>
            <li><?php _e('redeem.step2'); ?></li>
            <li><?php _e('redeem.step3'); ?></li>
            <li><?php _e('redeem.step4'); ?></li>
        </ol>
    </div>
</div>

<!-- 浮动提示框 -->
<div id="redeem-toast" class="redeem-toast">
    <div class="redeem-toast-progress"></div>
    <div class="redeem-toast-content">
        <div class="redeem-toast-icon" id="redeem-toast-icon">
            <!-- 图标将通过 JS 动态设置 -->
        </div>
        <div class="redeem-toast-body">
            <div class="redeem-toast-title" id="redeem-toast-title"><?php _e('redeem.success'); ?></div>
            <div class="redeem-toast-message" id="redeem-toast-message"><?php _e('redeem.subtitle'); ?></div>
        </div>
        <button class="redeem-toast-close" id="redeem-toast-close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<style>
/* 提示框容器 */
.redeem-toast {
    position: fixed;
    top: 2rem;
    right: 2rem;
    width: 400px;
    max-width: calc(100vw - 4rem);
    background: rgba(18, 18, 26, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 0.75rem;
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.2), 0 0 40px rgba(139, 92, 246, 0.1);
    opacity: 0;
    transform: translateX(120%);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 9999;
    overflow: hidden;
    pointer-events: none;
}

.redeem-toast.show {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
}

/* 绿色 - 成功提示 */
.redeem-toast.toast-success {
    border-color: rgba(16, 185, 129, 0.3);
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.2), 0 0 40px rgba(16, 185, 129, 0.1);
}

.redeem-toast.toast-success .redeem-toast-progress {
    background: linear-gradient(90deg, #10b981, #34d399, #6ee7b7);
}

.redeem-toast.toast-success .redeem-toast-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(52, 211, 153, 0.2));
    color: #10b981;
}

/* 橙色 - 警告提示 */
.redeem-toast.toast-warning {
    border-color: rgba(245, 158, 11, 0.3);
    box-shadow: 0 20px 60px rgba(245, 158, 11, 0.2), 0 0 40px rgba(245, 158, 11, 0.1);
}

.redeem-toast.toast-warning .redeem-toast-progress {
    background: linear-gradient(90deg, #f59e0b, #fbbf24, #fcd34d);
}

.redeem-toast.toast-warning .redeem-toast-icon {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(251, 191, 36, 0.2));
    color: #f59e0b;
}

/* 红色 - 错误提示 */
.redeem-toast.toast-error {
    border-color: rgba(239, 68, 68, 0.3);
    box-shadow: 0 20px 60px rgba(239, 68, 68, 0.2), 0 0 40px rgba(239, 68, 68, 0.1);
}

.redeem-toast.toast-error .redeem-toast-progress {
    background: linear-gradient(90deg, #ef4444, #f87171, #fca5a5);
}

.redeem-toast.toast-error .redeem-toast-icon {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(248, 113, 113, 0.2));
    color: #ef4444;
}

/* 进度条 */
.redeem-toast-progress {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #8b5cf6, #3b82f6, #06b6d4);
    transform-origin: left;
    transform: scaleX(1);
}

.redeem-toast.show .redeem-toast-progress {
    animation: progressShrink var(--toast-duration, 1000ms) linear forwards;
}

@keyframes progressShrink {
    from {
        transform: scaleX(1);
    }
    to {
        transform: scaleX(0);
    }
}

/* 内容区域 */
.redeem-toast-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
}

/* 图标 */
.redeem-toast-icon {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(59, 130, 246, 0.2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
}

/* 文本内容 */
.redeem-toast-body {
    flex: 1;
    min-width: 0;
}

.redeem-toast-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 0.25rem;
}

.redeem-toast-message {
    font-size: 0.8125rem;
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.4;
}

/* 关闭按钮 */
.redeem-toast-close {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.redeem-toast-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
    transform: scale(1.05);
}

/* 响应式 */
@media (max-width: 640px) {
    .redeem-toast {
        top: 1rem;
        right: 1rem;
        left: 1rem;
        width: auto;
        max-width: none;
    }
}
</style>


