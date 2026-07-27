<?php
/**
 * 导航栏组件
 * 固定顶部，毛玻璃效果，响应式设计
 */
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="fixed top-0 left-0 right-0 z-50 bg-bg-primary/80 backdrop-blur-md border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?php echo SITE_URL; ?>/index.php" class="hover:opacity-80 transition">
                    <img src="<?php echo SITE_URL; ?>/assets/images/gta-v-logo-transparent-free-png.webp" alt="WenKing" class="h-16 w-auto">
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:block">
                <div class="flex items-center space-x-8">
                    <a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo $current_page === 'index' ? 'text-white' : 'text-white/70'; ?> hover:text-accent-purple transition font-medium">
                        Home
                    </a>
                    <a href="<?php echo SITE_URL; ?>/index.php#features" class="text-white/70 hover:text-accent-purple transition font-medium">
                        Features
                    </a>
                    <a href="<?php echo SITE_URL; ?>/index.php#pricing" class="text-white/70 hover:text-accent-purple transition font-medium">
                        Pricing
                    </a>
                    <a href="<?php echo SITE_URL; ?>/index.php#faq" class="text-white/70 hover:text-accent-purple transition font-medium">
                        FAQ
                    </a>
                    <!-- 暂时隐藏 About Us 按钮 -->
                    <!-- <a href="<?php echo SITE_URL; ?>/about.php" class="<?php echo $current_page === 'about' ? 'text-white' : 'text-white/70'; ?> hover:text-accent-purple transition font-medium">
                        About Us
                    </a> -->

                    <!-- Language Switcher -->
                    <div class="relative group">
                        <button class="flex items-center gap-1 text-white/70 hover:text-accent-purple transition font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>EN</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-32 bg-bg-secondary border border-white/10 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="#" class="block px-4 py-2 text-white/70 hover:text-accent-purple hover:bg-white/5 transition text-sm">
                                🇺🇸 English
                            </a>
                            <a href="#" class="block px-4 py-2 text-white/70 hover:text-accent-purple hover:bg-white/5 transition text-sm">
                                🇨🇳 简体中文
                            </a>
                        </div>
                    </div>

                    <a href="<?php echo SITE_URL; ?>/auth.php" class="text-white/70 hover:text-accent-purple transition font-medium">
                        Login
                    </a>
                    <a href="<?php echo SITE_URL; ?>/app.php" class="btn-neon" style="color: rgba(255, 255, 255, 0.7);">
                        Dashboard
                    </a>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button id="mobile-menu-btn" class="text-white hover:text-accent-purple transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu md:hidden">
        <div class="mobile-menu-overlay"></div>
        <div class="mobile-menu-content bg-bg-secondary border-t border-white/10">
            <div class="px-4 py-4 space-y-3">
                <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-menu-item block text-white hover:text-accent-purple transition font-medium">
                    Home
                </a>
                <a href="<?php echo SITE_URL; ?>/index.php#features" class="mobile-menu-item block text-white/70 hover:text-accent-purple transition font-medium">
                    Features
                </a>
                <a href="<?php echo SITE_URL; ?>/index.php#pricing" class="mobile-menu-item block text-white/70 hover:text-accent-purple transition font-medium">
                    Pricing
                </a>
                <a href="<?php echo SITE_URL; ?>/index.php#faq" class="mobile-menu-item block text-white/70 hover:text-accent-purple transition font-medium">
                    FAQ
                </a>
                <a href="<?php echo SITE_URL; ?>/about.php" class="mobile-menu-item block text-white/70 hover:text-accent-purple transition font-medium">
                    About Us
                </a>
                <a href="<?php echo SITE_URL; ?>/auth.php" class="mobile-menu-item block text-white/70 hover:text-accent-purple transition font-medium">
                    Login
                </a>
                <a href="<?php echo SITE_URL; ?>/app.php" class="mobile-menu-item btn-neon block text-center">
                    Dashboard
                </a>
            </div>
        </div>
    </div>
</nav>
