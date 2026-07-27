<?php
/**
 * 功能特性区块组件
 * 展示6个核心功能卡片
 */
?>
<section id="features" class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    <?php _e('features.title'); ?>
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                <?php _e('features.subtitle'); ?>
            </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-10 h-10 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold mb-0"><?php _e('features.security'); ?></h3>
                </div>
                <p class="text-white/60"><?php _e('features.security_desc'); ?></p>
            </div>

            <!-- Feature 2 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up delay-100">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-10 h-10 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold mb-0"><?php _e('features.feature2'); ?></h3>
                </div>
                <p class="text-white/60"><?php _e('features.feature2_desc'); ?></p>
            </div>

            <!-- Feature 3 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up delay-200">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-10 h-10 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold mb-0"><?php _e('features.feature3'); ?></h3>
                </div>
                <p class="text-white/60"><?php _e('features.feature3_desc'); ?></p>
            </div>

            <!-- Feature 4 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-10 h-10 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="text-xl font-semibold mb-0"><?php _e('features.feature4'); ?></h3>
                </div>
                <p class="text-white/60"><?php _e('features.feature4_desc'); ?></p>
            </div>

            <!-- Feature 5 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up delay-100">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-10 h-10 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <h3 class="text-xl font-semibold mb-0"><?php _e('features.feature5'); ?></h3>
                </div>
                <p class="text-white/60"><?php _e('features.feature5_desc'); ?></p>
            </div>

            <!-- Feature 6 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up delay-200">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-10 h-10 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold mb-0"><?php _e('features.feature6'); ?></h3>
                </div>
                <p class="text-white/60"><?php _e('features.feature6_desc'); ?></p>
            </div>
        </div>
    </div>
</section>
