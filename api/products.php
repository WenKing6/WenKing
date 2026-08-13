<?php
/**
 * 产品管理 API 接口
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/Product.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product = new Product();

switch ($action) {
    case 'list':
        $items = $product->getAll();
        echo json_encode(['success' => true, 'data' => $items]);
        break;

    case 'create':
        $data = [
            'name'         => trim($_POST['name'] ?? ''),
            'tagline'      => trim($_POST['tagline'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'status'       => $_POST['status'] ?? 'development',
            'image'        => trim($_POST['image'] ?? ''),
            'button_text'  => trim($_POST['button_text'] ?? 'Now Buy'),
            'button_link'  => trim($_POST['button_link'] ?? ''),
            'features'     => trim($_POST['features'] ?? ''),
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_visible'   => (int)($_POST['is_visible'] ?? 1),
        ];

        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => '产品名称不能为空']);
            exit;
        }

        $result = $product->create($data);
        echo json_encode($result);
        break;

    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '无效的产品 ID']);
            exit;
        }

        $data = [
            'name'         => trim($_POST['name'] ?? ''),
            'tagline'      => trim($_POST['tagline'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'status'       => $_POST['status'] ?? 'development',
            'image'        => trim($_POST['image'] ?? ''),
            'button_text'  => trim($_POST['button_text'] ?? 'Now Buy'),
            'button_link'  => trim($_POST['button_link'] ?? ''),
            'features'     => trim($_POST['features'] ?? ''),
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_visible'   => (int)($_POST['is_visible'] ?? 1),
        ];

        if (empty($data['name'])) {
            echo json_encode(['success' => false, 'message' => '产品名称不能为空']);
            exit;
        }

        $success = $product->update($id, $data);
        echo json_encode(['success' => $success]);
        break;

    case 'toggle_visibility':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '无效的产品 ID']);
            exit;
        }

        $isVisible = $product->toggleVisibility($id);
        if ($isVisible === null) {
            echo json_encode(['success' => false, 'message' => '产品不存在']);
            exit;
        }
        echo json_encode(['success' => true, 'is_visible' => $isVisible]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '无效的产品 ID']);
            exit;
        }

        $success = $product->delete($id);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
        break;
}
