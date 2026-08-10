<?php
/**
 * 认证 API 接口
 * 处理登录、注册、忘记密码等请求
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/models/User.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user   = new User();

switch ($action) {
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => '请填写用户名和密码']);
            exit;
        }

        $result = $user->login($username, $password);
        if ($result['success']) {
            session_start();
            $_SESSION['user_id']   = $result['user']['id'];
            $_SESSION['username']  = $result['user']['username'];
            $_SESSION['email']     = $result['user']['email'];
            $_SESSION['role']      = $result['user']['role'];
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        }
        echo json_encode($result);
        break;

    case 'register':
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => '请填写所有字段']);
            exit;
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            echo json_encode(['success' => false, 'message' => '用户名长度需为 3-20 个字符']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '请输入有效的邮箱地址']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => '密码长度至少为 6 个字符']);
            exit;
        }

        $result = $user->register($username, $email, $password);
        echo json_encode($result);
        break;

    case 'check_email':
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => '请输入邮箱地址']);
            exit;
        }

        $found = $user->findByEmail($email);
        if ($found) {
            // 生成验证码并存入 session
            session_start();
            $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['reset_code']      = $code;
            $_SESSION['reset_email']     = $email;
            $_SESSION['reset_user_id']   = $found['id'];
            $_SESSION['reset_expires']   = time() + 600; // 10 分钟有效

            echo json_encode([
                'success' => true,
                'message' => '验证码已发送',
                'code'    => $code, // 开发环境返回验证码，生产环境应通过邮件发送
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '该邮箱未注册']);
        }
        break;

    case 'reset_password':
        session_start();
        $code         = trim($_POST['code'] ?? '');
        $newPassword  = $_POST['new_password'] ?? '';

        if (empty($code) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => '请填写验证码和新密码']);
            exit;
        }

        if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_expires'])) {
            echo json_encode(['success' => false, 'message' => '请先获取验证码']);
            exit;
        }

        if (time() > $_SESSION['reset_expires']) {
            echo json_encode(['success' => false, 'message' => '验证码已过期，请重新获取']);
            exit;
        }

        if ($code !== $_SESSION['reset_code']) {
            echo json_encode(['success' => false, 'message' => '验证码错误']);
            exit;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => '密码长度至少为 6 个字符']);
            exit;
        }

        $success = $user->resetPassword($_SESSION['reset_user_id'], $newPassword);
        if ($success) {
            unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['reset_expires']);
            echo json_encode(['success' => true, 'message' => '密码重置成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '密码重置失败']);
        }
        break;

    case 'logout':
        session_start();
        session_destroy();
        echo json_encode(['success' => true, 'message' => '已退出登录']);
        break;

    case 'get_users':
        $users = $user->getAll();
        echo json_encode(['success' => true, 'data' => $users]);
        break;

    case 'update_user':
        $userId   = (int) ($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $status   = trim($_POST['status'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? '');

        if ($userId <= 0 || empty($username) || empty($email) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            break;
        }

        if (!in_array($status, ['active', 'inactive', 'banned'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            break;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            break;
        }

        $success = $user->update($userId, $username, $email, $status, $password, $role);
        echo json_encode(['success' => $success['success'], 'message' => $success['message']]);
        break;

    case 'update_user_status':
        $userId = (int) ($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($userId <= 0 || !in_array($status, ['active', 'inactive', 'banned'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            break;
        }
        $success = $user->updateStatus($userId, $status);
        echo json_encode(['success' => $success, 'message' => $success ? 'Status updated' : 'Update failed']);
        break;

    case 'delete_user':
        $userId = (int) ($_POST['id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            break;
        }
        $success = $user->delete($userId);
        echo json_encode(['success' => $success, 'message' => $success ? 'User deleted' : 'Delete failed']);
        break;

    case 'update_role':
        session_start();
        $operatorId = (int) ($_SESSION['user_id'] ?? 0);
        if ($operatorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            break;
        }

        // CSRF 校验
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            break;
        }

        $userId  = (int) ($_POST['user_id'] ?? 0);
        $newRole = trim($_POST['role'] ?? '');

        if ($userId <= 0 || empty($newRole)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            break;
        }

        $result = $user->updateRole($userId, $newRole, $operatorId);
        echo json_encode($result);
        break;

    case 'get_csrf_token':
        session_start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        echo json_encode(['success' => true, 'csrf_token' => $_SESSION['csrf_token']]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
        break;
}
