<?php
/**
 * 产品展示区块组件
 * 展示3个产品卡片，带状态标签
 */
?>
<section id="products" class="py-20 bg-bg-secondary" style="background-color: rgba(18, 18, 26, 0);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    Our Products
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                Choose the perfect mod menu for your gaming needs
            </p>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Product 1 - Online -->
            <div class="relative group fade-in-up">
                <div class="product-card p-8 rounded-xl h-full">
                    <div class="card-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/hero-bg.jpg'); background-position: center top;"></div>
                    <span class="status-badge status-online mb-4 inline-block">ONLINE</span>
                    <h3 class="text-2xl font-display font-bold mb-2">GTA V Menu</h3>
                    <p class="text-white/60 mb-6 text-left">Complete lobby dominance with advanced features</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            100+ Features
                        </li>
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Daily Updates
                        </li>
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Undetected Status
                        </li>
                    </ul>
                    <a href="#pricing" class="btn-primary block w-full py-3 rounded-lg font-semibold text-center">
                        Now Buy
                    </a>
                </div>
            </div>

            <!-- Product 2 - Updating -->
            <div class="relative group fade-in-up delay-200">
                <div class="product-card p-8 rounded-xl h-full">
                    <div class="card-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/hero-bg.jpg'); background-position: center center;"></div>
                    <span class="status-badge status-updating mb-4 inline-block">UPDATING</span>
                    <h3 class="text-2xl font-display font-bold mb-2">RDR 2 Menu</h3>
                    <p class="text-white/60 mb-6">Wild West adventure with premium enhancements</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-updating" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            80+ Features
                        </li>
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-updating" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Weekly Updates
                        </li>
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-updating" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Security Protection
                        </li>
                    </ul>
                    <button class="btn-secondary block w-full py-3 rounded-lg font-semibold text-center text-white/50 cursor-not-allowed">
                        Coming Soon
                    </button>
                </div>
            </div>

            <!-- Product 3 - Development -->
            <div class="relative group fade-in-up delay-400">
                <div class="product-card p-8 rounded-xl h-full">
                    <div class="card-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/hero-bg.jpg'); background-position: center bottom;"></div>
                    <span class="status-badge status-dev mb-4 inline-block">DEVELOPMENT</span>
                    <h3 class="text-2xl font-display font-bold mb-2">Fortnite Menu</h3>
                    <p class="text-white/60 mb-6">Competitive edge with advanced aiming systems</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-dev" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Advanced Aimbot
                        </li>
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-dev" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Visual Enhancements
                        </li>
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-dev" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Movement Assistance
                        </li>
                    </ul>
                    <button class="btn-secondary block w-full py-3 rounded-lg font-semibold text-center text-white/50 cursor-not-allowed">
                        In Development
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
