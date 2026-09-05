<?php
/**
 * Landing and Authentication Portal
 * Location: C:\xampp\htdocs\mediconnect_db\index.php
 */
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header('Location: dashboard/' . $_SESSION['role'] . '.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediConnect: Centralized Web-Based Healthcare Consultation & Clinical Workflow Management System - Healthcare Consultation Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col items-center justify-center p-4">
  <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl space-y-6">
    <div class="text-center space-y-2">
      <div class="inline-flex p-3 bg-blue-500/10 text-blue-400 rounded-2xl mb-1 border border-blue-500/20">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-100">MediConnect: Centralized Web-Based Healthcare Consultation & Clinical Workflow Management System</h1>
      <p class="text-xs text-slate-400">Secure 3-Tier PHP / MySQL Healthcare System</p>
    </div>

    <!-- Quick Demo Logins -->
    <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 space-y-2">
      <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Quick Fill Test Credentials:</div>
      <div class="grid grid-cols-3 gap-1.5 text-xs">
        <button onclick="fillLogin('sarah.patient@example.com')" class="p-2 rounded bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 hover:bg-emerald-500/20 text-center cursor-pointer transition">
          <div class="font-bold">Patient</div>
          <div class="text-[10px] text-emerald-400/80">User 1</div>
        </button>
        <button onclick="fillLogin('dr.robert.chen@hospital.org')" class="p-2 rounded bg-blue-500/10 text-blue-300 border border-blue-500/20 hover:bg-blue-500/20 text-center cursor-pointer transition">
          <div class="font-bold">Doctor</div>
          <div class="text-[10px] text-blue-400/80">User 2</div>
        </button>
        <button onclick="fillLogin('admin@mediconnect.local')" class="p-2 rounded bg-purple-500/10 text-purple-300 border border-purple-500/20 hover:bg-purple-500/20 text-center cursor-pointer transition">
          <div class="font-bold">Admin</div>
          <div class="text-[10px] text-purple-400/80">User 3</div>
        </button>
      </div>
      <div class="text-[10px] text-slate-500 text-center">Password for all test accounts: <code class="text-slate-300">Password123#</code></div>
    </div>

    <!-- Login Form -->
    <form id="loginForm" class="space-y-4 text-xs" onsubmit="handleLogin(event)">
      <div>
        <label class="block text-slate-400 mb-1 font-medium">Email Address</label>
        <input type="email" id="loginEmail" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-100 focus:outline-none focus:border-blue-500" placeholder="user@example.com">
      </div>
      <div>
        <label class="block text-slate-400 mb-1 font-medium">Password</label>
        <input type="password" id="loginPassword" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-slate-100 focus:outline-none focus:border-blue-500" value="Password123#">
      </div>
      <div id="loginMsg" class="hidden text-xs p-2.5 rounded-lg"></div>
      <button type="submit" id="loginBtn" class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold transition cursor-pointer">
        Sign In to Portal
      </button>
    </form>
  </div>

  <script>
    function fillLogin(email) {
      document.getElementById('loginEmail').value = email;
      document.getElementById('loginPassword').value = 'Password123#';
    }

    async function handleLogin(e) {
      e.preventDefault();
      const email = document.getElementById('loginEmail').value;
      const password = document.getElementById('loginPassword').value;
      const msgDiv = document.getElementById('loginMsg');
      const btn = document.getElementById('loginBtn');

      btn.innerText = 'Verifying...';
      btn.disabled = true;

      const formData = new FormData();
      formData.append('email', email);
      formData.append('password', password);

      try {
        const res = await fetch('auth/login.php', { method: 'POST', body: formData });
        const data = await res.json();
        msgDiv.classList.remove('hidden', 'bg-rose-500/20', 'text-rose-300', 'bg-emerald-500/20', 'text-emerald-300');

        if (data.status === 'success') {
          msgDiv.classList.add('bg-emerald-500/20', 'text-emerald-300');
          msgDiv.innerText = data.message + ' Redirecting...';
          setTimeout(() => { window.location.href = data.redirect; }, 800);
        } else {
          msgDiv.classList.add('bg-rose-500/20', 'text-rose-300');
          msgDiv.innerText = data.message;
          btn.innerText = 'Sign In to Portal';
          btn.disabled = false;
        }
      } catch (err) {
        msgDiv.classList.remove('hidden');
        msgDiv.classList.add('bg-rose-500/20', 'text-rose-300');
        msgDiv.innerText = 'Connection error. Make sure Apache & MySQL are running in XAMPP.';
        btn.innerText = 'Sign In to Portal';
        btn.disabled = false;
      }
    }
  </script>
</body>
</html>