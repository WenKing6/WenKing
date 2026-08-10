<?php
/**
 * 产品展示区块组件
 * 从数据库读取产品数据并动态渲染
 */

require_once __DIR__ . '/../models/Product.php';
$productModel = new Product();
$products = $productModel->getVisible();

$statusLabels = [
    'online'      => __('products.online'),
    'updating'    => __('products.updating'),
    'development' => __('products.development'),
];
?>
<section id="products" class="py-20 bg-bg-secondary" style="background-color: rgba(18, 18, 26, 0);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    <?php _e('products.title'); ?>
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                <?php _e('products.subtitle'); ?>
            </p>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $index => $p):
                $features = Product::parseFeatures($p['features']);
                $isOnline = $p['status'] === 'online';
                $delayClass = $index === 0 ? '' : ($index === 1 ? 'delay-200' : 'delay-400');
            ?>
            <div class="relative group fade-in-up <?php echo $delayClass; ?>">
                <div class="product-card p-8 rounded-xl h-full">
                    <div class="card-bg" style="background-image: url('<?php echo SITE_URL . htmlspecialchars($p['image']); ?>'); background-position: center <?php echo $index === 0 ? 'top' : ($index === 1 ? 'center' : 'bottom'); ?>;"></div>
                    <span class="status-badge status-<?php echo htmlspecialchars($p['status']); ?> mb-4 inline-block"><?php echo htmlspecialchars($statusLabels[$p['status']] ?? ucfirst($p['status'])); ?></span>
                    <h3 class="text-2xl font-display font-bold mb-2"><?php echo htmlspecialchars($p['name']); ?></h3>
                    <p class="text-white/60 mb-6 <?php echo $index === 0 ? 'text-left' : ''; ?>"><?php echo htmlspecialchars($p['tagline']); ?></p>
                    <?php if (!empty($features)): ?>
                    <ul class="space-y-3 mb-6">
                        <?php foreach ($features as $feature): ?>
                        <li class="flex items-center text-white/70 text-sm">
                            <svg class="w-4 h-4 mr-2 text-status-<?php echo htmlspecialchars($p['status']); ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <?php echo htmlspecialchars($feature); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php if ($isOnline && !empty($p['button_link'])): ?>
                    <a href="<?php echo SITE_URL . htmlspecialchars($p['button_link']); ?>" class="btn-primary block w-full py-3 rounded-lg font-semibold text-center">
                        <?php echo htmlspecialchars($p['button_text']); ?>
                    </a>
                    <?php else: ?>
                    <button class="btn-secondary block w-full py-3 rounded-lg font-semibold text-center text-white/50 cursor-not-allowed">
                        <?php echo htmlspecialchars($p['button_text']); ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
