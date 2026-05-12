<?php
$pageTitle = 'Verify Vehicle';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$prefill = strtoupper(trim(get('plate')));
require_once 'includes/header.php';
?>
<div class="container" style="max-width:800px;padding-top:2.5rem;padding-bottom:4rem;">
  <div class="page-header" style="text-align:center;border:none;">
    <h1 style="font-size:2rem;">🔍 Vehicle Verification</h1>
    <p>Check if any vehicle is listed as stolen in the SSVIAS registry.</p>
  </div>

  <!-- Tabs -->
  <div class="verify-tabs" id="verifyTabs">
    <button class="verify-tab active" onclick="switchTab('plate')" id="tabPlate">🔢 Search by Plate</button>
    <button class="verify-tab" onclick="switchTab('vin')" id="tabVin">🔑 Search by VIN</button>
    <button class="verify-tab" onclick="switchTab('image')" id="tabImage">📸 Upload Image (OCR)</button>
  </div>

  <!-- Plate Search -->
  <div id="panelPlate">
    <div class="card">
      <div class="form-group" style="margin-bottom:0;">
        <label>Enter Plate Number</label>
        <div class="search-bar">
          <input type="text" id="plateInput" placeholder="e.g. NW-1234-A"
            value="<?= e($prefill) ?>"
            style="text-transform:uppercase;font-family:'JetBrains Mono',monospace;font-size:1.1rem;letter-spacing:.1em;">
          <button class="btn btn-primary" onclick="verifyPlate()" id="plateBtn">🔍 Verify</button>
        </div>
      </div>
    </div>
  </div>

  <!-- VIN Search -->
  <div id="panelVin" style="display:none;">
    <div class="card">
      <div class="form-group" style="margin-bottom:0;">
        <label>Enter Vehicle VIN</label>
        <div class="search-bar">
          <input type="text" id="vinInput" placeholder="17-character VIN"
            style="text-transform:uppercase;font-family:'JetBrains Mono',monospace;" maxlength="17">
          <button class="btn btn-primary" onclick="verifyVin()" id="vinBtn">🔍 Verify</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Image Upload -->
  <div id="panelImage" style="display:none;">
    <div class="card">
      <p style="font-size:.875rem;color:var(--text2);margin-bottom:1rem;">
        📸 Upload a clear photo of a vehicle's license plate. Our OCR engine will extract the plate number and check the database.
      </p>
      <div class="file-drop" id="ocrDrop">
        <input type="file" id="ocrFile" accept="image/*">
        <div class="drop-icon">📷</div>
        <p>Click or drag &amp; drop a plate photo here</p>
        <p style="font-size:.75rem;margin-top:.25rem;">JPG, PNG, WebP — Max 5MB</p>
      </div>
      <div class="file-preview" id="ocrPreview" style="display:none;margin-top:.75rem;border-radius:8px;overflow:hidden;max-height:200px;"></div>
      <button class="btn btn-primary btn-block" style="margin-top:1rem;" onclick="runOCR()" id="ocrBtn">🤖 Extract &amp; Verify Plate</button>
      <div id="ocrInfo" style="display:none;margin-top:.75rem;background:rgba(47,129,247,0.08);border:1px solid rgba(47,129,247,0.2);border-radius:8px;padding:.85rem;font-size:.85rem;color:var(--text2);"></div>
    </div>
  </div>

  <!-- Result -->
  <div class="verify-result" id="verifyResult"></div>

  <!-- Recent Stolen Vehicles -->
  <div style="margin-top:3rem;">
    <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:1rem;">🚨 Recently Reported Stolen</h2>
    <div class="table-wrap">
      <table id="stolenTable">
        <thead><tr><th>Plate</th><th>Vehicle</th><th>Color</th><th>Last Seen</th><th>Reported</th></tr></thead>
        <tbody>
          <?php
          $stolen = $pdo->query("
            SELECT v.plate_number, v.make, v.model, v.year, v.color, sr.last_seen_location, sr.reported_at
            FROM stolen_reports sr
            JOIN vehicles v ON v.id = sr.vehicle_id
            WHERE sr.status != 'closed'
            ORDER BY sr.reported_at DESC LIMIT 10
          ")->fetchAll();
          foreach ($stolen as $s): ?>
          <tr>
            <td><span class="vehicle-plate" style="font-size:.85rem;"><?= e($s['plate_number']) ?></span></td>
            <td><?= e($s['year'].' '.$s['make'].' '.$s['model']) ?></td>
            <td><?= e($s['color']) ?></td>
            <td><?= e($s['last_seen_location'] ?? 'Unknown') ?></td>
            <td><?= time_ago($s['reported_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($stolen)): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--text2);padding:2rem;">No active stolen reports.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function switchTab(tab) {
  ['plate','vin','image'].forEach(t => {
    document.getElementById('panel'+t.charAt(0).toUpperCase()+t.slice(1)).style.display = t===tab?'block':'none';
    document.getElementById('tab'+t.charAt(0).toUpperCase()+t.slice(1)).classList.toggle('active', t===tab);
  });
  document.getElementById('verifyResult').className = 'verify-result';
}

async function showResult(plate) {
  if (!plate) return;
  const btn = document.getElementById('plateBtn') || document.getElementById('vinBtn');
  const res  = document.getElementById('verifyResult');
  res.className = 'verify-result';
  res.innerHTML = '<div class="spinner"></div>';
  res.classList.add('show');

  const data = await apiGet(`/ssvias/api/verify.php?plate=${encodeURIComponent(plate)}`);
  if (!data.success) {
    res.className = 'verify-result show';
    res.style.background = 'rgba(255,255,255,0.03)';
    res.style.border = '1px solid var(--border)';
    res.innerHTML = `<div class="verify-icon">🔎</div><div class="verify-status" style="color:var(--text2);">Not Found</div><p style="color:var(--text2);font-size:.875rem;">${data.message}</p>`;
    return;
  }
  const v = data.data;
  const isSafe    = v.status === 'active' || v.status === 'recovered';
  const isStolen  = v.status === 'stolen';
  res.className   = 'verify-result show ' + (isStolen ? 'stolen' : 'safe');
  const icon      = isStolen ? '🚨' : '✅';
  const statusTxt = isStolen ? 'STOLEN — ALERT!' : (v.status === 'recovered' ? 'RECOVERED' : 'SAFE / CLEAR');
  const statusColor = isStolen ? 'var(--danger)' : 'var(--success)';
  let html = `
    <div class="verify-icon">${icon}</div>
    <div class="verify-status" style="color:${statusColor};">${statusTxt}</div>
    <div style="display:inline-block;background:var(--bg3);padding:.5rem 1.25rem;border-radius:8px;font-family:'JetBrains Mono',monospace;font-size:1.3rem;font-weight:700;letter-spacing:.12em;margin:.75rem 0;">${v.plate}</div>
    <div class="verify-details" style="margin-top:.5rem;">
      <strong style="color:var(--text);">${v.year} ${v.make} ${v.model}</strong> &nbsp;|&nbsp; ${v.color}
    </div>`;
  if (isStolen && v.report) {
    html += `<div style="margin-top:1rem;background:rgba(248,81,73,0.08);border:1px solid rgba(248,81,73,0.2);border-radius:8px;padding:1rem;text-align:left;max-width:440px;margin-inline:auto;">
      <div style="font-size:.8rem;font-weight:600;color:var(--danger);margin-bottom:.4rem;">⚠ THEFT REPORT DETAILS</div>
      <div style="font-size:.83rem;color:var(--text2);"><strong style="color:var(--text);">Last Seen:</strong> ${v.report.location||'Unknown'}</div>
      <div style="font-size:.83rem;color:var(--text2);margin-top:.25rem;"><strong style="color:var(--text);">Reported:</strong> ${new Date(v.report.reported_at).toLocaleDateString()}</div>
      ${v.report.description?`<div style="font-size:.83rem;color:var(--text2);margin-top:.25rem;">${v.report.description}</div>`:''}
    </div>`;
    html += `<div style="margin-top:1rem;"><a href="/ssvias/sightings.php?vehicle_plate=${encodeURIComponent(v.plate)}" class="btn btn-danger btn-sm">👁 Report a Sighting</a></div>`;
  }
  res.innerHTML = html;
}

async function verifyPlate() {
  const plate = document.getElementById('plateInput').value.trim().toUpperCase();
  if (!plate) { showToast('Enter a plate number first.','error'); return; }
  document.getElementById('plateInput').value = plate;
  await showResult(plate);
}
async function verifyVin() {
  const vin = document.getElementById('vinInput').value.trim().toUpperCase();
  if (!vin) { showToast('Enter a VIN first.','error'); return; }
  const res = await apiGet(`/ssvias/api/verify.php?vin=${encodeURIComponent(vin)}`);
  if (res.success) await showResult(res.data.plate);
  else { document.getElementById('verifyResult').className='verify-result show'; document.getElementById('verifyResult').innerHTML=`<div class="verify-icon">🔎</div><div style="color:var(--text2);">${res.message}</div>`; }
}

// OCR
document.getElementById('ocrFile').addEventListener('change', function() {
  const file = this.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const prev = document.getElementById('ocrPreview');
    prev.innerHTML = `<img src="${e.target.result}" style="width:100%;object-fit:cover;">`;
    prev.style.display = 'block';
  };
  reader.readAsDataURL(file);
});

async function runOCR() {
  const file = document.getElementById('ocrFile').files[0];
  if (!file) { showToast('Please select an image first.','error'); return; }
  setLoading(document.getElementById('ocrBtn'), true);
  const fd = new FormData();
  fd.append('image', file);
  const data = await fetch('/ssvias/api/ocr.php',{method:'POST',body:fd}).then(r=>r.json());
  setLoading(document.getElementById('ocrBtn'), false);
  if (!data.success) { showToast(data.message,'error'); return; }
  const info = document.getElementById('ocrInfo');
  info.style.display = 'block';
  info.innerHTML = `🤖 Extracted plate: <strong style="color:var(--text);font-family:'JetBrains Mono',monospace;">${data.plate}</strong> &nbsp;(Confidence: ${data.confidence}%)`;
  document.getElementById('plateInput').value = data.plate;
  switchTab('plate');
  await showResult(data.plate);
}

// Auto-search if plate is pre-filled
document.addEventListener('DOMContentLoaded', () => {
  const prefill = '<?= e($prefill) ?>';
  if (prefill) verifyPlate();
});

document.getElementById('plateInput').addEventListener('keydown', e => { if (e.key==='Enter') verifyPlate(); });
document.getElementById('plateInput').addEventListener('input', function() { this.value=this.value.toUpperCase(); });
</script>
<?php require_once 'includes/footer.php'; ?>
