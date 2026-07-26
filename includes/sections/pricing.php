<?php
/**
 * 定价方案区块组件
 * 展示3个定价层级
 */
?>
<section id="pricing" class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    Choose Your Plan
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                Flexible pricing options to match your gaming needs
            </p>
        </div>

        <!-- Pricing Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            <!-- Basic Plan -->
            <div class="pricing-card p-8 rounded-xl fade-in-up">
                <h3 class="text-2xl font-display font-bold mb-2">Basic</h3>
                <p class="text-white/60 mb-6">Perfect for casual gamers</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold">$19.99</span>
                    <span class="text-white/60">/month</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-white/70">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Basic Features
                    </li>
                    <li class="flex items-center text-white/70">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Community Support
                    </li>
                    <li class="flex items-center text-white/70">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Weekly Updates
                    </li>
                </ul>
                <a href="<?php echo DISCORD_URL; ?>" target="_blank" class="btn-secondary block w-full py-3 rounded-lg font-semibold text-center">
                    Choose Basic
                </a>
            </div>

            <!-- Pro Plan - Featured -->
            <div class="pricing-card featured p-8 rounded-xl relative fade-in-up delay-200" style="background-color: #12121a;">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-gradient-to-r from-accent-purple to-accent-blue px-4 py-1 rounded-full text-sm font-semibold">
                        Most Popular
                    </span>
                </div>
                <h3 class="text-2xl font-display font-bold mb-2">Pro</h3>
                <p class="text-white/60 mb-6">For dedicated gamers</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold">$39.99</span>
                    <span class="text-white/60">/month</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-white" style="background-color: #12121a;">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        All Basic Features
                    </li>
                    <li class="flex items-center text-white">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Advanced Features
                    </li>
                    <li class="flex items-center text-white">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Priority Support
                    </li>
                    <li class="flex items-center text-white">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Daily Updates
                    </li>
                </ul>
                <a href="<?php echo DISCORD_URL; ?>" target="_blank" class="btn-primary block w-full py-3 rounded-lg font-semibold text-center">
                    Choose Pro
                </a>
            </div>

            <!-- Lifetime Plan -->
            <div class="pricing-card p-8 rounded-xl fade-in-up delay-400">
                <h3 class="text-2xl font-display font-bold mb-2">Lifetime</h3>
                <p class="text-white/60 mb-6">One-time purchase, forever access</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold">$199.99</span>
                    <span class="text-white/60">/lifetime</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-white/70">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        All Pro Features
                    </li>
                    <li class="flex items-center text-white/70">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Lifetime Updates
                    </li>
                    <li class="flex items-center text-white/70">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        VIP Support
                    </li>
                    <li class="flex items-center text-white/70">
                        <svg class="w-5 h-5 mr-2 text-status-online" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Exclusive Features
                    </li>
                </ul>
                <a href="<?php echo DISCORD_URL; ?>" target="_blank" class="btn-secondary block w-full py-3 rounded-lg font-semibold text-center">
                    Choose Lifetime
                </a>
            </div>
        </div>
    </div>
</section>
