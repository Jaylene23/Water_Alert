<?php
include 'config.php';
requireRole('manager');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>WaterGuard — Manager</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#f0fdf4;--surface:#fff;--border:#d1fae5;--text:#0f172a;--muted:#64748b;--accent:#10b981;--danger:#ef4444;--success:#22c55e;--warn:#f59e0b;--primary:#064e3b;}
body{background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--text);}
header{background:var(--primary);color:#fff;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;}
.brand{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:10px;}
.brand img{height:32px;width:auto;}
.brand span{color:#6ee7b7;}
.badge-role{background:rgba(16,185,129,.25);color:#a7f3d0;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600;}
nav{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;display:flex;gap:4px;}
nav a{padding:12px 16px;font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;border-bottom:2px solid transparent;}
nav a.active{color:var(--accent);border-bottom-color:var(--accent);}
.container{max-width:1200px;margin:24px auto;padding:0 20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;}
.card h3{font-family:'Space Grotesk',sans-serif;font-size:1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.status-box{border-radius:10px;padding:20px;text-align:center;}
.status-safe{background:#dcfce7;border:1.5px solid #86efac;}
.status-leak{background:#fee2e2;border:1.5px solid #fca5a5;animation:pulse .8s infinite;}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.02)}}
.status-icon{font-size:2.5rem;}
.status-text{font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;margin-top:6px;}
.upd{font-size:.78rem;color:var(--muted);margin-top:4px;}
.health-item{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:.88rem;}
.health-item:last-child{border:0;}
.hval{font-weight:700;}
.hok{color:var(--success)} .hwarn{color:var(--warn)} .hbad{color:var(--danger)}
.ctrl-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.btn{padding:12px 16px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:.88rem;font-family:inherit;transition:opacity .2s,transform .1s;}
.btn:hover{opacity:.88;transform:translateY(-1px);}
.btn:active{transform:scale(.97);}
.btn-leak{background:#ef4444;color:#fff;}
.btn-safe{background:#22c55e;color:#fff;}
.btn-valve{background:#1e293b;color:#fff;grid-column:1/-1;}
.level-row{display:flex;justify-content:space-between;font-size:.88rem;margin-bottom:6px;font-weight:600;}
.bar-bg{background:#e2e8f0;border-radius:8px;height:16px;overflow:hidden;}
.bar-fill{height:100%;border-radius:8px;transition:width .5s,background .5s;}
table{width:100%;border-collapse:collapse;}
th{background:#f1f5f9;padding:10px 12px;text-align:left;font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
td{padding:10px 12px;font-size:.84rem;border-bottom:1px solid #f1f5f9;}
.badge{padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-safe{background:#dcfce7;color:#15803d;}
.badge-leak{background:#fee2e2;color:#dc2626;}
.badge-web{background:#ede9fe;color:#6d28d9;}
.badge-esp{background:#dbeafe;color:#1d4ed8;}
.full{grid-column:1/-1;}
.toast{position:fixed;bottom:24px;right:24px;background:#1e293b;color:#fff;padding:12px 20px;border-radius:10px;font-size:.85rem;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none;z-index:99;}
.toast.show{opacity:1;transform:translateY(0);}
</style>
</head>
<body>
<header>
  <div class="brand">
    <img src="WaterleakLG.png" alt="WaterGuard Logo">
    Water<span>Guard</span> <span style="font-size:.75rem;opacity:.6;margin-left:4px;">Manager</span>
  </div>
  <div style="display:flex;align-items:center;gap:12px;">
    <span class="badge-role">🛠️ <?= htmlspecialchars($_SESSION['user']) ?></span>
    <a href="logout.php" style="color:#6ee7b7;font-size:.8rem;text-decoration:underline;">Logout</a>
  </div>
</header>
<nav>
  <a href="manager.php" class="active">Dashboard</a>
  <a href="history.php">History</a>
</nav>

<div class="container">
  <div class="card">
    <h3>🚨 Live Status</h3>
    <div id="statusBox" class="status-box status-safe">
      <div class="status-icon" id="statusIcon">✅</div>
      <div class="status-text" id="statusText">System Normal</div>
      <div class="upd" id="lastUpd">–</div>
    </div>
  </div>

  <div class="card">
    <h3>💧 Water Level</h3>
    <canvas id="waterChart" height="180"></canvas>
    <div class="level-row"><span>Current Level</span><span id="levelPct">–</span></div>
    <div class="bar-bg"><div id="barFill" class="bar-fill" style="width:0%;background:#22c55e;"></div></div>
  </div>

  <div class="card">
    <h3>📡 System Health</h3>
    <div class="health-item"><span>Battery</span><span id="hBattery" class="hval hok">–</span></div>
    <div class="health-item"><span>Signal</span><span id="hSignal" class="hval hok">–</span></div>
    <div class="health-item"><span>Valve</span><span id="hValve" class="hval">–</span></div>
    <div class="health-item"><span>ESP32</span><span class="hval hok">Active</span></div>
  </div>

  <div class="card">
    <h3>🎛️ Controls</h3>
    <div class="ctrl-grid">
      <button class="btn btn-leak" onclick="simulate('LEAK')">🚨 Simulate Leak</button>
      <button class="btn btn-safe" onclick="simulate('SAFE')">✅ Simulate Safe</button>
      <button id="valveBtn" class="btn btn-valve" onclick="toggleValve()">🔒 Close Valve (Currently: OPEN)</button>
    </div>
    <p style="font-size:.75rem;color:var(--muted);margin-top:12px;">⚠️ Simulations write to the database and trigger alerts.</p>
  </div>

  <div class="card full">
    <h3>📜 Recent Logs <span style="font-size:.75rem;font-weight:400;color:var(--muted)">(Last 50 entries)</span></h3>
     <table>
      <thead> <tr><th>#</th><th>Status</th><th>Source</th><th>Time</th></tr> </thead>
      <tbody id="logBody"> <tr><td colspan="4" style="text-align:center;color:var(--muted);">Loading…</td></tr> </tbody>
     </table>
  </div>
</div>
<div class="toast" id="toast"></div>

<script>
let chart, labels = [], data = [], valveCurrent = 'OPEN';
function levelColor(v){ return v < 20 ? '#ef4444' : v < 50 ? '#f59e0b' : '#22c55e'; }

(function(){
  const ctx = document.getElementById('waterChart').getContext('2d');
  chart = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Level (%)', data, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.1)', fill: true, tension: .3, pointRadius: 3 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
  });
})();

function toast(msg){
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}

function fetchStatus(){
  fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=get_status' })
  .then(r => r.json()).then(d => {
    if (!d.success) return;
    const s = d.data;
    const lvl = parseInt(s.water_level);
    const leak = lvl < 20 || s.status === 'LEAK';
    document.getElementById('statusBox').className = 'status-box ' + (leak ? 'status-leak' : 'status-safe');
    document.getElementById('statusIcon').textContent = leak ? '🚨' : '✅';
    document.getElementById('statusText').textContent = leak ? '⚠ LEAK DETECTED' : 'System Normal';
    document.getElementById('lastUpd').textContent = 'Updated: ' + new Date(s.last_updated).toLocaleString();
    document.getElementById('levelPct').textContent = lvl + '%';
    document.getElementById('barFill').style.width = lvl + '%';
    document.getElementById('barFill').style.background = levelColor(lvl);
    // health
    const bat = parseInt(s.battery);
    const batEl = document.getElementById('hBattery');
    batEl.textContent = bat + '%';
    batEl.className = 'hval ' + (bat > 50 ? 'hok' : bat > 20 ? 'hwarn' : 'hbad');
    const sig = s.signal_strength || s.signal || '–';
    const sigEl = document.getElementById('hSignal');
    sigEl.textContent = sig;
    sigEl.className = 'hval ' + (sig === 'GOOD' ? 'hok' : 'hbad');
    valveCurrent = s.valve_state;
    document.getElementById('hValve').textContent = s.valve_state;
    document.getElementById('valveBtn').textContent = s.valve_state === 'OPEN'
      ? '🔒 Close Valve (Currently: OPEN)'
      : '🔓 Open Valve (Currently: CLOSED)';
    // chart
    const now = new Date().toLocaleTimeString();
    if (labels.length > 20) { labels.shift(); data.shift(); }
    labels.push(now); data.push(lvl);
    chart.data.datasets[0].borderColor = levelColor(lvl);
    chart.data.datasets[0].backgroundColor = levelColor(lvl) + '22';
    chart.update();
  });
}

function fetchLogs(){
  fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=get_logs' })
  .then(r => r.json()).then(d => {
    if (!d.success) return;
    document.getElementById('logBody').innerHTML = d.logs.length
      ? d.logs.map(l => `<tr>
          <td>${l.id}</td>
          <td><span class="badge badge-${l.status.toLowerCase()}">${l.status}</span></td>
          <td><span class="badge badge-${l.source === 'ESP32' ? 'esp' : 'web'}">${l.source}</span></td>
          <td>${new Date(l.created_at).toLocaleString()}</td>
        </tr>`).join('')
      : '<tr><td colspan="4" style="text-align:center;color:var(--muted);">No logs yet.</td></tr>';
  });
}

function simulate(type){
  fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=simulate_leak&status=' + type })
  .then(r => r.json()).then(d => {
    if (d.success) { toast(type === 'LEAK' ? '🚨 Leak simulated!' : '✅ Safe state restored!'); fetchStatus(); fetchLogs(); }
    else toast('❌ ' + (d.error || 'Error'));
  });
}

function toggleValve(){
  fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=toggle_valve&current=' + valveCurrent })
  .then(r => r.json()).then(d => {
    if (d.success) { toast('Valve is now ' + d.new); fetchStatus(); }
    else toast('❌ ' + (d.error || 'Error'));
  });
}

fetchStatus(); fetchLogs();
setInterval(() => { fetchStatus(); fetchLogs(); }, 4000);
</script>
</body>
</html>