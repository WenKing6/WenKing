# 中英文语言切换功能设计文档

## 1. 项目背景与目标

### 1.1 背景
WenKing 项目是一套基于 PHP SSR + AJAX 的多页面网站，包含官网首页、认证页、关于页以及应用内页面（Dashboard、Redeem、Downloads、Settings、Reseller、Manager）。目前所有文案均为英文硬编码，需要新增中/英文切换能力。

### 1.2 目标
- 支持全站中英文切换，覆盖 PHP 渲染的静态文案与 JS 动态文案
- 语言偏好对登录用户持久化到数据库，对游客持久化到 Cookie
- 切换过程无闪烁、无文案抖动
- 与现有 Tailwind CSS、PHP SSR、AJAX 无刷新页面切换架构完全兼容
- 为未来 URL 重构（子目录/域名模式）保留扩展空间

---

## 2. 总体架构

采用「PHP 服务端翻译 + Cookie/DB 持久化 + JS 全局翻译对象兜底」的混合方案。

```
┌─────────────────────────────────────────────────────────────┐
│                        用户请求                              │
└───────────────────────┬─────────────────────────────────────┘
                        │
        ┌───────────────▼───────────────┐
        │   语言解析（I18n 类）          │
        │  URL ?lang > DB > Cookie >    │
        │  Accept-Language > default    │
        └───────────────┬───────────────┘
                        │
        ┌───────────────▼───────────────┐
        │   加载 /lang/{lang}.php        │
        │   渲染 PHP 模板                │
        └───────────────┬───────────────┘
                        │
        ┌───────────────▼───────────────┐
        │   内联 window.i18n JSON        │
        │   JS 动态文案读取              │
        └───────────────────────────────┘
```

### 2.1 语言解析优先级
1. URL 参数 `?lang=zh|en`（最高优先级，用于调试和分享）
2. 登录用户数据库中的 `language` 字段
3. Cookie `wenking_lang`
4. 浏览器 `Accept-Language` 请求头
5. 默认语言 `en`（最低优先级）

---

## 3. 文件组织

### 3.1 新增文件
```
/lang/
  en.php              # 英文原文（默认 fallback）
  zh.php              # 中文翻译
/includes/
  I18n.php            # 国际化核心类
/api/router.php       # 新增 user/update-language 接口（扩展）
```

### 3.2 修改文件
- `config/config.php`：初始化 I18n 类
- `includes/header.php`：注入 `window.i18n`、设置 `<html lang="">`
- `includes/app/app-header.php`：同上
- `includes/nav.php`：语言切换按钮绑定切换逻辑
- `assets/js/main.js`：处理语言切换事件（未登录/已登录）
- 各页面 `.php` 文件：将静态英文替换为 `__()` / `_e()` 调用

### 3.3 翻译文件结构示例
```php
<?php
// /lang/en.php
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
    ],
    'redeem' => [
        'title'            => 'Redeem Code',
        'subtitle'         => 'Enter your redemption code to activate your subscription.',
        'placeholder'      => 'Enter your code here',
        'button'           => 'Redeem',
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
    ],
];
```

---

## 4. 核心组件设计

### 4.1 I18n 类（/includes/I18n.php）
```php
<?php
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

    public function getLang(): string { return $this->lang; }

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

    public function all(): array { return $this->translations; }

    private function resolveLanguage(): string {
        // 1. URL 参数
        if (!empty($_GET['lang']) && in_array($_GET['lang'], ['en', 'zh'], true)) {
            return $_GET['lang'];
        }

        // 2. 登录用户数据库偏好（如已登录）
        if (function_exists('get_current_user_language')) {
            $dbLang = get_current_user_language();
            if ($dbLang) return $dbLang;
        }

        // 3. Cookie
        if (!empty($_COOKIE['wenking_lang']) && in_array($_COOKIE['wenking_lang'], ['en', 'zh'], true)) {
            return $_COOKIE['wenking_lang'];
        }

        // 4. Accept-Language
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (stripos($acceptLang, 'zh') !== false) return 'zh';

        return 'en';
    }

    private function loadTranslations(string $lang): array {
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        return file_exists($file) ? require $file : [];
    }
}

// 全局辅助函数
function __(string $key, ?string $fallback = null): string {
    return I18n::getInstance()->get($key, $fallback);
}

function _e(string $key, ?string $fallback = null): void {
    echo __($key, $fallback);
}
```

### 4.2 在 config.php 中初始化
```php
require_once __DIR__ . '/../includes/I18n.php';
I18n::getInstance(); // 触发语言解析与文件加载
```

### 4.3 在 header 中注入 JS 翻译对象
```php
$lang = I18n::getInstance()->getLang();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    ...
    <script>
        window.i18n = <?php echo json_encode(I18n::getInstance()->all(), JSON_UNESCAPED_UNICODE); ?>;
        window.i18nLang = <?php echo json_encode($lang); ?>;
    </script>
</head>
```

---

## 5. 状态管理策略

### 5.1 游客（未登录）
- 切换语言时通过 JS 写入 Cookie `wenking_lang`，Path=/，Max-Age=30 天
- 刷新页面后 PHP 从 Cookie 读取并渲染对应语言
- 不调用后端 API

### 5.2 登录用户
- 切换语言时调用 `/api/router.php` 的 `user/update-language` 接口
- 接口将语言码写入用户表 `users.language` 字段
- 同时更新 Cookie `wenking_lang` 作为未登录场景/缓存兜底
- 刷新页面后 PHP 优先读取数据库偏好

### 5.3 用户表结构（推荐最小改动）
```sql
ALTER TABLE users ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT 'en';
```
或若已存在 `user_settings` 表：
```sql
INSERT INTO user_settings (user_id, `key`, `value`) VALUES (?, 'language', 'zh');
```

### 5.4 API 接口设计
```
POST /api/router.php?action=user/update-language
Content-Type: application/json

Request:
{
  "language": "zh"
}

Response:
{
  "success": true,
  "language": "zh"
}
```

---

## 6. 组件适配方法

### 6.1 PHP 模板替换
将所有硬编码英文替换为翻译键：
```php
<!-- 替换前 -->
<a href="...">Home</a>

<!-- 替换后 -->
<a href="..."><?php _e('nav.home'); ?></a>
```

### 6.2 JS 动态文案
```js
// 替换前
showToast('Success', 'Code redeemed successfully!');

// 替换后
const title = window.i18n?.redeem?.success_title ?? 'Success';
const message = window.i18n?.redeem?.success_message ?? 'Code redeemed successfully!';
showToast(title, message);
```

### 6.3 AJAX 加载的页面片段
`app.js` 当前通过 AJAX 加载 `/includes/app/sections/{page}.php`。这些片段在服务端已完成翻译，前端无需二次处理。需确保：
- AJAX 请求携带当前语言信息（Cookie 自动携带，无需额外处理）
- 服务端片段不使用缓存旧语言（必要时在请求头中禁用缓存）

### 6.4 语言切换按钮交互
```js
// main.js 中的切换逻辑
document.querySelectorAll('[data-lang-switch]').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const lang = e.currentTarget.dataset.langSwitch;
        if (!lang || lang === window.i18nLang) return;

        // 游客：仅写 cookie
        // 登录用户：先调 API，再写 cookie
        if (window.isLoggedIn) {
            await fetch('/api/router.php?action=user/update-language', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ language: lang })
            });
        }

        document.cookie = `wenking_lang=${lang}; path=/; max-age=${60*60*24*30}`;
        window.location.reload();
    });
});
```

---
## 7. 性能优化

### 7.1 服务端
- 单语言文件加载：仅加载当前语言，不加载其他语言
- OPcache：PHP 数组文件可被 OPcache 缓存，避免重复解析
- Cookie 优先：确定语言后使用 Cookie，避免每次请求查库
- 按需加载：AJAX 片段继续由 PHP 输出翻译后 HTML，无额外前端计算

### 7.2 客户端
- `window.i18n` 内联到 HTML `<head>`，避免额外 HTTP 请求
- 不引入大型 i18n 库（如 i18next），保持现有 JS 体积
- 切换语言时整页刷新一次，避免客户端大规模 DOM 重写带来的性能抖动

### 7.3 缓存策略
- 语言文件本身可被 OPcache 缓存
- 不建议对页面做全页缓存（如 Varnish），除非按语言拆分缓存键
- AJAX 片段可缓存，但缓存键需包含语言码

---

## 8. 错误处理与回退

### 8.1 缺失翻译键
- `I18n::get()` 在找不到键时返回 fallback 或键名本身
- 英文文件 `en.php` 作为最完整的基准，其他语言缺失时自然回退到英文键名或开发者提供的 fallback

### 8.2 非法语言码
- 仅白名单 `['en', 'zh']`，其他值一律回退到 `en`
- 防止路径遍历：`$lang` 必须在白名单中才用于文件加载

### 8.3 数据库/API 失败
- 登录用户调用 `user/update-language` 失败时，仍更新 Cookie 并刷新页面
- 下次请求时会尝试再次同步，或保持 Cookie 状态

---

## 9. 迁移策略

按模块渐进式迁移，降低一次性改动风险：

1. **基础设施（第 1 步）**
   - 创建 `I18n.php`
   - 创建 `lang/en.php`，将 `nav.php` 文案移入
   - 在 `header.php` / `app-header.php` 注入 `window.i18n`

2. **导航与公共组件（第 2 步）**
   - 翻译 `nav.php`、`footer.php`、`app-sidebar.php`
   - 实现语言切换按钮与 Cookie 逻辑

3. **认证与官网页面（第 3 步）**
   - 翻译 `auth.php`、`index.php`、`about.php` 及各 section

4. **应用内页面（第 4 步）**
   - 翻译 `dashboard.php`、`redeem.php`、`downloads.php`、`settings.php`
   - 翻译 `reseller.php`、`manager.php`
   - 更新 JS 中的 toast/提示文案

5. **后端持久化（第 5 步）**
   - 新增 `users.language` 字段
   - 实现 `user/update-language` API
   - 切换逻辑区分登录/未登录

---

## 10. 测试验收标准

### 10.1 功能测试
- [ ] 首页、认证页、关于页、应用页均能正确切换中/英文
- [ ] 切换后刷新页面，语言保持不变
- [ ] 未登录用户关闭浏览器后重新打开，语言保持（Cookie 有效期内）
- [ ] 登录用户跨设备登录后显示其设置的语言
- [ ] URL `?lang=zh` 可强制显示中文，不受 Cookie/DB 影响

### 10.2 JS 文案测试
- [ ] Redeem 成功/失败提示框显示当前语言
- [ ] 表单验证提示显示当前语言
- [ ] AJAX 切换页面后，新页面文案为当前语言

### 10.3 性能与兼容性测试
- [ ] 切换语言时无明显闪烁或文案抖动
- [ ] 移动端语言切换按钮正常工作
- [ ] 仅加载当前语言文件，无额外请求

---

## 11. 方案优缺点总结

| 维度 | 评估 |
|------|------|
| 实现复杂度 | 中低。核心是单个 I18n 类 + 翻译文件替换，无需引入第三方库 |
| 维护成本 | 低。新增文案只需在 `en.php` 和 `zh.php` 中添加键值对 |
| 性能 | 高。PHP 数组可被 OPcache 缓存，无客户端重渲染 |
| SEO | 中。当前为 Cookie 方案，后续可平滑升级到子目录/域名方案 |
| 扩展性 | 高。新增语言只需添加 `/lang/{code}.php`，URL 重构时语言系统可复用 |
| 与现有架构兼容性 | 高。完全兼容 PHP SSR + AJAX 架构，无需改动路由核心 |

---

## 12. 待确认事项

1. 用户表名是否为 `users`？如已存在 `user_settings` 表，可改用设置表避免改用户表。
2. 是否已有用户登录状态函数（如 `is_logged_in()`、`get_current_user_id()`）？需在实现前确认。
3. 当前项目是否有 OPcache？生产环境建议开启以提升翻译文件加载性能。
4. 是否需要支持繁体中文（zh-TW）或其他语言？目前方案按 `en` / `zh` 设计，扩展成本低。

---

*文档生成时间：2026-07-27*
*方案状态：待用户审批*
