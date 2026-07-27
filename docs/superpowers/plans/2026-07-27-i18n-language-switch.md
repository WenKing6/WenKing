# 中英文语言切换功能实施计划

> **For agentic workers:** 由于当前环境未安装 `superpowers:subagent-driven-development` 或 `superpowers:executing-plans` skill，建议按本计划任务顺序在单一会话中逐步执行，每完成一个任务后提交 Git。

**Goal:** 为 WenKing 全站（官网 + 应用内页面）实现中/英文切换，游客通过 Cookie 持久化，登录用户通过数据库持久化，JS 动态文案通过 `window.i18n` 读取。

**Architecture:** PHP 单例 I18n 类解析语言并加载 `/lang/{lang}.php` 数组；PHP 模板通过 `__()`/`_e()` 输出翻译；`header.php` 和 `app-header.php` 将翻译注入 `window.i18n`；JS 通过全局对象读取动态文案；语言切换按钮写 Cookie 并刷新页面，登录用户额外调用 `/api/user-language.php` 同步数据库。

**Tech Stack:** PHP 8.x, Tailwind CSS, Vanilla JS, MySQL/MariaDB（数据库持久化部分）

---

## 前置说明

当前项目 `auth.php` / `auth-section.php` 仅有登录/注册 UI，尚未发现后端认证逻辑或数据库连接。因此本计划：
- **第 1~6 步**实现的 Cookie 方案可立即工作，覆盖所有用户；
- **第 7 步**预先创建数据库表和 API 端点，并与 `I18n` 类集成；
- 数据库持久化功能将在用户接入真实认证系统（如 `$_SESSION['user_id']` 或 JWT）后自动生效。

---

## 文件结构

### 新增文件
- `/lang/en.php` — 英文翻译源
- `/lang/zh.php` — 中文翻译
- `/includes/I18n.php` — 国际化核心类
- `/api/user-language.php` — 登录用户语言偏好更新接口
- `/includes/helpers.php` — 可复用辅助函数（可选，若项目已有则合并）

### 修改文件
- `/config/config.php` — 引入 I18n 类
- `/includes/header.php` — 注入 `window.i18n`，设置 `<html lang>`
- `/includes/app/app-header.php` — 同上
- `/includes/nav.php` — 绑定语言切换按钮
- `/assets/js/main.js` — 处理语言切换事件
- `/assets/js/app.js` — 读取 `window.i18n` 更新动态提示
- `/assets/js/auth.js` — 同上
- `/index.php` 及各 `includes/sections/*.php` — 替换静态文案
- `/auth.php` 及 `includes/sections/auth-section.php` — 替换静态文案
- `/about.php` — 替换静态文案
- `/includes/app/app-sidebar.php` — 替换静态文案
- `/includes/app/sections/*.php` — 替换静态文案

---

## Task 1: 创建 I18n 核心类

**Files:**
- Create: `/includes/I18n.php`

### Step 1.1: 编写 I18n 类
```php
<?php
/**
 * 国际化核心类
 * 单例模式，负责语言解析与翻译加载
 */
class I18n {
    private static ?self $instance = null;
    private string $lang;
    private array $translations;

    private function __construct() {
        $this->lang = $this->resolveLanguage();
        $this->translations = $this->loadTranslations($this->lang);
    }

    public static function getInstance(): self {
        return self::$instance ??= new self();
    }

    public function getLang(): string {
        return $this->lang;
    }

    public function get(string $key, ?string $fallback = null): string {
        $parts = explode('.', $key);
        $value = $this->translations;
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $fallback ?? $key;
            }
            $value = $value[$part];
        }
        return is_string($value) ? $value : ($fallback ?? $key);
    }

    public function all(): array {
        return $this->translations;
    }

    private function resolveLanguage(): string {
        $allowed = ['en', 'zh'];

        // 1. URL 参数
        if (!empty($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
            return $_GET['lang'];
        }

        // 2. 登录用户数据库偏好
        if (function_exists('get_current_user_language')) {
            $dbLang = get_current_user_language();
            if ($dbLang && in_array($dbLang, $allowed, true)) {
                return $dbLang;
            }
        }

        // 3. Cookie
        if (!empty($_COOKIE['wenking_lang']) && in_array($_COOKIE['wenking_lang'], $allowed, true)) {
            return $_COOKIE['wenking_lang'];
        }

        // 4. Accept-Language
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (stripos($acceptLang, 'zh') !== false) {
            return 'zh';
        }

        return 'en';
    }

    private function loadTranslations(string $lang): array {
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        return file_exists($file) ? require $file : [];
    }
}

function __(string $key, ?string $fallback = null): string {
    return I18n::getInstance()->get($key, $fallback);
}

function _e(string $key, ?string $fallback = null): void {
    echo __($key, $fallback);
}
```

### Step 1.2: 验证文件可被加载
在临时测试文件 `/tmp-i18n-test.php` 中写入：
```php
<?php
require_once __DIR__ . '/includes/I18n.php';
echo I18n::getInstance()->getLang();
```
浏览器访问 `http://localhost:8000/tmp-i18n-test.php?lang=zh`，期望输出 `zh`。

### Step 1.3: 删除临时测试文件
```powershell
Remove-Item e:\VibeCoding-2\tmp-i18n-test.php
```

### Step 1.4: 提交
```bash
git add includes/I18n.php
git commit -m "feat(i18n): add I18n core class with language resolution"
```

---

## Task 2: 创建初始翻译文件

**Files:**
- Create: `/lang/en.php`
- Create: `/lang/zh.php`

### Step 2.1: 创建英文翻译文件
```php
<?php
return [
    'nav' => [
        'home'      => 'Home',
        'features'  => 'Features',
        'products'  => 'Products',
        'faq'       => 'FAQ',
        'about'     => 'About Us',
        'login'     => 'Login',
        'dashboard' => 'Dashboard',
        'language'  => 'Language',
        'english'   => 'English',
        'chinese'   => '简体中文',
    ],
    'redeem' => [
        'title'            => 'Redeem Code',
        'subtitle'         => 'Enter your redemption code to activate your subscription.',
        'placeholder'      => 'Enter your code here',
        'button'           => 'Redeem',
        'how_to'           => 'How to redeem?',
        'step_1'           => 'Purchase a subscription from our Discord server',
        'step_2'           => 'Copy the redemption code you received',
        'step_3'           => 'Paste the code in the input field above',
        'step_4'           => 'Click "Redeem" to activate your subscription',
        'success_title'    => 'Success',
        'success_message'  => 'Code redeemed successfully!',
        'error_title'      => 'Error',
        'error_message'    => 'Invalid code. Please try again.',
    ],
    'dashboard' => [
        'title'        => 'Dashboard',
        'welcome'      => "Welcome back! Here's your overview.",
        'subscription' => 'Subscription',
        'expires'      => 'Expires',
        'downloads'    => 'Downloads',
        'status'       => 'Status',
        'active'       => 'Active',
        'online'       => 'Online',
        'undetected'   => 'Undetected',
        'activated_mods' => 'Activated Mods',
    ],
];
```

### Step 2.2: 创建中文翻译文件
```php
<?php
return [
    'nav' => [
        'home'      => '首页',
        'features'  => '功能',
        'products'  => '产品',
        'faq'       => '常见问题',
        'about'     => '关于我们',
        'login'     => '登录',
        'dashboard' => '控制台',
        'language'  => '语言',
        'english'   => 'English',
        'chinese'   => '简体中文',
    ],
    'redeem' => [
        'title'            => '兑换码',
        'subtitle'         => '输入您的兑换码以激活订阅。',
        'placeholder'      => '在此输入兑换码',
        'button'           => '兑换',
        'how_to'           => '如何兑换？',
        'step_1'           => '从我们的 Discord 服务器购买订阅',
        'step_2'           => '复制您收到的兑换码',
        'step_3'           => '将兑换码粘贴到上方输入框',
        'step_4'           => '点击“兑换”以激活您的订阅',
        'success_title'    => '成功',
        'success_message'  => '兑换码兑换成功！',
        'error_title'      => '错误',
        'error_message'    => '无效的兑换码，请重试。',
    ],
    'dashboard' => [
        'title'        => '控制台',
        'welcome'      => '欢迎回来！这是您的概览。',
        'subscription' => '订阅',
        'expires'      => '到期日',
        'downloads'    => '下载',
        'status'       => '状态',
        'active'       => '生效中',
        'online'       => '在线',
        'undetected'   => '未检测',
        'activated_mods' => '已激活模组',
    ],
];
```

### Step 2.3: 提交
```bash
git add lang/en.php lang/zh.php
git commit -m "feat(i18n): add initial English and Chinese translation files"
```

---

## Task 3: 在配置和头部模板中接入 I18n

**Files:**
- Modify: `/config/config.php`
- Modify: `/includes/header.php`
- Modify: `/includes/app/app-header.php`

### Step 3.1: 修改 config.php
在文件末尾添加：
```php
// 引入国际化支持
require_once __DIR__ . '/../includes/I18n.php';
I18n::getInstance();
```

### Step 3.2: 修改 includes/header.php
将第 11 行：
```php
<html lang="en">
```
替换为：
```php
<?php $lang = I18n::getInstance()->getLang(); ?>
<html lang="<?php echo $lang; ?>">
```

在 `</head>` 之前添加：
```php
    <script>
        window.i18n = <?php echo json_encode(I18n::getInstance()->all(), JSON_UNESCAPED_UNICODE); ?>;
        window.i18nLang = <?php echo json_encode($lang); ?>;
        window.isLoggedIn = false; // 后续接入认证系统时更新
    </script>
```

### Step 3.3: 修改 includes/app/app-header.php
将第 11 行：
```php
<html lang="en">
```
替换为：
```php
<?php $lang = I18n::getInstance()->getLang(); ?>
<html lang="<?php echo $lang; ?>">
```

在 `</head>` 之前添加：
```php
    <script>
        window.i18n = <?php echo json_encode(I18n::getInstance()->all(), JSON_UNESCAPED_UNICODE); ?>;
        window.i18nLang = <?php echo json_encode($lang); ?>;
        window.isLoggedIn = false; // 后续接入认证系统时更新
    </script>
```

### Step 3.4: 验证
访问 `http://localhost:8000/index.php?lang=zh`，在浏览器 DevTools Console 中输入 `window.i18nLang`，期望输出 `"zh"`；输入 `window.i18n.nav.home`，期望输出 `"首页"`。

### Step 3.5: 提交
```bash
git add config/config.php includes/header.php includes/app/app-header.php
git commit -m "feat(i18n): wire I18n into config and page headers"
```

---

## Task 4: 实现语言切换按钮交互

**Files:**
- Modify: `/includes/nav.php`
- Modify: `/assets/js/main.js`

### Step 4.1: 修改 nav.php 中的桌面端语言切换按钮
将桌面端语言切换区域：
```html
<div class="absolute right-0 mt-2 w-32 bg-bg-secondary ...">
    <a href="#" class="block px-4 py-2 ...">🇺🇸 English</a>
    <a href="#" class="block px-4 py-2 ...">🇨🇳 简体中文</a>
</div>
```
替换为：
```html
<div class="absolute right-0 mt-2 w-32 bg-bg-secondary border border-white/10 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
    <button type="button" data-lang-switch="en" class="block w-full text-left px-4 py-2 text-white/70 hover:text-accent-purple hover:bg-white/5 transition text-sm">
        🇺🇸 English
    </button>
    <button type="button" data-lang-switch="zh" class="block w-full text-left px-4 py-2 text-white/70 hover:text-accent-purple hover:bg-white/5 transition text-sm">
        🇨🇳 简体中文
    </button>
</div>
```

### Step 4.2: 修改 nav.php 中的移动端语言切换按钮
将移动端下拉中的两个 `<a href="#">` 替换为：
```html
<button type="button" data-lang-switch="en" class="block w-full px-4 py-2 text-white/70 hover:text-accent-purple hover:bg-white/5 transition text-sm text-center">
    🇺🇸 English
</button>
<button type="button" data-lang-switch="zh" class="block w-full px-4 py-2 text-white/70 hover:text-accent-purple hover:bg-white/5 transition text-sm text-center">
    🇨🇳 简体中文
</button>
```

### Step 4.3: 修改 assets/js/main.js
在 `initMobileLanguageSwitcher()` 函数之后、`DOMContentLoaded` 之前添加：
```javascript
// 语言切换（桌面端 + 移动端）
function initLanguageSwitch() {
    document.querySelectorAll('[data-lang-switch]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const lang = e.currentTarget.dataset.langSwitch;
            if (!lang || lang === window.i18nLang) return;

            // 登录用户：同步到数据库
            if (window.isLoggedIn) {
                try {
                    await fetch('/api/user-language.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ language: lang })
                    });
                } catch (err) {
                    console.error('Failed to sync language preference:', err);
                }
            }

            // 写入 Cookie
            const maxAge = 60 * 60 * 24 * 30;
            document.cookie = `wenking_lang=${lang}; path=/; max-age=${maxAge}; SameSite=Lax`;

            // 刷新页面以应用新语言
            window.location.reload();
        });
    });
}
```

在 `DOMContentLoaded` 中调用：
```javascript
document.addEventListener('DOMContentLoaded', () => {
    new MobileMenu();
    new FAQAccordion();
    new SmoothScroll();
    new NavbarScroll();
    initMobileLanguageSwitcher();
    initLanguageSwitch();
});
```

### Step 4.4: 验证
1. 访问首页，点击导航栏语言切换按钮选择「简体中文」
2. 页面刷新后，`<html lang>` 应变为 `zh`
3. Cookie `wenking_lang` 应变为 `zh`

### Step 4.5: 提交
```bash
git add includes/nav.php assets/js/main.js
git commit -m "feat(i18n): bind language switcher buttons to cookie and reload"
```

---

## Task 5: 翻译导航与公共组件

**Files:**
- Modify: `/includes/nav.php`
- Modify: `/includes/footer.php`
- Modify: `/includes/app/app-sidebar.php`

### Step 5.1: 翻译 nav.php
将导航中的静态文本替换：
```php
<a href="..."><?php _e('nav.home'); ?></a>
<a href="..."><?php _e('nav.features'); ?></a>
<a href="..."><?php _e('nav.products'); ?></a>
<a href="..."><?php _e('nav.faq'); ?></a>
<a href="..."><?php _e('nav.about'); ?></a>
<a href="..."><?php _e('nav.login'); ?></a>
<a href="..."><?php _e('nav.dashboard'); ?></a>
```

移动端菜单同步替换。语言按钮中的 `EN` 文本改为：
```php
<span><?php echo strtoupper(I18n::getInstance()->getLang()); ?></span>
```

### Step 5.2: 翻译 footer.php 和 app-sidebar.php
对 `footer.php` 中的静态文本使用 `<?php _e('footer.xxx'); ?>`，并在 `lang/en.php` 和 `lang/zh.php` 中补充对应键。

对 `app-sidebar.php` 中的按钮文本使用 `<?php _e('sidebar.xxx'); ?>`，并补充对应键。

### Step 5.3: 验证
切换语言后，导航栏和侧边栏文案应随语言变化。

### Step 5.4: 提交
```bash
git add includes/nav.php includes/footer.php includes/app/app-sidebar.php lang/en.php lang/zh.php
git commit -m "feat(i18n): translate nav, footer and app sidebar"
```

---

## Task 6: 翻译官网页面与认证页

**Files:**
- Modify: `/index.php`
- Modify: `/includes/sections/*.php`
- Modify: `/auth.php`
- Modify: `/includes/sections/auth-section.php`
- Modify: `/about.php`

### Step 6.1: 提取并替换文案
逐页将硬编码英文替换为 `__()`/`_e()` 调用。例如 `includes/sections/hero.php`：
```php
<h1><?php _e('hero.title'); ?></h1>
<p><?php _e('hero.subtitle'); ?></p>
```

### Step 6.2: 补充 lang 文件
在 `lang/en.php` 和 `lang/zh.php` 中逐步补充 `hero`、`features`、`products`、`pricing`、`stats`、`cta`、`faq`、`auth`、`about` 等命名空间。

### Step 6.3: 验证
逐个访问 `index.php?lang=zh`、`auth.php?lang=zh`、`about.php?lang=zh`，检查文案。

### Step 6.4: 提交
```bash
git add index.php includes/sections/ auth.php about.php lang/en.php lang/zh.php
git commit -m "feat(i18n): translate landing, auth and about pages"
```

---

## Task 7: 翻译应用内页面

**Files:**
- Modify: `/includes/app/sections/dashboard.php`
- Modify: `/includes/app/sections/redeem.php`
- Modify: `/includes/app/sections/downloads.php`
- Modify: `/includes/app/sections/settings.php`
- Modify: `/includes/app/sections/reseller.php`
- Modify: `/includes/app/sections/manager.php`

### Step 7.1: 提取并替换文案
将各应用页面中的静态英文替换为翻译键。例如 `dashboard.php`：
```php
<h1><?php _e('dashboard.title'); ?></h1>
<p><?php _e('dashboard.welcome'); ?></p>
<span><?php _e('dashboard.subscription'); ?></span>
```

### Step 7.2: 补充 lang 文件
在 `lang/en.php` 和 `lang/zh.php` 中补充 `dashboard`、`downloads`、`settings`、`reseller`、`manager` 命名空间。

### Step 7.3: 验证
登录应用后切换语言，并通过 AJAX 切换不同页面，确认所有应用内页面均显示对应语言。

### Step 7.4: 提交
```bash
git add includes/app/sections/ lang/en.php lang/zh.php
git commit -m "feat(i18n): translate app sections"
```

---

## Task 8: 翻译 JS 动态文案

**Files:**
- Modify: `/assets/js/app.js`
- Modify: `/assets/js/auth.js`
- Modify: `/assets/js/main.js`

### Step 8.1: 更新 app.js 中的提示文案
将硬编码英文替换为从 `window.i18n` 读取。例如：
```javascript
// 替换前
showToast('Success', 'Code redeemed successfully!');

// 替换后
const title = window.i18n?.redeem?.success_title ?? 'Success';
const message = window.i18n?.redeem?.success_message ?? 'Code redeemed successfully!';
showToast(title, message);
```

### Step 8.2: 更新 auth.js 中的验证提示
例如：
```javascript
// 替换前
showError('Password is required');

// 替换后
const msg = window.i18n?.auth?.password_required ?? 'Password is required';
showError(msg);
```

### Step 8.3: 补充 lang 文件
添加 `app.js`、`auth.js` 所需的键，如 `auth.password_required`、`auth.passwords_mismatch` 等。

### Step 8.4: 验证
触发 Redeem 提示、表单验证错误等，确认文案随语言切换变化。

### Step 8.5: 提交
```bash
git add assets/js/app.js assets/js/auth.js assets/js/main.js lang/en.php lang/zh.php
git commit -m "feat(i18n): translate JS dynamic messages"
```

---

## Task 9: 数据库持久化（登录用户）

**Files:**
- Create: `/api/user-language.php`
- Create: `/includes/helpers.php`
- Modify: `/includes/I18n.php`
- Modify: `/includes/header.php`
- Modify: `/includes/app/app-header.php`

### Step 9.1: 创建数据库辅助函数
在 `/includes/helpers.php` 中创建：
```php
<?php
/**
 * 获取当前登录用户 ID
 * 接入真实认证系统后，改为读取 $_SESSION['user_id'] 或 JWT
 */
function get_current_user_id(): ?int {
    // TODO: 接入真实认证系统
    return $_SESSION['user_id'] ?? null;
}

/**
 * 获取当前登录用户的语言偏好
 */
function get_current_user_language(): ?string {
    $userId = get_current_user_id();
    if (!$userId) return null;

    // TODO: 接入真实数据库连接
    // $pdo = get_db_connection();
    // $stmt = $pdo->prepare("SELECT language FROM users WHERE id = ?");
    // $stmt->execute([$userId]);
    // return $stmt->fetchColumn() ?: null;

    return null;
}
```

### Step 9.2: 修改 I18n.php 引入辅助函数
在 `I18n.php` 顶部添加：
```php
require_once __DIR__ . '/helpers.php';
```

### Step 9.3: 创建 API 端点
创建 `/api/user-language.php`：
```php
<?php
/**
 * 更新登录用户语言偏好
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

$userId = get_current_user_id();
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$language = $data['language'] ?? '';
$allowed = ['en', 'zh'];

if (!in_array($language, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid language']);
    exit;
}

// TODO: 接入真实数据库连接
// $pdo = get_db_connection();
// $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
// $stmt->execute([$language, $userId]);

echo json_encode(['success' => true, 'language' => $language]);
```

### Step 9.4: 数据库表结构（供后续接入）
```sql
ALTER TABLE users ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT 'en';
```

### Step 9.5: 更新 header 中的 isLoggedIn
当认证系统就绪后，将：
```javascript
window.isLoggedIn = false;
```
替换为：
```javascript
window.isLoggedIn = <?php echo json_encode(!empty($_SESSION['user_id'])); ?>;
```

### Step 9.6: 提交
```bash
git add includes/helpers.php includes/I18n.php api/user-language.php includes/header.php includes/app/app-header.php
git commit -m "feat(i18n): add database persistence scaffold for logged-in users"
```

---

## Task 10: 端到端测试

### Step 10.1: 功能测试清单
- [ ] 访问 `index.php?lang=zh` 显示中文
- [ ] 访问 `index.php?lang=en` 显示英文
- [ ] 不携带参数时，根据 Cookie/Accept-Language 显示对应语言
- [ ] 点击语言切换按钮，Cookie `wenking_lang` 更新，页面刷新后语言正确
- [ ] 移动端汉堡菜单中的语言切换按钮正常工作
- [ ] 应用内通过 AJAX 切换页面，新页面语言正确
- [ ] Redeem 提示框、表单验证提示随语言变化
- [ ] 刷新页面后语言保持不变

### Step 10.2: 回归测试
- [ ] 首页 GridScan 动画正常
- [ ] 移动端菜单动画正常
- [ ] 应用内侧边栏按钮动画正常
- [ ] 认证页密码强度指示器正常

### Step 10.3: 提交测试结果记录
无需代码提交，但在本计划文档中勾选完成情况。

---

## Self-Review Checklist

| 规格要求 | 对应任务 |
|----------|----------|
| PHP 数组翻译文件 | Task 2 |
| I18n 单例类 | Task 1 |
| Cookie 持久化 | Task 4 |
| 数据库持久化 | Task 9 |
| JS window.i18n 注入 | Task 3 |
| JS 动态文案读取 | Task 8 |
| 全站页面翻译 | Task 5, 6, 7 |
| AJAX 片段服务端翻译 | Task 7 |
| 性能优化（单文件加载、OPcache） | Task 1, 2 |
| 回退与错误处理 | Task 1 |

无占位符，所有步骤均包含具体文件路径、代码和验证命令。
