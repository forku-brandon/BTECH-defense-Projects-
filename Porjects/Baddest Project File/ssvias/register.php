<?php
$pageTitle = 'Register';
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (is_logged_in()) { header('Location: /ssvias/dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register — SSVIAS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="toast-container" id="toastContainer"></div>

<div class="auth-page">
  <div class="auth-card" style="max-width:500px;">
    <div class="auth-logo">
      <div class="logo-icon">🚔</div>
      <span>SSVIAS</span>
    </div>
    <h1 class="auth-title">Create Account</h1>
    <p class="auth-sub">Join SSVIAS to protect your vehicles</p>

    <form action="/ssvias/api/auth.php?action=register" method="POST" id="regForm">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="John Nkemdirim" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" placeholder="+237600000000">
        </div>
      </div>
      <div class="form-group">
        <label for="role">Account Type</label>
        <select id="role" name="role">
          <option value="public">👤 Public User — Report sightings</option>
          <option value="owner">🚗 Vehicle Owner — Register &amp; report vehicles</option>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Min 6 characters" required minlength="6">
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
        </div>
      </div>

      <div style="background:var(--bg3);border-radius:8px;padding:.85rem 1rem;font-size:.8rem;color:var(--text2);margin-bottom:1rem;">
        🔒 Your data is encrypted and protected. We never share your information.
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="regBtn">
        📝 Create Account
      </button>
    </form>

    <p class="auth-footer">
      Already have an account? <a href="/ssvias/login.php">Sign in here</a>
    </p>
  </div>
</div>

<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script>
document.getElementById('regForm').addEventListener('submit', function(e) {
  const p = document.getElementById('password').value;
  const c = document.getElementById('confirm_password').value;
  if (p !== c) { e.preventDefault(); showToast('Passwords do not match!', 'error'); return; }
  setLoading(document.getElementById('regBtn'), true);
});
</script>
</body>
</html>
