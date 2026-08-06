# Partners 经销商展示页 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 创建一个独立的 `partners.php` 经销商展示页面，使用 GridScan 3D 背景，展示 3 个测试经销商卡片。

**Architecture:** 参考现有 `about.php` 页面结构，新建 `partners.php` 作为入口，复用 `header.php`、`nav.php`、`footer.php`；卡片网格抽成 `includes/sections/partners-grid.php`，内部用 PHP 数据数组循环渲染，保持与现有项目架构一致并便于后续复用。

**Tech Stack:** PHP, Tailwind CSS, 现有 WenKing 主样式与动画系统

---

## File Structure

| File | Action | Responsibility |
|------|--------|----------------|
| `partners.php` | Create | 页面入口，注入 GridScan 背景，引入 partners-grid 组件 |
| `includes/sections/partners-grid.php` | Create | 经销商数据数组 + 响应式卡片网格渲染 |

---

## Task 1: Create `includes/sections/partners-grid.php`

**Files:**
- Create: `includes/sections/partners-grid.php`

- [ ] **Step 1: Write the PHP data array and card grid component**

Create `includes/sections/partners-grid.php` with the following content:

```php
<?php
/**
 * Partners / Resellers Grid Section
 * Displays authorized reseller cards
 */

$partners = [
    [
        'name'        => 'Nova Mods',
        'status'      => 'official',
        'status_label'=> 'Official',
        'avatar'      => SITE_URL . '/assets/images/hero-bg.jpg',
        'description' => 'Premium GTA V mod menu reseller with instant delivery.',
        'website'     => 'https://nova-mods.example.com',
        'discord'     => 'NovaSupport',
        'telegram'    => '@novamods',
        'payments'    => ['PayPal', 'Credit Card', 'Crypto'],
        'rating'      => 5,
    ],
    [
        'name'        => 'Global Keys',
        'status'      => 'verified',
        'status_label'=> 'Verified',
        'avatar'      => SITE_URL . '/assets/images/hero-bg.jpg',
        'description' => 'Reliable keys and licenses for Atlas Menu products.',
        'website'     => 'https://globalkeys.example.com',
        'discord'     => 'GlobalKeys',
        'telegram'    => '@globalkeys',
        'payments'    => ['PayPal', 'Alipay', 'WeChat Pay'],
        'rating'      => 4,
    ],
    [
        'name'        => 'Elite Gaming',
        'status'      => 'trusted',
        'status_label'=> 'Trusted',
        'avatar'      => SITE_URL . '/assets/images/hero-bg.jpg',
        'description' => 'Trusted reseller offering 24/7 support and discounts.',
        'website'     => 'https://elitegaming.example.com',
        'discord'     => 'EliteGaming',
        'telegram'    => '@elitegaming',
        'payments'    => ['Crypto', 'Credit Card'],
        'rating'      => 5,
    ],
];

$status_colors = [
    'official' => 'bg-accent-purple/20 text-accent-purple border-accent-purple/30',
    'verified' => 'bg-accent-cyan/20 text-accent-cyan border-accent-cyan/30',
    'trusted'  => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
];
?>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 fade-in-up">
            <h2 class="text-h2 font-display font-bold mb-4">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    Authorized Resellers
                </span>
            </h2>
            <p class="text-white/70 text-lg max-w-2xl mx-auto">
                Trusted partners around the world. Choose a reseller to purchase our products.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($partners as $index => $partner): ?>
                <div class="glass-card p-6 rounded-2xl hover-lift fade-in-up" style="animation-delay: <?php echo $index * 100; ?>ms;">
                    <!-- Header: Avatar + Name + Status -->
                    <div class="flex items-center gap-4 mb-4">
                        <img src="<?php echo $partner['avatar']; ?>"
                             alt="<?php echo htmlspecialchars($partner['name']); ?>"
                             class="w-20 h-20 rounded-full object-cover border-2 border-white/10">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xl font-display font-bold text-white truncate">
                                <?php echo htmlspecialchars($partner['name']); ?>
                            </h3>
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded-full border <?php echo $status_colors[$partner['status']]; ?>">
                                <?php echo htmlspecialchars($partner['status_label']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="flex items-center gap-1 mb-3">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-4 h-4 <?php echo $i <= $partner['rating'] ? 'text-yellow-400' : 'text-white/20'; ?>"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        <?php endfor; ?>
                        <span class="ml-1 text-sm text-white/50">(<?php echo $partner['rating']; ?>/5)</span>
                    </div>

                    <!-- Description -->
                    <p class="text-white/70 text-sm mb-5 leading-relaxed">
                        <?php echo htmlspecialchars($partner['description']); ?>
                    </p>

                    <!-- Contact -->
                    <div class="space-y-2 mb-5 text-sm text-white/60">
                        <?php if (!empty($partner['discord'])): ?>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-accent-purple" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.699.772 1.364 1.225 1.994a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"></path>
                                </svg>
                                <span><?php echo htmlspecialchars($partner['discord']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($partner['telegram'])): ?>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-accent-cyan" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.944 0A12 12 0 0012 24a12 12 0 0012-12A12 12 0 0011.944 0zm0 0"></path>
                                    <path fill="#fff" d="M16.52 8.955c.085-.64-.507-1.195-1.142-1.072l-8.65 1.59c-.636.117-1.05.724-.91 1.357l1.387 6.32c.14.633.774 1.02 1.41.878l2.03-.46a.65.65 0 00.49-.778l-.46-2.158a.65.65 0 00-.778-.49l-1.25.283.26-1.186 6.32-1.387a.65.65 0 00.513-.744l-.18-1.053zm-3.71 3.228l-3.91.858-.45 2.05 1.466-.332a.65.65 0 01.778.49l.46 2.158a.65.65 0 01-.49.778l-2.03.46c-.636.143-1.27-.245-1.41-.878L8.28 11.83c-.14-.633.274-1.24.91-1.357l8.65-1.59c.635-.117 1.227.432 1.142 1.072l-.18 1.053a.65.65 0 01-.744.513l-4.17.915-.163-.273z"></path>
                                </svg>
                                <span><?php echo htmlspecialchars($partner['telegram']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Payments -->
                    <div class="mb-5">
                        <div class="text-xs text-white/40 mb-2">Accepted Payments</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($partner['payments'] as $payment): ?>
                                <span class="px-2 py-1 text-xs rounded-md bg-white/10 text-white/80 border border-white/10">
                                    <?php echo htmlspecialchars($payment); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Website Button -->
                    <a href="<?php echo htmlspecialchars($partner['website']); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-primary w-full block text-center py-2.5 rounded-lg font-semibold">
                        Visit Website
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Verify file created**

Check that `includes/sections/partners-grid.php` exists and contains the expected structure.

---

## Task 2: Create `partners.php`

**Files:**
- Create: `partners.php`

- [ ] **Step 1: Write the page entry file**

Create `partners.php` with the following content:

```php
<?php
/**
 * Partners / Authorized Resellers Page
 */
require_once __DIR__ . '/config/config.php';

define('PAGE_TITLE', 'Partners - ' . SITE_NAME);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<!-- GridScan 全局背景动画 -->
<div id="grid-scan-bg" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; pointer-events: none;"></div>

<main>
    <!-- Page Header -->
    <section class="relative pt-32 pb-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-accent-purple/10 via-bg-primary to-accent-blue/10"></div>
        <div class="glow-orb glow-orb-1"></div>
        <div class="glow-orb glow-orb-2"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-h1 font-display font-bold mb-6 fade-in-up">
                <span class="bg-gradient-to-r from-accent-purple to-accent-cyan bg-clip-text text-transparent">
                    Authorized Resellers
                </span>
            </h1>
            <p class="text-xl text-white/70 max-w-3xl mx-auto fade-in-up delay-100">
                Browse our trusted network of official partners and resellers.
            </p>
        </div>
    </section>

    <!-- Partners Grid -->
    <?php require_once __DIR__ . '/includes/sections/partners-grid.php'; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

- [ ] **Step 2: Verify file created**

Check that `partners.php` exists and matches the structure of `about.php`.

---

## Task 3: Local Test

**Files:**
- Test: Open `http://localhost/partners.php` (or your configured local URL)

- [ ] **Step 1: Start local server if not running**

Run: `php -S localhost:8000 -t e:\VibeCoding-2`

- [ ] **Step 2: Open the page in browser**

Navigate to `http://localhost:8000/partners.php`.

- [ ] **Step 3: Verify visual requirements**

Expected:
- GridScan 3D background animation is visible
- Page header shows "Authorized Resellers" title and subtitle
- 3 dealer cards are displayed in a responsive grid
- Each card shows: avatar (hero-bg.jpg), name, status badge, star rating, description, Discord/Telegram, payment methods, "Visit Website" button
- Cards use glass-card style with hover-lift effect
- Layout works on desktop (3 columns), tablet (2 columns), mobile (1 column)

---

## Task 4: Commit

- [ ] **Step 1: Stage new files**

```bash
git add partners.php includes/sections/partners-grid.php docs/superpowers/plans/2026-08-01-partners-page.md
```

- [ ] **Step 2: Commit**

```bash
git commit -m "feat: add partners page with authorized reseller cards"
```
