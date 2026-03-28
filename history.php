<?php
include 'config.php';
requireRole('user', 'manager', 'admin');
$back  = ['admin' => 'admin.php', 'manager' => 'manager.php', 'user' => 'index.php'];
$home  = $back[getRole()] ?? 'index.php';
$color = ['admin' => '#8b5cf6', 'manager' => '#10b981', 'user' => '#3b82f6'][getRole()] ?? '#3b82f6';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>WaterGuard — History</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#f8fafc;font-family:'DM Sans',sans-serif;color:#0f172a;}
header{background:#1e293b;color:#fff;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;}
.brand{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;}
.container{max-width:900px;margin:28px auto;padding:0 20px;}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;}
.card h2{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;margin-bottom:18px;}
table{width:100%;border-collapse:collapse;}
th{background:#f1f5f9;padding:10px 14px;text-align:left;font-size:.78rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;}
td{padding:11px 14px;font-size:.86rem;border-bottom:1px solid #f1f5f9;}
.badge{padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-safe{background:#dcfce7;color:#15803d;}
.badge-leak{background:#fee2e2;color:#dc2626;}
.back-link{display:inline-block;margin-bottom:16px;font-size:.85rem;color:<?= $color ?>;text-decoration:none;font-weight:600;}
.back-link:hover{text-decoration:underline;}
.note{font-size:.78rem;color:#94a3b8;margin-left:8px;font-weight:400;}
</style>
</head>
<body>
<header>
  <div class="brand">💧 WaterGuard — Leak History</div>
  <a href="logout.php" style="color:#94a3b8;font-size:.8rem;text-decoration:underline;">Logout</a>
</header>
<div class="container">
  <a class="back-link" href="<?= $home ?>">← Back to Dashboard</a>
  <div class="card">
    <h2>📋 Leak History <span class="note"><?= isUser() ? 'Last 10 entries' : 'Last 50 entries' ?></span></h2>
    <table>
      <thead><tr><th>#</th><th>Status</th><th>Source</th><th>Date &amp; Time</th></tr></thead>
      <tbody id="logBody"><tr><td colspan="4" style="text-align:center;color:#94a3b8;">Loading…</td></tr></tbody>
    </table>
  </div>
</div>
<script>
fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=get_logs' })
.then(r => r.json()).then(d => {
  if (!d.success) return;
  document.getElementById('logBody').innerHTML = d.logs.length
    ? d.logs.map(l => `<tr>
        <td>${l.id}</td>
        <td><span class="badge badge-${l.status.toLowerCase()}">${l.status}</span></td>
        <td>${l.source}</td>
        <td>${new Date(l.created_at).toLocaleString()}</td>
      </tr>`).join('')
    : '<tr><td colspan="4" style="text-align:center;color:#94a3b8;">No logs yet.</td></tr>';
});
</script>
</body>
</html>
