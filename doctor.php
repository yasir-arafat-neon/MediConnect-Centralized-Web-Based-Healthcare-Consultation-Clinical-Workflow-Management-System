<?php
/**
 * User 2 (Doctor) Dashboard View
 * Location: C:\xampp\htdocs\mediconnect_db\dashboard\doctor.php
 */
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT dp.*, d.dept_name, u.full_name FROM doctor_profiles dp JOIN users u ON dp.user_id = u.user_id JOIN departments d ON dp.dept_id = d.dept_id WHERE dp.user_id = :uid");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$doctor = $stmt->fetch();
$doctorId = $doctor ? $doctor['doctor_id'] : 0;

// Fetch assigned appointments
$apptStmt = $pdo->prepare("SELECT a.*, u.full_name AS patient_name, u.phone AS patient_phone FROM appointments a JOIN users u ON a.patient_user_id = u.user_id WHERE a.doctor_id = :did ORDER BY a.appointment_date ASC");
$apptStmt->execute(['did' => $doctorId]);
$appointments = $apptStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Physician Workplace - MediConnect: Centralized Web-Based Healthcare Consultation & Clinical Workflow Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
  <nav class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-sm">U2</span>
      <div>
        <div class="text-xs text-slate-400">Doctor Practice Dashboard</div>
        <div class="text-sm font-semibold text-slate-200"><?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo htmlspecialchars($doctor['dept_name'] ?? 'General'); ?>)</div>
      </div>
    </div>
    <div class="flex items-center gap-4 text-xs">
      <span class="px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">License: <?php echo htmlspecialchars($doctor['license_number'] ?? 'VERIFIED'); ?></span>
      <a href="../auth/logout.php" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-rose-400 transition">Logout</a>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Feature 1: Issue Digital E-Prescription -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
        <h3 class="text-blue-400 font-bold text-xs uppercase tracking-wider">Feature 2: Digital E-Prescription Generator</h3>
        <form action="../api/doctor_actions.php?action=issue_prescription" method="POST" class="space-y-3 text-xs">
          <div>
            <label class="block text-slate-400 mb-1">Target Patient Consultation</label>
            <select name="appointment_id" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200">
              <?php foreach ($appointments as $a): ?>
                <option value="<?php echo $a['appointment_id']; ?>">
                  <?php echo htmlspecialchars($a['token_number']) . ' - ' . htmlspecialchars($a['patient_name']) . ' (' . $a['appointment_date'] . ')'; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Clinical Diagnosis</label>
            <input type="text" name="diagnosis" required placeholder="e.g. Mild Hypertension (Stage 1)" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Medications & Frequency (Rx)</label>
            <textarea name="medications_json" rows="2" placeholder="e.g. Amlodipine 5mg OD x 30 days, Aspirin 75mg post lunch" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200"></textarea>
          </div>
          <button type="submit" class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold transition">
            Sign & Archive E-Prescription
          </button>
        </form>
      </div>

      <!-- Feature 2: Patient Consultation Queue -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
        <h3 class="text-blue-400 font-bold text-xs uppercase tracking-wider">Feature 3: Today's Consultation Queue</h3>
        <div class="space-y-2.5 text-xs">
          <?php if (empty($appointments)): ?>
            <div class="text-slate-500 py-6 text-center">No patients queued for today.</div>
          <?php else: ?>
            <?php foreach ($appointments as $appt): ?>
              <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                <div class="flex justify-between font-medium">
                  <span class="text-slate-200"><?php echo htmlspecialchars($appt['patient_name']); ?></span>
                  <span class="font-mono text-blue-400"><?php echo htmlspecialchars($appt['token_number']); ?></span>
                </div>
                <div class="text-slate-400 text-[11px]">Phone: <?php echo htmlspecialchars($appt['patient_phone']); ?> &bull; Date: <?php echo $appt['appointment_date']; ?></div>
                <div class="text-slate-300 text-[11px] italic">"<?php echo htmlspecialchars($appt['symptoms']); ?>"</div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</body>
</html>