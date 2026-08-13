<?php
/**
 * License Management API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/License.php';

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

    case 'create_allocation':
        $userId = (int)($_POST['user_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $durationDays = (int)($_POST['duration_days'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($userId <= 0 || $productId <= 0 || $durationDays <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'User, product, duration and quantity are required']);
            exit;
        }

        $result = $license->createAllocation($userId, $productId, $durationDays, $quantity);
        echo json_encode($result);
        break;

    case 'list_allocations':
        $items = $license->getAllocations();
        echo json_encode(['success' => true, 'data' => $items]);
        break;

    case 'delete_allocation':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid allocation ID']);
            exit;
        }

        $result = $license->deleteAllocation($id);
        echo json_encode($result);
        break;

    case 'get_quota':
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }

        $items = $license->getQuotaForUser($userId);
        echo json_encode(['success' => true, 'data' => $items]);
        break;

    case 'generate_keys':
        $userId = (int)($_POST['user_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $durationDays = (int)($_POST['duration_days'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($userId <= 0 || $productId <= 0 || $durationDays <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'User, product, duration and quantity are required']);
            exit;
        }

        $result = $license->generateKeys($userId, $productId, $durationDays, $quantity);
        echo json_encode($result);
        break;

    case 'recycle':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            exit;
        }

        $result = $license->recycle($id);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}
