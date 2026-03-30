<?php
include 'config.php';

if (isLoggedIn()) {
    $dest = ['admin' => 'admin.php', 'manager' => 'manager.php', 'user' => 'index.php'];
    header("Location: " . ($dest[getRole()] ?? 'index.php'));
    exit;
}

$error         = '';
$selected_role = $_POST['role'] ?? 'user';

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
    'user'    => 'User',
    'manager' => 'Manager',
    'admin'   => 'Admin',
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
:root{--surface:#1e293b;--border:#334155;--text:#f1f5f9;--muted:#94a3b8;--user:#3b82f6;--manager:#10b981;--admin:#8b5cf6;}

body {
  margin: 0;
  padding: 0;
  background: url('Waterbg.jpg') center/cover fixed no-repeat;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}

/* Light overlay for text readability */
body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  pointer-events: none;
  z-index: 0;
}

.form-card {
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 32px;
  width: 100%;
  max-width: 400px;
  position: relative;
  z-index: 1;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
  text-align: center;
}

.logo {
  margin-bottom: 20px;
}

.logo img {
  max-width: 80px;
  height: auto;
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
}

.brand {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: white;
}

.brand span {
  color: #38bdf8;
}

.subtitle {
  color: rgba(255,255,255,0.7);
  font-size: .85rem;
  text-align: center;
  margin-bottom: 24px;
}

label {
  display: block;
  font-size: .82rem;
  color: rgba(255,255,255,0.9);
  margin-bottom: 6px;
  font-weight: 500;
  text-align: left;
}

input, select {
  width: 100%;
  background: rgba(0, 0, 0, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 11px 14px;
  border-radius: 8px;
  font-size: .95rem;
  margin-bottom: 16px;
  outline: none;
  font-family: inherit;
  cursor: pointer;
}

select option {
  background: #1e293b;
  color: white;
}

input:focus, select:focus {
  border-color: #38bdf8;
  background: rgba(0, 0, 0, 0.8);
  box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
}

input::placeholder {
  color: rgba(255,255,255,0.5);
}

.btn-login {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  font-size: .95rem;
  cursor: pointer;
  transition: all .2s;
  font-family: inherit;
  background: #3b82f6;
  color: #fff;
}

.btn-login:hover {
  opacity: .88;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.error {
  background: rgba(239,68,68,.2);
  color: #fca5a5;
  border: 1px solid rgba(239,68,68,.3);
  border-radius: 8px;
  padding: 10px 14px;
  font-size: .85rem;
  margin-bottom: 16px;
  text-align: center;
}
</style>
</head>
<body>
<div class="form-card">
  <div class="logo">
    <img src="WaterleakLG.png" alt="WaterGuard Logo">
  </div>
  <div class="brand">Water<span>Guard</span></div>
  <p class="subtitle">Select your role to sign in</p>
  
  <?php if ($error): ?><div class="error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
  
  <form method="POST">
    <label>Role</label>
    <select name="role" required>
      <?php foreach ($roles as $key => $label): ?>
        <option value="<?= $key ?>" <?= $selected_role === $key ? 'selected' : '' ?>>
          <?= $label ?>
        </option>
      <?php endforeach; ?>
    </select>
    
    <label>Username</label>
    <input type="text" name="username" required autofocus placeholder="Enter username">
    
    <label>Password</label>
    <input type="password" name="password" required placeholder="••••••••">
    
    <button type="submit" class="btn-login">Sign In</button>
  </form>
</div>
</body>
</html>