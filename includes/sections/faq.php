<?php
/**
 * FAQ 区块组件
 */
?>
<section id="faq" class="py-20 bg-bg-secondary" style="background-color: rgba(18, 18, 26, 0);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    <?php _e('faq.title'); ?>
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                <?php _e('faq.subtitle'); ?>
            </p>
        </div>

        <!-- FAQ Items -->
        <div class="space-y-4">
            <!-- FAQ Item 1 -->
            <div class="faq-item rounded-xl overflow-hidden fade-in-up">
                <button class="faq-question w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-lg"><?php _e('faq.q1'); ?></span>
                    <svg class="faq-icon w-5 h-5 text-white/60 flex-shrink-0 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                    <p class="px-6 py-4 text-white/70"><?php _e('faq.a1'); ?></p>
                </div>
            </div>

            <!-- FAQ Item 2 -->
            <div class="faq-item rounded-xl overflow-hidden fade-in-up delay-100">
                <button class="faq-question w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-lg"><?php _e('faq.q2'); ?></span>
                    <svg class="faq-icon w-5 h-5 text-white/60 flex-shrink-0 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                    <p class="px-6 py-4 text-white/70"><?php _e('faq.a2'); ?></p>
                </div>
            </div>

            <!-- FAQ Item 3 -->
            <div class="faq-item rounded-xl overflow-hidden fade-in-up delay-200">
                <button class="faq-question w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-lg"><?php _e('faq.q3'); ?></span>
                    <svg class="faq-icon w-5 h-5 text-white/60 flex-shrink-0 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                    <p class="px-6 py-4 text-white/70"><?php _e('faq.a3'); ?></p>
                </div>
            </div>

            <!-- FAQ Item 4 -->
            <div class="faq-item rounded-xl overflow-hidden fade-in-up delay-300">
                <button class="faq-question w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-lg"><?php _e('faq.q4'); ?></span>
                    <svg class="faq-icon w-5 h-5 text-white/60 flex-shrink-0 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                    <p class="px-6 py-4 text-white/70"><?php _e('faq.a4'); ?></p>
                </div>
            </div>

            <!-- FAQ Item 5 -->
            <div class="faq-item rounded-xl overflow-hidden fade-in-up delay-400">
                <button class="faq-question w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-lg"><?php _e('faq.q5'); ?></span>
                    <svg class="faq-icon w-5 h-5 text-white/60 flex-shrink-0 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                    <p class="px-6 py-4 text-white/70"><?php _e('faq.a5'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
