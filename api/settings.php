<?php
/**
 * 网站设置 API 接口
 * 支持：读取设置、修改网站名称、上传网站图标
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/SiteSetting.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$settings = new SiteSetting();

/**
 * 站点名称校验与过滤
 * - 去除首尾空格
 * - 过滤危险/非法特殊字符（<>{}|`" 反斜杠等）
 * - 限制长度不超过 50
 */
function validateSiteName(string $name): string {
    $name = trim($name);
    $name = preg_replace('/[<>{}|`"\\\\\x00-\x1F\x7F]/u', '', $name);
    return mb_substr($name, 0, 50);
}

switch ($action) {
    case 'get':
        echo json_encode(['success' => true, 'data' => $settings->getAll()]);
        break;

    case 'save_name':
        $name = validateSiteName($_POST['site_name'] ?? '');
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Site name cannot be empty.']);
            exit;
        }
        $settings->set('site_name', $name);
        echo json_encode(['success' => true, 'site_name' => $name]);
        break;

    case 'upload_icon':
        if (empty($_FILES['icon']) || $_FILES['icon']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload failed.']);
            exit;
        }

        $file = $_FILES['icon'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'Image is too large. Maximum size is 2MB.']);
            exit;
        }

        $allowedExt = ['png', 'jpg', 'jpeg', 'svg'];
        $allowedMime = ['image/png', 'image/jpeg', 'image/svg+xml'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: PNG, JPG, SVG.']);
            exit;
        }

        // 服务端 MIME 校验（防伪造扩展名）
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, $allowedMime, true)) {
            echo json_encode(['success' => false, 'message' => 'File content does not match an allowed image type.']);
            exit;
        }

        // SVG 安全扫描：拒绝包含脚本 / 事件处理器的文件（防存储型 XSS）
        if ($ext === 'svg' || $mime === 'image/svg+xml') {
            $svgContent = (string)file_get_contents($file['tmp_name']);
            if (preg_match('/<\s*(script|foreignObject)\b|on\w+\s*=/i', $svgContent)) {
                echo json_encode(['success' => false, 'message' => 'SVG contains unsafe content (script or event handlers).']);
                exit;
            }
        }

        $uploadDir = __DIR__ . '/../assets/uploads/site/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            echo json_encode(['success' => false, 'message' => 'Upload directory is not writable.']);
            exit;
        }

        $newName = 'site-icon-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $uploadDir . $newName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save the uploaded file.']);
            exit;
        }

        $publicPath = '/assets/uploads/site/' . $newName;

        // 清理旧的网站图标文件（避免堆积）
        $oldPath = $settings->get('site_icon');
        if ($oldPath && $oldPath !== $publicPath && strpos($oldPath, '/assets/uploads/site/') === 0) {
            $oldFile = __DIR__ . '/..' . $oldPath;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $settings->set('site_icon', $publicPath);
        echo json_encode(['success' => true, 'site_icon' => $publicPath]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
