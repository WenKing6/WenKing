<?php
/**
 * Hero区块组件
 * 大标题 + CTA按钮 + 背景光效
 */
?>
<section id="home" class="relative min-h-screen flex items-center justify-center pt-16 overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-transparent"></div>
    <div class="glow-orb glow-orb-1"></div>
    <div class="glow-orb glow-orb-2"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="fade-in-up">
            <h1 class="text-hero font-display font-bold mb-6">
                <span class="bg-gradient-to-r from-accent-purple via-accent-blue to-accent-cyan bg-clip-text text-transparent">
                    WenKing Mod Menu
                </span>
                <br>
                <span class="text-white">MOD MENU</span>
            </h1>

            <p class="text-xl text-white/70 mb-8 max-w-2xl mx-auto">
                Experience the most advanced game mod menu with undetected features, lightning-fast performance, and 24/7 dedicated support.
            </p>

            <div class="flex flex-row gap-4 justify-center">
                <a href="#pricing" class="button type1 flex items-center justify-center" style="width: 175px; height: 62px; padding: 0; color: #ffffff; backdrop-filter: none;">
                    <span class="btn-txt">Dashboard</span>
                </a>
                <a href="<?php echo DISCORD_URL; ?>" target="_blank" class="button type1 flex items-center justify-center" style="width: 175px; height: 62px; padding: 0; color: #ffffff; backdrop-filter: none;">
                    <span class="btn-txt">Discord</span>
                </a>
            </div>
        </div>
    </div>
</section>
