<?php
$pageTitle = 'Login';
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (is_logged_in()) { header('Location: /ssvias/dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login — SSVIAS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="toast-container" id="toastContainer"></div>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">🚔</div>
      <span>SSVIAS</span>
    </div>
    <h1 class="auth-title">Welcome Back</h1>
    <p class="auth-sub">Sign in to your account to continue</p>

    <form action="/ssvias/api/auth.php?action=login" method="POST" id="loginForm">
      <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-group">
          <span class="input-icon">✉</span>
          <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
        </div>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-group">
          <span class="input-icon">🔒</span>
          <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="loginBtn" style="margin-top:.5rem;">
        🔐 Sign In
      </button>
    </form>

    <div class="divider">OR</div>

    <div style="background:rgba(47,129,247,0.06);border:1px solid rgba(47,129,247,0.2);border-radius:8px;padding:1rem;font-size:.8rem;color:var(--text2);">
      <strong style="color:var(--text);">Demo Credentials:</strong><br>
      Admin: <code>admin@ssvias.cm</code> / <code>admin123</code><br>
      Officer: <code>officer@ssvias.cm</code> / <code>admin123</code><br>
      Owner: <code>john@example.cm</code> / <code>admin123</code>
    </div>

    <p class="auth-footer">
      Don't have an account? <a href="/ssvias/register.php">Create one here</a>
    </p>
  </div>
</div>

<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
  setLoading(document.getElementById('loginBtn'), true);
});
</script>
</body>
</html>
