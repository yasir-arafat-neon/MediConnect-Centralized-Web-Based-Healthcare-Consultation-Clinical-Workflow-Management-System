<?php
/**
 * User 3 (Admin) Dashboard View
 * Location: C:\xampp\htdocs\mediconnect_db\dashboard\admin.php
 */
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$pendingDocs = $pdo->query("SELECT dp.*, u.full_name, u.email, d.dept_name FROM doctor_profiles dp JOIN users u ON dp.user_id = u.user_id JOIN departments d ON dp.dept_id = d.dept_id WHERE dp.is_verified = 0")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();
$auditLogs = $pdo->query("SELECT l.*, u.full_name FROM system_audit_logs l LEFT JOIN users u ON l.actor_user_id = u.user_id ORDER BY l.log_id DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hospital Administration - MediConnect: Centralized Web-Based Healthcare Consultation & Clinical Workflow Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
  <nav class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-sm">U3</span>
      <div>
        <div class="text-xs text-slate-400">Hospital Administration</div>
        <div class="text-sm font-semibold text-slate-200"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
      </div>
    </div>
    <div class="flex items-center gap-4 text-xs">
      <span class="px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-400 border border-purple-500/20">Superuser</span>
      <a href="../auth/logout.php" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-rose-400 transition">Logout</a>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Feature 1: Doctor Verification -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
        <h3 class="text-purple-400 font-bold text-xs uppercase tracking-wider">Feature 1: Doctor Approvals</h3>
        <div class="space-y-3 text-xs">
          <?php if (empty($pendingDocs)): ?>
            <div class="text-slate-500 py-6 text-center">All doctor credentials verified.</div>
          <?php else: ?>
            <?php foreach ($pendingDocs as $doc): ?>
              <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                <div class="font-semibold text-slate-200"><?php echo htmlspecialchars($doc['full_name']); ?></div>
                <div class="text-[11px] text-slate-400">License: <?php echo htmlspecialchars($doc['license_number']); ?> (<?php echo htmlspecialchars($doc['dept_name']); ?>)</div>
                <form action="../api/admin_actions.php?action=verify_doctor" method="POST" class="flex gap-2">
                  <input type="hidden" name="doctor_id" value="<?php echo $doc['doctor_id']; ?>">
                  <button type="submit" name="is_approved" value="1" class="flex-1 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-[11px]">Approve</button>
                  <button type="submit" name="is_approved" value="0" class="flex-1 py-1 rounded bg-rose-600 hover:bg-rose-500 text-white font-medium text-[11px]">Reject</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Feature 2: Department Tariff Manager -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
        <h3 class="text-purple-400 font-bold text-xs uppercase tracking-wider">Feature 2: Department Tariffs</h3>
        <form action="../api/admin_actions.php?action=add_department" method="POST" class="space-y-3 text-xs">
          <div>
            <label class="block text-slate-400 mb-1">Department Name</label>
            <input type="text" name="dept_name" required placeholder="e.g. Dermatology" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-slate-400 mb-1">Code</label>
              <input type="text" name="dept_code" required placeholder="DERM-01" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200">
            </div>
            <div>
              <label class="block text-slate-400 mb-1">Fee ($)</label>
              <input type="number" name="consultation_fee" value="65" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-200">
            </div>
          </div>
          <button type="submit" class="w-full py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-semibold transition">
            Add Department
          </button>
        </form>
      </div>

      <!-- Feature 3: Live System Audit Logs -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
        <h3 class="text-purple-400 font-bold text-xs uppercase tracking-wider">Feature 3: System Audit Logs</h3>
        <div class="space-y-2 text-xs">
          <?php foreach ($auditLogs as $log): ?>
            <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 space-y-1">
              <div class="flex justify-between text-[10px] text-slate-500">
                <span class="text-purple-400 font-mono"><?php echo htmlspecialchars($log['event_type']); ?></span>
                <span><?php echo substr($log['timestamp'], 11, 8); ?></span>
              </div>
              <div class="text-slate-300 text-[11px]"><?php echo htmlspecialchars($log['details']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </main>
</body>
</html>