<?php
/**
 * User 1 (Patient) Dashboard View
 * Location: C:\xampp\htdocs\mediconnect_db\dashboard\patient.php
 */
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../index.php');
    exit;
}

$patientId = $_SESSION['user_id'];

// Fetch patient's appointments
$apptStmt = $pdo->prepare("SELECT a.*, u.full_name AS doctor_name, d.dept_name, d.consultation_fee 
                           FROM appointments a
                           JOIN doctor_profiles dp ON a.doctor_id = dp.doctor_id
                           JOIN users u ON dp.user_id = u.user_id
                           JOIN departments d ON dp.dept_id = d.dept_id
                           WHERE a.patient_user_id = :pid
                           ORDER BY a.appointment_date DESC");
$apptStmt->execute(['pid' => $patientId]);
$myAppointments = $apptStmt->fetchAll();

// Fetch doctors for booking dropdown
$docs = $pdo->query("SELECT dp.doctor_id, u.full_name, d.dept_name, dp.qualification 
                     FROM doctor_profiles dp 
                     JOIN users u ON dp.user_id = u.user_id 
                     JOIN departments d ON dp.dept_id = d.dept_id
                     WHERE dp.is_verified = 1")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Portal - MediConnect: Centralized Web-Based Healthcare Consultation & Clinical Workflow Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
  <nav class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">U1</span>
      <div>
        <div class="text-xs text-slate-400">Patient Dashboard</div>
        <div class="text-sm font-semibold text-slate-200"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
      </div>
    </div>
    <div class="flex items-center gap-4 text-xs">
      <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active Session</span>
      <a href="../auth/logout.php" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-rose-400 transition">Logout</a>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Feature 1: Real-Time Appointment Booking -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
        <h3 class="text-emerald-400 font-bold text-xs uppercase tracking-wider">Feature 1: Book Consultation</h3>
        <form action="../api/patient_actions.php?action=book_appointment" method="POST" class="space-y-3 text-xs">
          <div>
            <label class="block text-slate-400 mb-1">Select Physician & Specialty</label>
            <select name="doctor_id" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200">
              <?php foreach ($docs as $d): ?>
                <option value="<?php echo $d['doctor_id']; ?>"><?php echo htmlspecialchars($d['full_name']) . " (" . htmlspecialchars($d['dept_name']) . ")"; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-slate-400 mb-1">Date</label>
              <input type="date" name="appointment_date" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
            </div>
            <div>
              <label class="block text-slate-400 mb-1">Time Slot</label>
              <input type="text" name="appointment_time" value="10:30:00" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200">
            </div>
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Symptoms Summary</label>
            <textarea name="symptoms" rows="2" placeholder="Briefly describe your symptoms..." class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200"></textarea>
          </div>
          <button type="submit" class="w-full py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold transition">
            Confirm Booking & Generate Token
          </button>
        </form>
      </div>

      <!-- Feature 2: Digital Health Locker & Active Vouchers -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 md:col-span-2">
        <h3 class="text-emerald-400 font-bold text-xs uppercase tracking-wider">Feature 2: Health Locker & Appointments</h3>
        <div class="space-y-3 text-xs">
          <?php if (empty($myAppointments)): ?>
            <div class="text-slate-500 py-6 text-center">No appointments booked yet.</div>
          <?php else: ?>
            <?php foreach ($myAppointments as $appt): ?>
              <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-emerald-300"><?php echo htmlspecialchars($appt['token_number']); ?></span>
                    <span class="text-slate-300 font-medium"><?php echo htmlspecialchars($appt['doctor_name']); ?></span>
                    <span class="text-slate-500">&bull; <?php echo htmlspecialchars($appt['dept_name']); ?></span>
                  </div>
                  <div class="text-slate-400 text-[11px]">
                    Date: <?php echo htmlspecialchars($appt['appointment_date']); ?> at <?php echo htmlspecialchars($appt['appointment_time']); ?>
                  </div>
                  <?php if (!empty($appt['symptoms'])): ?>
                    <div class="text-slate-400 text-[11px]">Symptoms: <span class="italic"><?php echo htmlspecialchars($appt['symptoms']); ?></span></div>
                  <?php endif; ?>
                </div>
                <div class="text-right">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase <?php echo $appt['status'] === 'booked' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-blue-500/20 text-blue-400'; ?>">
                    <?php echo htmlspecialchars($appt['status']); ?>
                  </span>
                  <div class="text-[11px] text-slate-500 mt-1">Fee: $<?php echo htmlspecialchars($appt['consultation_fee']); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</body>
</html>