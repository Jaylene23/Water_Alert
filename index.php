<?php
include 'config.php';
requireRole('user');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>WaterGuard — User Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#f0f9ff;--surface:#fff;--border:#e0f2fe;--text:#0f172a;--muted:#64748b;--accent:#3b82f6;--danger:#ef4444;--success:#22c55e;--warn:#f59e0b;}
body{background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--text);}
header{background:#1e3a5f;color:#fff;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;}
.brand{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;}
.brand span{color:#38bdf8;}
.badge-role{background:rgba(59,130,246,.25);color:#bfdbfe;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600;}
nav{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;display:flex;gap:4px;}
nav a{padding:12px 16px;font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;border-bottom:2px solid transparent;}
nav a.active{color:var(--accent);border-bottom-color:var(--accent);}
.container{max-width:1100px;margin:24px auto;padding:0 20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;}
.card h3{font-family:'Space Grotesk',sans-serif;font-size:1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.status-box{border-radius:10px;padding:20px;text-align:center;margin-bottom:12px;}
.status-safe{background:#dcfce7;border:1.5px solid #86efac;}
.status-leak{background:#fee2e2;border:1.5px solid #fca5a5;animation:pulse .8s infinite;}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.02)}}
.status-icon{font-size:2.5rem;}
.status-text{font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;margin-top:6px;}
.upd{font-size:.78rem;color:var(--muted);margin-top:4px;}
.level-row{display:flex;justify-content:space-between;font-size:.88rem;margin-bottom:6px;font-weight:600;}
.bar-bg{background:#e2e8f0;border-radius:8px;height:14px;overflow:hidden;}
.bar-fill{height:100%;border-radius:8px;transition:width .5s,background .5s;}
.hint{font-size:.75rem;color:var(--muted);margin-top:6px;}
.notice{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;font-size:.85rem;color:#1d4ed8;}
table{width:100%;border-collapse:collapse;}
th{background:#f1f5f9;padding:10px 12px;text-align:left;font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
td{padding:10px 12px;font-size:.84rem;border-bottom:1px solid var(--border);}
.badge{padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-safe{background:#dcfce7;color:#15803d;}
.badge-leak{background:#fee2e2;color:#dc2626;}
.full{grid-column:1/-1;}
</style>
</head>
<body>
<header>
  <div class="brand">💧 Water<span>Guard</span></div>
  <div style="display:flex;align-items:center;gap:12px;">
    <span class="badge-role">👤 <?= htmlspecialchars($_SESSION['user']) ?> — User</span>
    <a href="logout.php" style="color:#94a3b8;font-size:.8rem;text-decoration:underline;">Logout</a>
  </div>
</header>
<nav>
  <a href="index.php" class="active">Dashboard</a>
  <a href="history.php">History</a>
</nav>

<div class="container">
  <div class="card">
    <h3>🚨 Live Status</h3>
    <div id="statusBox" class="status-box status-safe">
      <div class="status-icon" id="statusIcon">✅</div>
      <div class="status-text" id="statusText">System Normal</div>
      <div class="upd" id="lastUpd">Waiting for data…</div>
    </div>
    <div class="notice">ℹ️ You have <strong>read-only</strong> access. Contact a Manager for controls.</div>
  </div>

  <div class="card">
    <h3>💧 Water Level</h3>
    <canvas id="waterChart" height="180"></canvas>
    <div class="level-row"><span>Current Level</span><span id="levelPct">–</span></div>
    <div class="bar-bg"><div id="barFill" class="bar-fill" style="width:0%;background:#22c55e;"></div></div>
    <p class="hint">⚠️ Alert triggers when level drops below 20%</p>
  </div>

  <div class="card full">
    <h3>📋 Recent Activity <span style="font-size:.75rem;font-weight:400;color:var(--muted)">(Last 10 entries)</span></h3>
    <table>
      <thead><tr><th>#</th><th>Status</th><th>Source</th><th>Time</th></tr></thead>
      <tbody id="logBody"><tr><td colspan="4" style="text-align:center;color:var(--muted);">Loading…</td></tr></tbody>
    </table>
  </div>
</div>

<script>
let chart, labels = [], data = [];
function levelColor(v){ return v < 20 ? '#ef4444' : v < 50 ? '#f59e0b' : '#22c55e'; }

(function(){
  const ctx = document.getElementById('waterChart').getContext('2d');
  chart = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Water Level (%)', data, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.1)', fill: true, tension: .3, pointRadius: 3 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
  });
})();

function fetchStatus() {
  fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=get_status' })
  .then(r => r.json()).then(d => {
    if (!d.success) return;
    const s = d.data;
    const leak = parseInt(s.water_level) < 20 || s.status === 'LEAK';
    document.getElementById('statusBox').className = 'status-box ' + (leak ? 'status-leak' : 'status-safe');
    document.getElementById('statusIcon').textContent = leak ? '🚨' : '✅';
    document.getElementById('statusText').textContent = leak ? 'LEAK DETECTED' : 'System Normal';
    document.getElementById('lastUpd').textContent = 'Last updated: ' + new Date(s.last_updated).toLocaleString();
    document.getElementById('levelPct').textContent = s.water_level + '%';
    const bar = document.getElementById('barFill');
    bar.style.width = s.water_level + '%';
    bar.style.background = levelColor(parseInt(s.water_level));
    const now = new Date().toLocaleTimeString();
    if (labels.length > 20) { labels.shift(); data.shift(); }
    labels.push(now); data.push(parseInt(s.water_level));
    chart.data.datasets[0].borderColor = levelColor(parseInt(s.water_level));
    chart.data.datasets[0].backgroundColor = levelColor(parseInt(s.water_level)) + '22';
    chart.update();
  });
}

function fetchLogs() {
  fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=get_logs' })
  .then(r => r.json()).then(d => {
    if (!d.success) return;
    document.getElementById('logBody').innerHTML = d.logs.length
      ? d.logs.map(l => `<tr><td>${l.id}</td><td><span class="badge badge-${l.status.toLowerCase()}">${l.status}</span></td><td>${l.source}</td><td>${new Date(l.created_at).toLocaleString()}</td></tr>`).join('')
      : '<tr><td colspan="4" style="text-align:center;color:var(--muted);">No logs yet.</td></tr>';
  });
}

fetchStatus(); fetchLogs();
setInterval(() => { fetchStatus(); fetchLogs(); }, 5000);
</script>
</body>
</html>
