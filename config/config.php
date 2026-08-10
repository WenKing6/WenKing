<?php
/**
 * Atlas Menu 全局配置文件
 */

// 站点基本信息
define('SITE_NAME', 'WenKing');
define('SITE_URL', 'http://localhost:8000');
define('SITE_DESCRIPTION', 'The most advanced game mod menu solution');
define('SITE_VERSION', '1.0.0');

// SEO配置
define('META_TITLE', 'WenKing - Premium Game Mod Menu');
define('META_KEYWORDS', 'game mod menu, game enhancement, mod menu');
define('META_DESCRIPTION', 'WenKing provides the most advanced and secure game mod menu solutions with undetected features and 24/7 support.');

// 社交媒体链接
define('DISCORD_URL', '#');
define('TELEGRAM_URL', '#');

// 时区设置
date_default_timezone_set('UTC');

// 错误报告（开发环境）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 引入数据库配置
require_once __DIR__ . '/database.php';

// 引入国际化支持
require_once __DIR__ . '/../includes/I18n.php';
I18n::getInstance();
