<?php
/**
 * Common Authentication - Login Endpoint
 * Location: C:\xampp\htdocs\mediconnect_db\auth\login.php
 */
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and Password are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, email, password_hash, role, full_name, status FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['status'] !== 'active') {
            echo json_encode(['status' => 'error', 'message' => 'Account is pending or suspended by Administrator.']);
            exit;
        }

        // Establish secure session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];

        // Record audit trail
        $auditStmt = $pdo->prepare("INSERT INTO system_audit_logs (actor_user_id, event_type, ip_address, details) VALUES (:uid, 'AUTH_SUCCESS', :ip, 'User successfully logged in.')");
        $auditStmt->execute([
            'uid' => $user['user_id'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'redirect' => '../dashboard/' . $user['role'] . '.php',
            'user' => [
                'id' => $user['user_id'],
                'role' => $user['role'],
                'name' => $user['full_name']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>