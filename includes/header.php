<?php
/**
 * 页面头部模板
 * 包含HTML头部、CSS引用和Tailwind配置
 */
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE : META_TITLE; ?></title>
    <meta name="description" content="<?php echo META_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo META_KEYWORDS; ?>">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Three.js for GridScan Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/animations.css">

    <!-- Tailwind Config Extension -->
    <script>
        window.tailwind = window.tailwind || {};
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-primary': '#0a0a0f',
                        'bg-secondary': '#12121a',
                        'bg-tertiary': '#1a1a24',
                        'accent-purple': '#8b5cf6',
                        'accent-blue': '#3b82f6',
                        'accent-cyan': '#06b6d4',
                        'status-online': '#10b981',
                        'status-updating': '#f59e0b',
                        'status-dev': '#6366f1',
                    },
                    fontFamily: {
                        'primary': ['Inter', 'sans-serif'],
                        'display': ['Rajdhani', 'sans-serif'],
                    },
                    fontSize: {
                        'hero': ['clamp(3rem, 8vw, 6rem)', { lineHeight: '1.1' }],
                        'h1': ['clamp(2.5rem, 6vw, 4rem)', { lineHeight: '1.2' }],
                        'h2': ['clamp(2rem, 5vw, 3rem)', { lineHeight: '1.2' }],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-bg-primary text-white font-primary antialiased">
