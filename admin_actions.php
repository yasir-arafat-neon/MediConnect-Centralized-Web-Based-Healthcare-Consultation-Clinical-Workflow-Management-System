<?php
/**
 * User 3 (Hospital Admin) Specific Actions Endpoint
 * Location: C:\xampp\htdocs\mediconnect_db\api\admin_actions.php
 */
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized Admin Access']));
}

$action = $_GET['action'] ?? '';

switch ($action) {
    // Feature 1: Doctor Credential Verification & Approval
    case 'verify_doctor':
        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $approve  = (int)($_POST['is_approved'] ?? 1);

        $pdo->beginTransaction();
        // Update doctor profile verified flag
        $stmt1 = $pdo->prepare("UPDATE doctor_profiles SET is_verified = :app WHERE doctor_id = :did");
        $stmt1->execute(['app' => $approve, 'did' => $doctorId]);

        // Activate user account
        $stmt2 = $pdo->prepare("UPDATE users u JOIN doctor_profiles dp ON u.user_id = dp.user_id SET u.status = :st WHERE dp.doctor_id = :did");
        $stmt2->execute(['st' => ($approve ? 'active' : 'suspended'), 'did' => $doctorId]);

        // Record audit
        $audit = $pdo->prepare("INSERT INTO system_audit_logs (actor_user_id, event_type, ip_address, details) VALUES (:uid, 'DOCTOR_VERIFICATION', :ip, :det)");
        $audit->execute([
            'uid' => $_SESSION['user_id'],
            'ip'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'det' => "Doctor ID $doctorId verification status set to $approve"
        ]);
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Doctor status updated and logged.']);
        break;

    // Feature 2: Department & Service Tariff Management
    case 'add_department':
        $name = trim($_POST['dept_name'] ?? '');
        $code = trim($_POST['dept_code'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $fee  = (float)($_POST['consultation_fee'] ?? 50.00);

        $stmt = $pdo->prepare("INSERT INTO departments (dept_name, dept_code, description, consultation_fee) VALUES (:nm, :cd, :ds, :fe)");
        $stmt->execute(['nm' => $name, 'cd' => $code, 'ds' => $desc, 'fe' => $fee]);

        echo json_encode(['status' => 'success', 'dept_id' => $pdo->lastInsertId(), 'message' => 'Department created successfully!']);
        break;

    // Feature 3: System Audit Logs & Operational Analytics
    case 'get_audit_telemetry':
        $logs = $pdo->query("SELECT l.*, u.full_name, u.role FROM system_audit_logs l LEFT JOIN users u ON l.actor_user_id = u.user_id ORDER BY l.log_id DESC LIMIT 50")->fetchAll();
        $apptStats = $pdo->query("SELECT status, COUNT(*) AS count FROM appointments GROUP BY status")->fetchAll();

        echo json_encode([
            'status' => 'success',
            'audit_logs' => $logs,
            'appointment_metrics' => $apptStats
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>