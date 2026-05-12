/**
 * SSVIAS — Global JavaScript Utilities
 */

// ─── Toast Notifications ──────────────────────────────────
function showToast(message, type = 'info', duration = 4000) {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const icons = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span style="font-size:1.1rem;">${icons[type] || 'ℹ'}</span> ${message}`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'slideIn .3s ease reverse';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// ─── Fetch API Wrapper ────────────────────────────────────
async function apiPost(url, data) {
  const fd = data instanceof FormData ? data : (() => {
    const f = new FormData();
    Object.entries(data).forEach(([k, v]) => f.append(k, v));
    return f;
  })();
  const res = await fetch(url, { method: 'POST', body: fd });
  return res.json();
}

async function apiGet(url) {
  const res = await fetch(url);
  return res.json();
}

// ─── File Preview ─────────────────────────────────────────
function initFilePreviews() {
  document.querySelectorAll('.file-drop').forEach(drop => {
    const input = drop.querySelector('input[type=file]');
    const preview = drop.closest('.form-group')?.querySelector('.file-preview');
    if (!input || !preview) return;

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });

    drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('dragover'); });
    drop.addEventListener('dragleave', () => drop.classList.remove('dragover'));
    drop.addEventListener('drop', e => {
      e.preventDefault();
      drop.classList.remove('dragover');
      if (e.dataTransfer.files[0]) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
      }
    });
  });
}

// ─── Loading Button ───────────────────────────────────────
function setLoading(btn, loading) {
  if (loading) {
    btn.dataset.original = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> Loading...';
    btn.disabled = true;
  } else {
    btn.innerHTML = btn.dataset.original || btn.innerHTML;
    btn.disabled = false;
  }
}

// ─── Confirm Dialog ───────────────────────────────────────
function confirmAction(message, callback) {
  if (confirm(message)) callback();
}

// ─── Table Search ─────────────────────────────────────────
function initTableSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

// ─── Counter Animation ────────────────────────────────────
function animateCounters() {
  document.querySelectorAll('[data-counter]').forEach(el => {
    const target = parseInt(el.dataset.counter);
    let current = 0;
    const step = Math.ceil(target / 40);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString();
      if (current >= target) clearInterval(timer);
    }, 40);
  });
}

// ─── Modal ────────────────────────────────────────────────
function openModal(id) {
  document.getElementById(id)?.classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('show');
  document.body.style.overflow = '';
}
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('show');
    document.body.style.overflow = '';
  }
});

// ─── Init ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initFilePreviews();
  animateCounters();

  // Flash messages from URL param
  const url = new URL(window.location.href);
  const msg = url.searchParams.get('msg');
  const err = url.searchParams.get('err');
  if (msg) showToast(decodeURIComponent(msg), 'success');
  if (err) showToast(decodeURIComponent(err), 'error');
});
