<?php
/**
 * 页面底部模板
 * 包含页脚链接、社交媒体和版权信息
 */
?>
    <footer class="bg-bg-secondary border-t border-white/10 mt-20" style="background-color: rgba(18, 18, 26, 1);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1">
                    <h3 class="text-2xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent mb-4">
                        WenKing
                    </h3>
                    <p class="text-white/60 text-sm mb-4">
                        <?php _e('footer.description'); ?>
                    </p>
                    <div class="flex space-x-4">
                        <a href="<?php echo DISCORD_URL; ?>" target="_blank" class="text-white/60 hover:text-accent-purple transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                        </a>
                        <a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="text-white/60 hover:text-accent-purple transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Products -->
                <div>
                    <h4 class="text-white font-semibold mb-4"><?php _e('footer.products'); ?></h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo SITE_URL; ?>/index.php#features" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.features'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/index.php#pricing" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.pricing'); ?></a></li>
                        <li><a href="#" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.changelog'); ?></a></li>
                        <li><a href="#" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.documentation'); ?></a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="text-white font-semibold mb-4"><?php _e('footer.support'); ?></h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo SITE_URL; ?>/index.php#faq" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.faq'); ?></a></li>
                        <li><a href="#" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.tutorials'); ?></a></li>
                        <li><a href="#" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.contact'); ?></a></li>
                        <li><a href="<?php echo DISCORD_URL; ?>" target="_blank" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.discord_support'); ?></a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="text-white font-semibold mb-4"><?php _e('footer.company'); ?></h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo SITE_URL; ?>/about.php" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.about'); ?></a></li>
                        <li><a href="#" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.privacy'); ?></a></li>
                        <li><a href="#" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.terms'); ?></a></li>
                        <li><a href="<?php echo DISCORD_URL; ?>" target="_blank" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.discord'); ?></a></li>
                        <li><a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="text-white/60 hover:text-accent-purple transition"><?php _e('footer.telegram'); ?></a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-white/10 mt-8 pt-8 text-center text-white/40 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php _e('footer.copyright'); ?></p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/animations.js"></script>
    
    <!-- GridScan Background Animation -->
    <script src="<?php echo SITE_URL; ?>/assets/js/grid-scan.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('grid-scan-bg');
        if (container && typeof GridScan !== 'undefined') {
            // 随机深色（网格线）
            function randomDarkColor() {
                const r = Math.floor(Math.random() * 60 + 20);
                const g = Math.floor(Math.random() * 60 + 20);
                const b = Math.floor(Math.random() * 80 + 30);
                return `rgb(${r}, ${g}, ${b})`;
            }

            // 随机亮色（扫描光）
            function randomBrightColor() {
                const palettes = [
                    ['#8b5cf6', '#a78bfa'], // 紫色系
                    ['#3b82f6', '#60a5fa'], // 蓝色系
                    ['#06b6d4', '#22d3ee'], // 青色系
                    ['#ec4899', '#f472b6'], // 粉色系
                    ['#10b981', '#34d399'], // 绿色系
                    ['#f59e0b', '#fbbf24'], // 橙色系
                    ['#ef4444', '#f87171'], // 红色系
                ];
                const palette = palettes[Math.floor(Math.random() * palettes.length)];
                return palette[Math.floor(Math.random() * palette.length)];
            }

            const gridScan = new GridScan(container, {
                lineThickness: 1,
                linesColor: randomDarkColor(),
                scanColor: randomBrightColor(),
                scanOpacity: 0.4,
                gridScale: 0.1,
                lineStyle: 'solid',
                lineJitter: 0.1,
                scanDirection: 'pingpong',
                noiseIntensity: 0.01,
                scanGlow: 0.5,
                scanSoftness: 2,
                scanPhaseTaper: 0.9,
                scanDuration: 2.0,
                scanDelay: 2.0,
                sensitivity: 0.55
            });

            // 每 4 秒切换一次颜色
            setInterval(function() {
                gridScan.setColors(randomDarkColor(), randomBrightColor());
            }, 4000);
        }
    });
    </script>
</body>
</html>
