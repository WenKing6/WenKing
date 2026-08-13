<?php
/**
 * License Management API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/License.php';
require_once __DIR__ . '/../includes/models/User.php';

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

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$license = new License();

switch ($action) {
    case 'list':
        $items = $license->getAll();
        $stats = $license->getStats();
        echo json_encode(['success' => true, 'data' => $items, 'stats' => $stats]);
        break;

    case 'create':
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
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            exit;
        }

        $result = $license->activate($id);
        echo json_encode($result);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            exit;
        }

        $result = $license->delete($id);
        echo json_encode($result);
        break;

    case 'batch_delete':
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (!is_array($ids) || empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'No license IDs provided']);
            exit;
        }

        $result = $license->deleteBatch($ids);
        echo json_encode($result);
        break;

    case 'get_by_product_user':
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
        requireRole($currentUser, 'admin');
        $userId = (int)($_POST['user_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $durationDays = (int)($_POST['duration_days'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($userId <= 0 || $productId <= 0 || $durationDays <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        // 配额只能给经理角色
        $target = $userModel->findById($userId);
        if (!$target || $target['role'] !== 'manager') {
            echo json_encode(['success' => false, 'message' => 'Quota can only be granted to a Manager']);
            exit;
        }

        echo json_encode($license->createAllocation($userId, $productId, $durationDays, $quantity));
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
        // 经理领取钥匙：身份取自 Session，不接受外部传入 user_id（防篡改）
        if (!$currentUser || $currentUser['role'] !== 'manager') {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        $productId = (int)($_POST['product_id'] ?? 0);
        $durationDays = (int)($_POST['duration_days'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        if ($productId <= 0 || $durationDays <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Product, duration and quantity are required']);
            exit;
        }
        echo json_encode($license->claimKeys($currentUserId, $productId, $durationDays, $quantity));
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
