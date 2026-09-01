<?php
/**
 * License Management API
 */

header('Content-Type: application/json; charset=utf-8');
// API 响应禁止缓存（防止浏览器用旧缓存掩盖服务端行为变化）
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/License.php';
require_once __DIR__ . '/../includes/models/User.php';
require_once __DIR__ . '/../includes/services/LicenseModule.php';

// 当前登录用户（Session + 数据库角色校验，防止 URL 参数篡改）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userModel = new User();
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUser = $currentUserId > 0 ? $userModel->findById($currentUserId) : null;

/**
 * 角色校验：不满足指定角色则直接返回 403
 */
function requireRole(?array $currentUser, string $role): void {
    if (!$currentUser || $currentUser['role'] !== $role) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit;
    }
}

/**
 * 登录校验：未登录直接拒绝（所有 action 的最低要求，防止游客越权操作许可证数据）
 */
function requireLogin(?array $currentUser): void {
    if (!$currentUser) {
        echo json_encode(['success' => false, 'message' => 'Please sign in']);
        exit;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$license = new License();

switch ($action) {
    case 'list':
        // 钥匙全表含敏感数据，仅管理员可拉取
        requireRole($currentUser, 'admin');
        $items = $license->getAll();
        $stats = $license->getStats();
        echo json_encode(['success' => true, 'data' => $items, 'stats' => $stats]);
        break;

    case 'create':
        // 生成新钥匙入库：仅管理员
        requireRole($currentUser, 'admin');
        $data = [
            'license_key' => trim($_POST['license_key'] ?? ''),
            'product_id' => (int)($_POST['product_id'] ?? 0),
            'user_id' => !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null,
            'duration_days' => (int)($_POST['duration_days'] ?? 30),
            'status' => $_POST['status'] ?? 'unused',
            'note' => trim($_POST['note'] ?? ''),
        ];

        if (empty($data['license_key'])) {
            echo json_encode(['success' => false, 'message' => 'License key is required']);
            exit;
        }

        if (empty($data['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Product is required']);
            exit;
        }

        $result = $license->create($data);
        echo json_encode($result);
        break;

    case 'batch_create':
        // 批量生成新钥匙入库：仅管理员
        requireRole($currentUser, 'admin');
        // Batch create licenses
        $licensesData = json_decode($_POST['licenses'] ?? '[]', true);
        
        if (empty($licensesData) || !is_array($licensesData)) {
            echo json_encode(['success' => false, 'message' => 'No license data provided']);
            exit;
        }

        // Validate each license entry
        $validated = [];
        foreach ($licensesData as $item) {
            if (empty($item['license_key'])) {
                echo json_encode(['success' => false, 'message' => 'License key is required for all items']);
                exit;
            }
            if (empty($item['product_id'])) {
                echo json_encode(['success' => false, 'message' => 'Product is required for all items']);
                exit;
            }
            
            $validated[] = [
                'license_key' => trim($item['license_key']),
                'product_id' => (int)$item['product_id'],
                'user_id' => !empty($item['user_id']) ? (int)$item['user_id'] : null,
                'duration_days' => (int)($item['duration_days'] ?? 30),
                'status' => $item['status'] ?? 'unused',
                'note' => trim($item['note'] ?? ''),
            ];
        }

        $result = $license->createBatch($validated);
        echo json_encode($result);
        break;

    case 'update':
        requireRole($currentUser, 'admin');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            exit;
        }

        $data = [
            'product_id' => (int)($_POST['product_id'] ?? 0),
            'user_id' => !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null,
            'duration_days' => (int)($_POST['duration_days'] ?? 30),
            'status' => $_POST['status'] ?? 'unused',
            'note' => trim($_POST['note'] ?? ''),
        ];

        $result = $license->update($id, $data);
        echo json_encode($result);
        break;

    case 'activate':
        requireRole($currentUser, 'admin');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            exit;
        }

        $result = $license->activate($id);
        echo json_encode($result);
        break;

    case 'delete':
        requireRole($currentUser, 'admin');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            exit;
        }

        $result = $license->delete($id);
        echo json_encode($result);
        break;

    case 'batch_delete':
        requireRole($currentUser, 'admin');
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (!is_array($ids) || empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'No license IDs provided']);
            exit;
        }

        $result = $license->deleteBatch($ids);
        echo json_encode($result);
        break;

    case 'get_by_product_user':
        requireLogin($currentUser);
        $productId = (int)($_GET['product_id'] ?? 0);
        $userId = (int)($_GET['user_id'] ?? 0);
        
        if ($productId <= 0 || $userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Product ID and User ID are required']);
            exit;
        }

        $items = $license->getByProductAndUser($productId, $userId);
        echo json_encode(['success' => true, 'data' => $items]);
        break;

    case 'list_allocations':
        // 管理员看全部配额，经理只看自己的
        if (!$currentUser) {
            echo json_encode(['success' => false, 'message' => 'Please sign in']);
            exit;
        }
        if ($currentUser['role'] === 'admin') {
            $items = $license->getAllocations();
        } elseif ($currentUser['role'] === 'manager') {
            $items = $license->getAllocations($currentUserId);
        } else {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $items]);
        break;

    case 'create_allocation':
        requireLogin($currentUser);
        $userId = (int)($_POST['user_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $durationDays = (int)($_POST['duration_days'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        // 权限与划转规则统一由 LicenseModule 权限矩阵决定：
        // admin → manager/reseller（直接授权）；manager → reseller（从自己剩余配额划转）
        $licenseModule = new LicenseModule();
        echo json_encode($licenseModule->grantQuota(
            ['id' => $currentUserId, 'role' => $currentUser['role']],
            $userId,
            $productId,
            $durationDays,
            $quantity
        ));
        break;

    case 'delete_allocation':
        requireRole($currentUser, 'admin');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid allocation ID']);
            exit;
        }
        echo json_encode($license->deleteAllocation($id));
        break;

    case 'inventory_count':
        // 查询某产品 + 某时长的库存可用钥匙数（登录即可查询，用于弹窗实时显示）
        if (!$currentUser) {
            echo json_encode(['success' => false, 'message' => 'Please sign in']);
            exit;
        }
        $productId = (int)($_GET['product_id'] ?? 0);
        $durationDays = (int)($_GET['duration_days'] ?? 0);
        if ($productId <= 0 || $durationDays <= 0) {
            echo json_encode(['success' => false, 'message' => 'Product and duration are required']);
            exit;
        }
        $count = $license->getInventoryCount($productId, $durationDays);
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    case 'claim_keys':
        // 领取钥匙（Create License）：身份取自 Session，不接受外部传入 user_id（防篡改）；
        // 可领取角色（manager/reseller）由 LicenseModule 权限矩阵决定
        requireLogin($currentUser);
        $productId = (int)($_POST['product_id'] ?? 0);
        $durationDays = (int)($_POST['duration_days'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        $licenseModule = new LicenseModule();
        echo json_encode($licenseModule->claim(
            ['id' => $currentUserId, 'role' => $currentUser['role']],
            $productId,
            $durationDays,
            $quantity
        ));
        break;

    case 'recycle':
        // 管理员回收经理已领取但未激活的钥匙
        requireRole($currentUser, 'admin');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            exit;
        }
        echo json_encode($license->recycle($id));
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}
