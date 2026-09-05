<?php
/**
 * User 2 (Doctor) Specific Actions Endpoint
 * Location: C:\xampp\htdocs\mediconnect_db\api\doctor_actions.php
 */
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$stmt = $pdo->prepare("SELECT doctor_id FROM doctor_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$docProfile = $stmt->fetch();
$doctorId = $docProfile ? $docProfile['doctor_id'] : 0;

$action = $_GET['action'] ?? '';

switch ($action) {
    // Feature 1: Set Weekly Availability Schedule
    case 'set_schedule':
        $day   = $_POST['day_of_week'] ?? 'Mon';
        $start = $_POST['start_time'] ?? '09:00:00';
        $end   = $_POST['end_time'] ?? '13:00:00';
        $quota = (int)($_POST['max_quota'] ?? 15);

        $stmt = $pdo->prepare("INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, max_quota, is_active) 
                               VALUES (:did, :day, :st, :et, :quota, 1)");
        $stmt->execute(['did' => $doctorId, 'day' => $day, 'st' => $start, 'et' => $end, 'quota' => $quota]);
        echo json_encode(['status' => 'success', 'message' => 'Schedule slot published successfully!']);
        break;

    // Feature 2: Generate Digital E-Prescription
    case 'issue_prescription':
        $appointmentId = (int)($_POST['appointment_id'] ?? 0);
        $diagnosis     = trim($_POST['diagnosis'] ?? '');
        $medsJson      = $_POST['medications_json'] ?? '[]';
        $dietNotes     = trim($_POST['dietary_notes'] ?? '');
        $labTests      = trim($_POST['lab_tests_ordered'] ?? '');

        // 1. Insert prescription
        $stmt = $pdo->prepare("INSERT INTO prescriptions (appointment_id, diagnosis, medications_json, dietary_notes, lab_tests_ordered) 
                               VALUES (:aid, :diag, :meds, :diet, :labs)");
        $stmt->execute(['aid' => $appointmentId, 'diag' => $diagnosis, 'meds' => $medsJson, 'diet' => $dietNotes, 'labs' => $labTests]);

        // 2. Mark appointment as completed
        $updateAppt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = :aid AND doctor_id = :did");
        $updateAppt->execute(['aid' => $appointmentId, 'did' => $doctorId]);

        echo json_encode(['status' => 'success', 'message' => 'E-Prescription issued and appointment completed.']);
        break;

    // Feature 3: Patient Medical History Chronology
    case 'get_patient_history':
        $patientId = (int)($_GET['patient_user_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT a.appointment_date, a.symptoms, p.diagnosis, p.medications_json, p.dietary_notes, p.lab_tests_ordered
                               FROM appointments a
                               LEFT JOIN prescriptions p ON a.appointment_id = p.appointment_id
                               WHERE a.patient_user_id = :pid AND a.status = 'completed'
                               ORDER BY a.appointment_date DESC");
        $stmt->execute(['pid' => $patientId]);
        $history = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'patient_id' => $patientId, 'history' => $history]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>