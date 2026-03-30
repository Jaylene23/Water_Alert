<?php
include 'config.php';

if (isLoggedIn()) {
    $dest = ['admin' => 'admin.php', 'manager' => 'manager.php', 'user' => 'index.php'];
    header("Location: " . ($dest[getRole()] ?? 'index.php'));
    exit;
}

$error         = '';
$selected_role = $_GET['role'] ?? $_POST['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uname = trim($_POST['username'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $role  = trim($_POST['role'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $uname, $role);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        if ($pass === $user['password']) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['uid']  = $user['id'];
            $dest = ['admin' => 'admin.php', 'manager' => 'manager.php', 'user' => 'index.php'];
            header("Location: " . ($dest[$user['role']] ?? 'index.php'));
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found for that username and role.";
    }
}

$roles = [
    'user'    => ['label' => 'User',    'icon' => '👤', 'desc' => 'Monitor tank status'],
    'manager' => ['label' => 'Manager', 'icon' => '🛠️', 'desc' => 'Manage & control system'],
    'admin'   => ['label' => 'Admin',   'icon' => '🔐', 'desc' => 'System administration'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>WaterGuard — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f172a;--surface:#1e293b;--border:#334155;--text:#f1f5f9;--muted:#94a3b8;--user:#3b82f6;--manager:#10b981;--admin:#8b5cf6;}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;}
.bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;}
.logo{font-family:'Space Grotesk',sans-serif;font-size:1.6rem;font-weight:700;margin-bottom:8px;}
.logo span{color:#38bdf8;}
.subtitle{color:var(--muted);font-size:.9rem;margin-bottom:32px;}
.role-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;width:100%;max-width:480px;margin-bottom:28px;}
.role-btn{background:var(--surface);border:2px solid var(--border);border-radius:12px;padding:16px 12px;cursor:pointer;text-align:center;transition:all .2s;text-decoration:none;color:var(--text);}
.role-btn:hover{transform:translateY(-2px);}
.role-btn[data-role="user"]{color:var(--user);}
.role-btn[data-role="manager"]{color:var(--manager);}
.role-btn[data-role="admin"]{color:var(--admin);}
.role-btn.active[data-role="user"]{border-color:var(--user);box-shadow:0 0 0 3px rgba(59,130,246,.2);}
.role-btn.active[data-role="manager"]{border-color:var(--manager);box-shadow:0 0 0 3px rgba(16,185,129,.2);}
.role-btn.active[data-role="admin"]{border-color:var(--admin);box-shadow:0 0 0 3px rgba(139,92,246,.2);}
.role-icon{font-size:1.6rem;display:block;margin-bottom:6px;}
.role-label{font-weight:700;font-size:.85rem;display:block;}
.role-desc{font-size:.72rem;color:var(--muted);display:block;margin-top:2px;}
.form-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:380px;}
.form-card h3{font-family:'Space Grotesk',sans-serif;font-size:1.1rem;margin-bottom:4px;}
.role-tag{font-size:.78rem;padding:3px 10px;border-radius:20px;display:inline-block;margin-bottom:20px;font-weight:600;}
.tag-user{background:rgba(59,130,246,.15);color:var(--user);}
.tag-manager{background:rgba(16,185,129,.15);color:var(--manager);}
.tag-admin{background:rgba(139,92,246,.15);color:var(--admin);}
label{display:block;font-size:.82rem;color:var(--muted);margin-bottom:6px;font-weight:500;}
input{width:100%;background:#0f172a;border:1px solid var(--border);color:var(--text);padding:11px 14px;border-radius:8px;font-size:.95rem;margin-bottom:16px;outline:none;font-family:inherit;}
input:focus{border-color:#38bdf8;}
.btn-login{width:100%;padding:12px;border:none;border-radius:8px;font-weight:700;font-size:.95rem;cursor:pointer;transition:opacity .2s;font-family:inherit;}
.btn-user{background:var(--user);color:#fff;}
.btn-manager{background:var(--manager);color:#fff;}
.btn-admin{background:var(--admin);color:#fff;}
.btn-login:hover{opacity:.88;}
.error{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:10px 14px;font-size:.85rem;margin-bottom:16px;}
.no-role{color:var(--muted);text-align:center;font-size:.9rem;padding:20px 0;}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="logo">💧 Water<span>Guard</span></div>
<p class="subtitle">Select your role to sign in</p>

<div class="role-grid">
<?php foreach ($roles as $key => $r): ?>
  <a href="?role=<?= $key ?>" class="role-btn<?= ($selected_role === $key) ? ' active' : '' ?>" data-role="<?= $key ?>">
    <span class="role-icon"><?= $r['icon'] ?></span>
    <span class="role-label"><?= $r['label'] ?></span>
    <span class="role-desc"><?= $r['desc'] ?></span>
  </a>
<?php endforeach; ?>
</div>

<?php if ($selected_role && isset($roles[$selected_role])):
    $r = $roles[$selected_role]; ?>
<div class="form-card">
  <h3>Sign in as <?= $r['label'] ?></h3>
  <span class="role-tag tag-<?= $selected_role ?>"><?= $r['icon'] ?> <?= $r['label'] ?></span>
  <?php if ($error): ?><div class="error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="role" value="<?= $selected_role ?>">
    <label>Username</label>
    <input type="text" name="username" required autofocus placeholder="Enter username">
    <label>Password</label>
    <input type="password" name="password" required placeholder="••••••••">
    <button type="submit" class="btn-login btn-<?= $selected_role ?>">Sign In</button>
  </form>
</div>
<?php else: ?>
<div class="form-card"><p class="no-role">👆 Choose a role above to continue</p></div>
<?php endif; ?>
</body>
</html>
