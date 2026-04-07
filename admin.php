<?php
include 'config.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>WaterGuard — Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#faf5ff;--surface:#fff;--border:#e9d5ff;--text:#0f172a;--muted:#64748b;--accent:#8b5cf6;--danger:#ef4444;--success:#22c55e;--warn:#f59e0b;--primary:#1e3a5f;}
body{background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--text);}
header{background:var(--primary);color:#fff;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;}
.brand{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:10px;}
.brand img{height:32px;width:auto;}
.brand span{color:#38bdf8;}
.badge-role{background:rgba(59,130,246,.3);color:#bfdbfe;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600;}
nav{background:var(--primary);border-bottom:1px solid rgba(255,255,255,0.1);padding:0 24px;display:flex;gap:4px;flex-wrap:wrap;}
nav a{padding:12px 16px;font-size:.85rem;font-weight:600;color:rgba(255,255,255,0.7);text-decoration:none;border-bottom:2px solid transparent;transition:all .2s;cursor:pointer;}
nav a:hover{color:#fff;background:rgba(255,255,255,0.1);}
nav a.active{color:#fff;border-bottom-color:#38bdf8;}
.tab-content{display:none;}
.tab-content.active{display:block;}
.container{max-width:1400px;margin:24px auto;padding:0 20px;}
.grid-4{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:20px;}
.grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;}
.card h3{font-family:'Space Grotesk',sans-serif;font-size:1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.stat-tile{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center;}
.stat-num{font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:700;color:var(--accent);}
.stat-label{font-size:.8rem;color:var(--muted);margin-top:4px;font-weight:500;}
table{width:100%;border-collapse:collapse;}
th{background:#f5f3ff;padding:10px 12px;text-align:left;font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
td{padding:10px 12px;font-size:.84rem;border-bottom:1px solid #f5f3ff;}
.badge{padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-user{background:#dbeafe;color:#1d4ed8;}
.badge-manager{background:#d1fae5;color:#065f46;}
.badge-admin{background:#ede9fe;color:#5b21b6;}
.badge-safe{background:#dcfce7;color:#15803d;}
.badge-leak{background:#fee2e2;color:#dc2626;}
.form-row{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;}
label{display:block;font-size:.8rem;font-weight:600;color:var(--muted);margin-bottom:5px;}
input[type=text],input[type=password],select{width:100%;background:#faf5ff;border:1px solid var(--border);color:var(--text);padding:10px 12px;border-radius:8px;font-size:.88rem;outline:none;font-family:inherit;}
input:focus,select:focus{border-color:var(--accent);}
.btn{padding:10px 18px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:.85rem;font-family:inherit;transition:opacity .2s;}
.btn:hover{opacity:.85;}
.btn-primary{background:var(--accent);color:#fff;}
.btn-danger{background:var(--danger);color:#fff;padding:6px 12px;font-size:.78rem;}
.health-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f5f3ff;font-size:.88rem;}
.health-row:last-child{border:0;}
.hval{font-weight:700;}
.hok{color:var(--success)} .hwarn{color:var(--warn)} .hbad{color:var(--danger)}
.dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;}
.dot-ok{background:var(--success)} .dot-bad{background:var(--danger)}
.toast{position:fixed;bottom:24px;right:24px;background:#1e3a5f;color:#fff;padding:12px 20px;border-radius:10px;font-size:.85rem;opacity:0;transform:translateY(10px);transition:all .3s;pointer-events:none;z-index:99;}
.toast.show{opacity:1;transform:translateY(0);}
.log-details{max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}.container{overflow-x:auto;}}
</style>
</head>
<body>
<header>
  <div class="brand">
    <img src="WaterleakLG.png" alt="WaterGuard Logo">
    Water<span>Guard</span> <span style="font-size:.75rem;opacity:.6;margin-left:4px;">Admin Panel</span>
  </div>
  <div style="display:flex;align-items:center;gap:12px;">
    <span class="badge-role">🔐 <?= htmlspecialchars($_SESSION['user']) ?></span>
    <a href="logout.php" style="color:#bfdbfe;font-size:.8rem;text-decoration:underline;">Logout</a>
  </div>
</header>
<nav>
  <a class="active" onclick="switchTab('overview',this)">Overview</a>
  <a onclick="switchTab('users',this)">User Management</a>
  <a onclick="switchTab('userlogs',this)">User Logs</a>
  <a onclick="switchTab('logs',this)">Leak Logs</a>
  <a onclick="switchTab('alerts',this)">Alerts</a>
</nav>

<!-- OVERVIEW -->
<div id="tab-overview" class="tab-content active">
<div class="container">
  <div class="grid-4">
    <div class="stat-tile"><div class="stat-num" id="s-users">–</div><div class="stat-label">Total Users</div></div>
    <div class="stat-tile"><div class="stat-num" id="s-logs">–</div><div class="stat-label">Total Logs</div></div>
    <div class="stat-tile"><div class="stat-num" id="s-leaks" style="color:#ef4444">–</div><div class="stat-label">Leak Events</div></div>
    <div class="stat-tile"><div class="stat-num" id="s-alerts" style="color:#f59e0b">–</div><div class="stat-label">Alerts Sent</div></div>
  </div>
  <div class="grid-2">
    <div class="card">
      <h3>🏥 System Health</h3>
      <div class="health-row"><span>Tank Status</span><span id="ov-status" class="hval hok">–</span></div>
      <div class="health-row"><span>Water Level</span><span id="ov-level" class="hval">–</span></div>
      <div class="health-row"><span>Battery</span><span id="ov-battery" class="hval">–</span></div>
      <div class="health-row"><span>Signal</span><span id="ov-signal" class="hval">–</span></div>
      <div class="health-row"><span>Valve</span><span id="ov-valve" class="hval">–</span></div>
      <div class="health-row"><span>Last Updated</span><span id="ov-upd" style="font-size:.8rem;color:var(--muted)">–</span></div>
    </div>
    <div class="card">
      <h3>👥 Users by Role</h3>
      <div id="roleList" style="display:flex;flex-direction:column;gap:8px;"></div>
    </div>
  </div>
</div>
</div>

<!-- USER MANAGEMENT -->
<div id="tab-users" class="tab-content">
<div class="container">
  <div class="card" style="margin-bottom:20px;">
    <h3>➕ Create New User</h3>
    <div class="form-row">
      <div><label>Username</label><input type="text" id="nu-user" placeholder="username"></div>
      <div><label>Password</label><input type="password" id="nu-pass" placeholder="password"></div>
      <div><label>Role</label>
        <select id="nu-role">
          <option value="user">👤 User</option>
          <option value="manager">🛠️ Manager</option>
          <option value="admin">🔐 Admin</option>
        </select>
      </div>
      <div><label>&nbsp;</label><button class="btn btn-primary" onclick="createUser()">Create</button></div>
    </div>
  </div>
  <div class="card">
    <h3>👥 All Users</h3>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>ID</th><th>Username</th><th>Role</th><th>Created</th><th>Action</th></tr>
        </thead>
        <tbody id="userBody">
          <tr><td colspan="5" style="text-align:center;color:var(--muted);">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<!-- USER LOGS -->
<div id="tab-userlogs" class="tab-content">
<div class="container">
  <div class="card">
    <h3>User Activity Logs</h3>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Action</th>
            <th>IP Address</th>
            <th>Device</th>
            <th>Browser</th>
            <th>OS</th>
            <th>Details</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody id="userLogsBody">
          <tr><td colspan="9" style="text-align:center;color:var(--muted);">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<!-- LEAK LOGS -->
<div id="tab-logs" class="tab-content">
<div class="container">
  <div class="card">
    <h3>📋 All Leak Logs</h3>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>ID</th><th>Status</th><th>Source</th><th>Time</th></tr>
        </thead>
        <tbody id="logBody">
          <tr><td colspan="4" style="text-align:center;color:var(--muted);">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<!-- ALERTS -->
<div id="tab-alerts" class="tab-content">
<div class="container">
  <div class="card">
    <h3>🔔 System Alerts</h3>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>ID</th><th>Type</th><th>Message</th><th>Time</th></tr>
        </thead>
        <tbody id="alertBody">
          <tr><td colspan="4" style="text-align:center;color:var(--muted);">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<div class="toast" id="toast"></div>

<script>
function toast(msg){
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}

function switchTab(name, el){
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  el.classList.add('active');
  if (name === 'users') loadUsers();
  if (name === 'userlogs') loadUserLogs();
  if (name === 'logs') loadLogs();
  if (name === 'alerts') loadAlerts();
}

function post(body){ 
  return fetch('api.php', { 
    method: 'POST', 
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
    body 
  }).then(r => r.json()); 
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
  });
}

function loadStats(){
  post('action=get_stats').then(d => {
    if (!d.success) return;
    document.getElementById('s-users').textContent = d.stats.total_users;
    document.getElementById('s-logs').textContent = d.stats.total_logs;
    document.getElementById('s-leaks').textContent = d.stats.total_leaks;
    document.getElementById('s-alerts').textContent = d.stats.total_alerts;
  }).catch(err => console.error('Error loading stats:', err));
}

function loadOverview(){
  post('action=get_status').then(d => {
    if (!d.success || !d.data) {
      console.log('No tank data available, using defaults');
      document.getElementById('ov-status').innerHTML = '<span class="dot dot-ok"></span>SAFE';
      document.getElementById('ov-status').className = 'hval hok';
      document.getElementById('ov-level').textContent = '75%';
      document.getElementById('ov-level').className = 'hval hok';
      document.getElementById('ov-battery').textContent = '85%';
      document.getElementById('ov-battery').className = 'hval hok';
      document.getElementById('ov-signal').textContent = 'GOOD';
      document.getElementById('ov-signal').className = 'hval hok';
      document.getElementById('ov-valve').textContent = 'OPEN';
      document.getElementById('ov-upd').textContent = new Date().toLocaleString();
      return;
    }
    
    const s = d.data;
    const lvl = parseInt(s.water_level) || 75;
    const leak = lvl < 20 || s.status === 'LEAK';
    const stEl = document.getElementById('ov-status');
    stEl.innerHTML = (leak ? '<span class="dot dot-bad"></span>LEAK' : '<span class="dot dot-ok"></span>SAFE');
    stEl.className = 'hval ' + (leak ? 'hbad' : 'hok');
    const lvEl = document.getElementById('ov-level');
    lvEl.textContent = lvl + '%';
    lvEl.className = 'hval ' + (lvl < 20 ? 'hbad' : lvl < 50 ? 'hwarn' : 'hok');
    const bat = parseInt(s.battery) || 85;
    const batEl = document.getElementById('ov-battery');
    batEl.textContent = bat + '%';
    batEl.className = 'hval ' + (bat > 50 ? 'hok' : bat > 20 ? 'hwarn' : 'hbad');
    const sig = s.signal_strength || s.signal || 'GOOD';
    const sigEl = document.getElementById('ov-signal');
    sigEl.textContent = sig;
    sigEl.className = 'hval ' + (sig === 'GOOD' ? 'hok' : 'hbad');
    document.getElementById('ov-valve').textContent = s.valve_state || 'OPEN';
    document.getElementById('ov-upd').textContent = s.last_updated ? new Date(s.last_updated).toLocaleString() : new Date().toLocaleString();
  }).catch(err => {
    console.error('Error loading overview:', err);
  });
}

function loadRoleBreakdown(){
  post('action=get_users').then(d => {
    if (!d.success) return;
    const counts = { admin: 0, manager: 0, user: 0 };
    d.users.forEach(u => { if (counts[u.role] !== undefined) counts[u.role]++; });
    const icons = { admin: '🔐', manager: '🛠️', user: '👤' };
    const colors = { admin: 'badge-admin', manager: 'badge-manager', user: 'badge-user' };
    document.getElementById('roleList').innerHTML = Object.entries(counts).map(([r, c]) => `
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:#faf5ff;border-radius:8px;">
        <span style="font-size:.9rem;font-weight:600;">${icons[r]} ${r.charAt(0).toUpperCase()+r.slice(1)}</span>
        <span class="badge ${colors[r]}">${c} account${c !== 1 ? 's' : ''}</span>
      </div>`).join('');
  }).catch(err => console.error('Error loading role breakdown:', err));
}

function loadUsers(){
  post('action=get_users').then(d => {
    if (!d.success) {
      toast('❌ Failed to load users: ' + (d.error || 'Unknown error'));
      document.getElementById('userBody').innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--muted);">Error loading users</td></tr>';
      return;
    }
    if (!d.users || d.users.length === 0) {
      document.getElementById('userBody').innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--muted);">No users found.</td></tr>';
      return;
    }
    document.getElementById('userBody').innerHTML = d.users.map(u => `
      <tr>
        <td>${u.user_id}</td>
        <td><strong>${escapeHtml(u.username)}</strong></td>
        <td><span class="badge badge-${u.role}">${u.role}</span></td>
        <td style="font-size:.8rem;color:var(--muted)">${new Date(u.created_at).toLocaleString()}</td>
        <td><button class="btn btn-danger" onclick="deleteUser(${u.user_id},'${escapeHtml(u.username)}')">Delete</button></td>
      </tr>
    `).join('');
  }).catch(err => {
    console.error('Error loading users:', err);
    toast('❌ Error loading users');
  });
}

function loadUserLogs(){
  post('action=get_user_logs').then(d => {
    if (!d.success) {
      document.getElementById('userLogsBody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--muted);">Error loading logs: ' + escapeHtml(d.error) + '</td></tr>';
      return;
    }
    
    if (!d.logs || d.logs.length === 0) {
      document.getElementById('userLogsBody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--muted);">No user logs found</td></tr>';
      return;
    }
    
    document.getElementById('userLogsBody').innerHTML = d.logs.map(log => `
      <tr>
        <td>${log.log_id}</td>
        <td><strong>${escapeHtml(log.username || 'Unknown')}</strong></td>
        <td><span class="badge badge-manager">${escapeHtml(log.action)}</span></td>
        <td><code style="font-size:0.7rem;">${escapeHtml(log.ip_address || '-')}</code></td>
        <td><span class="badge badge-user">${escapeHtml(log.device_type || '-')}</span></td>
        <td>${escapeHtml(log.browser || '-')}</td>
        <td>${escapeHtml(log.os || '-')}</td>
        <td class="log-details" title="${escapeHtml(log.details || '-')}">${escapeHtml(log.details || '-')}</td>
        <td style="font-size:0.75rem;">${new Date(log.created_at).toLocaleString()}</td>
      </tr>
    `).join('');
  }).catch(err => {
    console.error('Error loading user logs:', err);
    document.getElementById('userLogsBody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--muted);">Error loading logs</td></tr>';
  });
}

function createUser(){
  const uname = document.getElementById('nu-user').value.trim();
  const pass = document.getElementById('nu-pass').value.trim();
  const role = document.getElementById('nu-role').value;
  if (!uname || !pass) { toast('⚠️ Fill in all fields'); return; }
  post(`action=create_user&username=${encodeURIComponent(uname)}&password=${encodeURIComponent(pass)}&new_role=${role}`)
  .then(d => {
    if (d.success) {
      toast('✅ User created!');
      document.getElementById('nu-user').value = '';
      document.getElementById('nu-pass').value = '';
      loadUsers(); loadRoleBreakdown(); loadStats();
    } else toast('❌ ' + (d.error || 'Error'));
  }).catch(err => {
    console.error('Error creating user:', err);
    toast('❌ Error creating user');
  });
}

function deleteUser(userId, name){
  if (!confirm(`Delete user "${name}"? This action cannot be undone.`)) return;
  post(`action=delete_user&uid=${userId}`).then(d => {
    if (d.success) { 
      toast('🗑️ User deleted'); 
      loadUsers(); 
      loadRoleBreakdown(); 
      loadStats(); 
    } else {
      toast('❌ ' + (d.error || 'Error'));
    }
  }).catch(err => {
    console.error('Error deleting user:', err);
    toast('❌ Error deleting user');
  });
}

function loadLogs(){
  post('action=get_logs').then(d => {
    if (!d.success) return;
    document.getElementById('logBody').innerHTML = d.logs.length
      ? d.logs.map(l => `<tr>
          <td>${l.id}</td>
          <td><span class="badge badge-${l.status.toLowerCase()}">${l.status}</span></td>
          <td>${l.source}</td>
          <td>${new Date(l.created_at).toLocaleString()}</td>
        </tr>`).join('')
      : '<tr><td colspan="4" style="text-align:center;color:var(--muted);">No logs yet.</td></tr>';
  }).catch(err => console.error('Error loading logs:', err));
}

function loadAlerts(){
  post('action=get_alerts').then(d => {
    if (!d.success) return;
    document.getElementById('alertBody').innerHTML = d.alerts.length
      ? d.alerts.map(a => `<tr>
          <td>${a.id}</td>
          <td><span class="badge badge-leak">${a.alert_type || a.type || 'ALERT'}</span></td>
          <td>${a.message}</td>
          <td style="font-size:.8rem">${new Date(a.sent_at).toLocaleString()}</td>
        </tr>`).join('')
      : '<tr><td colspan="4" style="text-align:center;color:var(--muted);">No alerts yet.</td></tr>';
  }).catch(err => console.error('Error loading alerts:', err));
}

// Initial load
loadStats(); 
loadOverview(); 
loadRoleBreakdown();

// Auto-refresh every 5 seconds
setInterval(() => { 
  loadStats(); 
  loadOverview(); 
}, 5000);
</script>
</body>
</html>