<?php
$pageTitle = 'Home';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get live stats
$totalVehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$stolenCount   = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='stolen'")->fetchColumn();
$recoveredCount= $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='recovered'")->fetchColumn();
$usersCount    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$sightingsCount= $pdo->query("SELECT COUNT(*) FROM sightings")->fetchColumn();

require_once 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge">🛡 Protecting Bamenda's Roads</div>
    <h1>Identify &amp; Report<br><span>Stolen Vehicles</span><br>Instantly</h1>
    <p>A community-powered platform connecting vehicle owners, citizens, and law enforcement to combat vehicle theft across Bamenda and Cameroon.</p>
    <div class="hero-actions">
      <a href="/ssvias/verify.php" class="btn btn-primary btn-lg">🔍 Verify a Vehicle</a>
      <a href="/ssvias/register.php" class="btn btn-outline btn-lg">📝 Get Started Free</a>
    </div>
    <div class="hero-stats">
      <div class="stat-item">
        <span class="stat-num" data-counter="<?= $totalVehicles ?>">0</span>
        <span class="stat-label">Vehicles Registered</span>
      </div>
      <div class="stat-item">
        <span class="stat-num" data-counter="<?= $stolenCount ?>" style="color:var(--danger);">0</span>
        <span class="stat-label">Active Stolen Reports</span>
      </div>
      <div class="stat-item">
        <span class="stat-num" data-counter="<?= $recoveredCount ?>" style="color:var(--success);">0</span>
        <span class="stat-label">Vehicles Recovered</span>
      </div>
      <div class="stat-item">
        <span class="stat-num" data-counter="<?= $sightingsCount ?>">0</span>
        <span class="stat-label">Sightings Reported</span>
      </div>
    </div>
  </div>
</section>

<!-- QUICK VERIFY -->
<section class="section" style="padding:3rem 0;">
  <div class="container">
    <div class="card" style="max-width:680px;margin:0 auto;background:linear-gradient(135deg,rgba(47,129,247,0.08),rgba(124,58,237,0.08));border-color:rgba(47,129,247,0.25);">
      <h2 class="card-title" style="font-size:1.3rem;margin-bottom:1rem;justify-content:center;">⚡ Quick Vehicle Check</h2>
      <p style="text-align:center;color:var(--text2);font-size:.9rem;margin-bottom:1.25rem;">Enter a plate number to instantly verify if a vehicle is reported stolen.</p>
      <form action="/ssvias/verify.php" method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <input type="text" name="plate" placeholder="e.g. NW-1234-A" style="flex:1;min-width:200px;text-transform:uppercase;font-family:'JetBrains Mono',monospace;letter-spacing:.08em;" required>
        <button type="submit" class="btn btn-primary" style="flex-shrink:0;">🔍 Check Now</button>
      </form>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" style="padding:3rem 0 4rem;">
  <div class="container">
    <h2 class="section-title">How It Works</h2>
    <p class="section-sub">Four simple steps to protect and recover your vehicle</p>
    <div class="steps">
      <div class="step-card">
        <div class="step-num">1</div>
        <h3>Register Your Vehicle</h3>
        <p>Create an account and add your vehicle details including plate number, VIN, and photos.</p>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <h3>Report if Stolen</h3>
        <p>If your vehicle is stolen, report it immediately with last known location and details.</p>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <h3>Community Reports</h3>
        <p>Citizens can report sightings of stolen vehicles, alerting both owners and authorities.</p>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <h3>Get Recovered</h3>
        <p>Law enforcement tracks reports and updates vehicle status once recovered.</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section" style="background:var(--bg2);padding:4rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
  <div class="container">
    <h2 class="section-title">System Features</h2>
    <p class="section-sub">Everything you need to protect your vehicle</p>
    <div class="grid grid-3" style="margin-top:2rem;">
      <?php
      $features = [
        ['🔍','Instant Plate Verification','Check any vehicle plate in seconds to know if it\'s stolen.'],
        ['📸','Image-Based Search','Upload a photo of a vehicle plate for automatic recognition.'],
        ['🔔','Real-Time Alerts','Get notified the moment your stolen vehicle is spotted.'],
        ['👁','Crowd Reporting','Citizens report sightings to build a community alert network.'],
        ['📊','Admin Dashboard','Full administrative control for officers and system admins.'],
        ['🔒','Secure & Private','Your data is encrypted and only shared with authorized users.'],
      ];
      foreach ($features as $f): ?>
      <div class="card" style="text-align:center;padding:2rem 1.5rem;">
        <div style="font-size:2.2rem;margin-bottom:.85rem;"><?= $f[0] ?></div>
        <h3 style="font-size:1rem;font-weight:600;margin-bottom:.5rem;"><?= $f[1] ?></h3>
        <p style="font-size:.83rem;color:var(--text2);"><?= $f[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section" style="text-align:center;padding:5rem 1.5rem;">
  <div class="hero-badge" style="display:inline-flex;margin-bottom:1rem;">🚀 Join the Community</div>
  <h2 style="font-size:2rem;font-weight:800;margin-bottom:.75rem;">Ready to Protect Your Vehicle?</h2>
  <p style="color:var(--text2);max-width:480px;margin:0 auto 2rem;font-size:1rem;">Join thousands of Bamenda residents already using SSVIAS to keep their vehicles safe.</p>
  <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
    <a href="/ssvias/register.php" class="btn btn-primary btn-lg">📝 Create Free Account</a>
    <a href="/ssvias/verify.php" class="btn btn-outline btn-lg">🔍 Verify a Vehicle</a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
