<?php
/**
 * Common Authentication - User Registration Endpoint
 * Location: C:\xampp\htdocs\mediconnect_db\auth\register.php
 */
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$phone    = trim($_POST['phone'] ?? '');
$role     = $_POST['role'] ?? 'patient';
$password = $_POST['password'] ?? '';

// Validation
if (empty($fullName) || empty($email) || strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide valid details. Password must be at least 8 chars.']);
    exit;
}

if (!in_array($role, ['patient', 'doctor', 'admin'])) {
    $role = 'patient';
}

try {
    // Check if email already registered
    $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
    $checkStmt->execute(['email' => $email]);
    if ($checkStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'This email is already registered.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    // Doctors require admin approval before becoming active
    $status = ($role === 'doctor') ? 'pending' : 'active';

    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, full_name, phone, status) VALUES (:email, :hash, :role, :name, :phone, :status)");
    $stmt->execute([
        'email'  => $email,
        'hash'   => $hash,
        'role'   => $role,
        'name'   => $fullName,
        'phone'  => $phone,
        'status' => $status
    ]);

    $newUserId = $pdo->lastInsertId();

    // If doctor, initialize unverified profile
    if ($role === 'doctor') {
        $docStmt = $pdo->prepare("INSERT INTO doctor_profiles (user_id, dept_id, license_number, qualification, experience_years, is_verified) VALUES (:uid, 1, 'PENDING_VERIFY', 'General Practitioner', 0, 0)");
        $docStmt->execute(['uid' => $newUserId]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Registration complete. ' . ($role === 'doctor' ? 'Your profile is awaiting Admin license verification.' : 'You can now log in.')
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>