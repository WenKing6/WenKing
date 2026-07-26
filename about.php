<?php
/**
 * About Us Page
 */
require_once __DIR__ . '/config/config.php';
define('PAGE_TITLE', 'About Us - ' . SITE_NAME);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<!-- Page Header -->
<section class="relative pt-32 pb-16 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-accent-purple/10 via-bg-primary to-accent-blue/10"></div>
    <div class="glow-orb glow-orb-1"></div>
    <div class="glow-orb glow-orb-2"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-h1 font-display font-bold mb-6 fade-in-up">
            <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                About Atlas Menu
            </span>
        </h1>
        <p class="text-xl text-white/70 max-w-3xl mx-auto fade-in-up delay-100">
            We're a team of passionate developers dedicated to creating the most advanced and secure game mod menu solutions.
        </p>
    </div>
</section>

<!-- Mission Section -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="fade-in-up">
                <h2 class="text-h2 font-display font-bold mb-6">
                    <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                        Our Mission
                    </span>
                </h2>
                <p class="text-white/70 text-lg mb-6">
                    At Atlas Menu, we believe in pushing the boundaries of what's possible in gaming. Our mission is to provide players with cutting-edge tools that enhance their gaming experience while maintaining the highest standards of security and performance.
                </p>
                <p class="text-white/70 text-lg mb-6">
                    We're not just developers—we're gamers ourselves. We understand the desire for customization, control, and competitive advantage. That's why we've built a platform that combines innovation with reliability.
                </p>
                <div class="flex flex-wrap gap-4 mt-8">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-accent-purple rounded-full"></div>
                        <span class="text-white/80">Innovation First</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-accent-blue rounded-full"></div>
                        <span class="text-white/80">Security Always</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-accent-cyan rounded-full"></div>
                        <span class="text-white/80">Community Driven</span>
                    </div>
                </div>
            </div>
            <div class="relative fade-in-up delay-200">
                <div class="glass-card p-8 rounded-2xl">
                    <div class="space-y-6">
                        <div>
                            <div class="text-4xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent mb-2">2023</div>
                            <p class="text-white/60">Founded</p>
                        </div>
                        <div>
                            <div class="text-4xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent mb-2">10,000+</div>
                            <p class="text-white/60">Active Users</p>
                        </div>
                        <div>
                            <div class="text-4xl font-display font-bold bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent mb-2">99%</div>
                            <p class="text-white/60">Undetected Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-bg-secondary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    Our Core Values
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                The principles that guide everything we do
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Value 1 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up">
                <div class="w-16 h-16 bg-gradient-to-br from-accent-purple to-accent-blue rounded-xl flex items-center justify-center mb-6 mx-auto">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-display font-bold mb-4 text-center">Security First</h3>
                <p class="text-white/70 text-center">
                    We prioritize user safety above all else. Our advanced anti-detection technology and continuous security updates ensure you can game with confidence.
                </p>
            </div>

            <!-- Value 2 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up delay-100">
                <div class="w-16 h-16 bg-gradient-to-br from-accent-purple to-accent-blue rounded-xl flex items-center justify-center mb-6 mx-auto">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-display font-bold mb-4 text-center">Performance Excellence</h3>
                <p class="text-white/70 text-center">
                    Speed matters. We optimize every line of code to deliver lightning-fast performance without compromising on features or stability.
                </p>
            </div>

            <!-- Value 3 -->
            <div class="glass-card p-8 rounded-xl hover-lift fade-in-up delay-200">
                <div class="w-16 h-16 bg-gradient-to-br from-accent-purple to-accent-blue rounded-xl flex items-center justify-center mb-6 mx-auto">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-display font-bold mb-4 text-center">Community Focus</h3>
                <p class="text-white/70 text-center">
                    Our users are our family. We listen to feedback, engage with our community, and build features that matter to real gamers.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    Meet Our Team
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                The talented developers behind Atlas Menu
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Team Member 1 -->
            <div class="glass-card p-8 rounded-xl text-center hover-lift fade-in-up">
                <div class="w-24 h-24 bg-gradient-to-br from-accent-purple to-accent-blue rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-3xl font-display font-bold">AK</span>
                </div>
                <h3 class="text-xl font-display font-bold mb-2">Alex Kim</h3>
                <p class="text-accent-purple text-sm mb-4">Lead Developer</p>
                <p class="text-white/70 text-sm">
                    Full-stack developer with 8+ years of experience in game modification and security systems.
                </p>
            </div>

            <!-- Team Member 2 -->
            <div class="glass-card p-8 rounded-xl text-center hover-lift fade-in-up delay-100">
                <div class="w-24 h-24 bg-gradient-to-br from-accent-purple to-accent-blue rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-3xl font-display font-bold">SR</span>
                </div>
                <h3 class="text-xl font-display font-bold mb-2">Sarah Rodriguez</h3>
                <p class="text-accent-blue text-sm mb-4">Security Engineer</p>
                <p class="text-white/70 text-sm">
                    Anti-detection specialist focused on keeping our users safe from the latest detection methods.
                </p>
            </div>

            <!-- Team Member 3 -->
            <div class="glass-card p-8 rounded-xl text-center hover-lift fade-in-up delay-200">
                <div class="w-24 h-24 bg-gradient-to-br from-accent-purple to-accent-blue rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-3xl font-display font-bold">MJ</span>
                </div>
                <h3 class="text-xl font-display font-bold mb-2">Mike Johnson</h3>
                <p class="text-accent-cyan text-sm mb-4">UI/UX Designer</p>
                <p class="text-white/70 text-sm">
                    Creates intuitive and beautiful interfaces that make complex features easy to use.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-bg-secondary">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="glass-card p-12 rounded-2xl fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                Ready to Join Our Community?
            </h2>
            <p class="text-white/70 text-lg mb-8">
                Connect with thousands of gamers and experience the Atlas Menu difference
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo DISCORD_URL; ?>" target="_blank" class="btn-primary px-8 py-4 rounded-lg font-semibold text-lg">
                    Join Discord
                </a>
                <a href="index.php#pricing" class="btn-secondary px-8 py-4 rounded-lg font-semibold text-lg">
                    View Pricing
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
