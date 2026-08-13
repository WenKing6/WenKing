<?php
/**
 * FAQ 条目管理 API
 * 支持：list / create / update / delete / toggle
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/FaqItem.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$faq = new FaqItem();

switch ($action) {
    case 'list':
        echo json_encode(['success' => true, 'data' => $faq->getAll()]);
        break;

    case 'create':
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        if ($question === '') {
            echo json_encode(['success' => false, 'message' => 'Question cannot be empty.']);
            exit;
        }
        if ($answer === '') {
            echo json_encode(['success' => false, 'message' => 'Answer cannot be empty.']);
            exit;
        }
        $result = $faq->create([
            'question'   => mb_substr($question, 0, 255),
            'answer'     => $answer,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_visible' => (int)($_POST['is_visible'] ?? 1),
        ]);
        echo json_encode(['success' => true, 'id' => $result['id']]);
        break;

    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid FAQ item.']);
            exit;
        }
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        if ($question === '') {
            echo json_encode(['success' => false, 'message' => 'Question cannot be empty.']);
            exit;
        }
        if ($answer === '') {
            echo json_encode(['success' => false, 'message' => 'Answer cannot be empty.']);
            exit;
        }
        $faq->update($id, [
            'question'   => mb_substr($question, 0, 255),
            'answer'     => $answer,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_visible' => (int)($_POST['is_visible'] ?? 1),
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid FAQ item.']);
            exit;
        }
        $faq->delete($id);
        echo json_encode(['success' => true]);
        break;

    case 'toggle':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid FAQ item.']);
            exit;
        }
        $visible = $faq->toggleVisibility($id);
        if ($visible === null) {
            echo json_encode(['success' => false, 'message' => 'FAQ item not found.']);
            exit;
        }
        echo json_encode(['success' => true, 'is_visible' => $visible]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}
