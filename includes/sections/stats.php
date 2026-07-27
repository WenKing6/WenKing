<?php
/**
 * 统计计数器区块组件
 * 展示4个关键数据指标
 */
?>
<section class="py-20 bg-bg-secondary" style="background-color: rgba(18, 18, 26, 0);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Stat 1 -->
            <div class="text-center fade-in-up">
                <div class="text-5xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent counter" data-target="10000">
                    0
                </div>
                <div class="text-white/60 mt-2 text-sm uppercase tracking-wide"><?php _e('stats.active_users'); ?></div>
            </div>

            <!-- Stat 2 -->
            <div class="text-center fade-in-up delay-100">
                <div class="text-5xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent counter" data-target="500">
                    0
                </div>
                <div class="text-white/60 mt-2 text-sm uppercase tracking-wide"><?php _e('stats.user_online'); ?></div>
            </div>

            <!-- Stat 3 -->
            <div class="text-center fade-in-up delay-200">
                <div class="text-5xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent counter" data-target="99">
                    0
                </div>
                <div class="text-white/60 mt-2 text-sm uppercase tracking-wide"><?php _e('stats.reseller'); ?></div>
            </div>

            <!-- Stat 4 -->
            <div class="text-center fade-in-up delay-300">
                <div class="text-5xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent counter" data-target="24">
                    0
                </div>
                <div class="text-white/60 mt-2 text-sm uppercase tracking-wide"><?php _e('stats.hours_support'); ?></div>
            </div>
        </div>
    </div>
</section>
