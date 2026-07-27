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
