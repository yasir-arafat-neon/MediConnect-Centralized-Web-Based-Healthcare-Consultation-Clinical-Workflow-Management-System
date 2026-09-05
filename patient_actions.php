<?php
/**
 * User 1 (Patient) Specific Actions Endpoint
 * Location: C:\xampp\htdocs\mediconnect_db\api\patient_actions.php
 */
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

// Ensure user is logged in as Patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$patientId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    // Feature 1: Book Appointment
    case 'book_appointment':
        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $date     = $_POST['appointment_date'] ?? '';
        $time     = $_POST['appointment_time'] ?? '';
        $symptoms = trim($_POST['symptoms'] ?? '');

        if (!$doctorId || empty($date) || empty($time)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing booking fields.']);
            exit;
        }

        $token = 'TKN-' . strtoupper(substr(uniqid(), -6));
        $stmt = $pdo->prepare("INSERT INTO appointments (token_number, patient_user_id, doctor_id, appointment_date, appointment_time, status, symptoms) VALUES (:tkn, :pid, :did, :dt, :tm, 'booked', :sym)");
        $stmt->execute([
            'tkn' => $token, 'pid' => $patientId, 'did' => $doctorId,
            'dt'  => $date,  'tm'  => $time,      'sym' => $symptoms
        ]);

        echo json_encode(['status' => 'success', 'token' => $token, 'message' => 'Appointment confirmed successfully!']);
        break;

    // Feature 2: Personal Health Locker (Prescriptions)
    case 'my_prescriptions':
        $stmt = $pdo->prepare("SELECT p.*, a.appointment_date, u.full_name AS doctor_name, d.dept_name 
                               FROM prescriptions p 
                               JOIN appointments a ON p.appointment_id = a.appointment_id 
                               JOIN doctor_profiles dp ON a.doctor_id = dp.doctor_id
                               JOIN users u ON dp.user_id = u.user_id
                               JOIN departments d ON dp.dept_id = d.dept_id
                               WHERE a.patient_user_id = :pid
                               ORDER BY a.appointment_date DESC");
        $stmt->execute(['pid' => $patientId]);
        $list = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $list]);
        break;

    // Feature 3: Submit Doctor Rating
    case 'submit_review':
        $appointmentId = (int)($_POST['appointment_id'] ?? 0);
        $doctorId      = (int)($_POST['doctor_id'] ?? 0);
        $rating        = (int)($_POST['rating'] ?? 5);
        $comment       = trim($_POST['comment'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO doctor_reviews (appointment_id, patient_id, doctor_id, rating, comment) VALUES (:aid, :pid, :did, :rt, :cmt)");
        $stmt->execute(['aid' => $appointmentId, 'pid' => $patientId, 'did' => $doctorId, 'rt' => $rating, 'cmt' => $comment]);

        echo json_encode(['status' => 'success', 'message' => 'Feedback submitted successfully!']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action parameter']);
}
?>