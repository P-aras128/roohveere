<?php
// Pehla admin banane ke liye. Sirf tab chalta hai jab admins table khali ho.
// Admin ban jaye to ye file DELETE kar dein.
require_once __DIR__ . '/../includes/functions.php';

if ((int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) {
    http_response_code(403);
    die('Admin pehle se bana hua hai. Is file (admin/setup.php) ko delete kar dein.');
}

$msg = ''; $done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim((string)($_POST['username'] ?? ''));
    $p = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $u)) $msg = 'Username 3-50 huroof ka ho (sirf a-z, 0-9, _ . -).';
    elseif (strlen($p) < 8) $msg = 'Password kam az kam 8 huroof ka ho.';
    else {
        $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
        $done = true;
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Setup</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/assets/admin.css"></head><body>
<div class="login-wrap">
<?php if ($done): ?>
  <div class="checkout-form"><h1 style="font-size:28px">Admin ban gaya ✓</h1>
    <p style="margin:14px 0">Ab <b>admin/setup.php</b> file delete kar dein, phir login karein.</p>
    <a class="btn btn-block" href="<?= BASE_URL ?>/admin/login.php">Admin Login</a></div>
<?php else: ?>
  <form method="post" class="checkout-form"><?= csrf_field() ?>
    <h1 style="font-size:28px;margin-bottom:16px">Pehla Admin Banayein</h1>
    <?php if ($msg): ?><div class="flash flash-error" style="margin-bottom:14px"><?= e($msg) ?></div><?php endif; ?>
    <label>Username<input type="text" name="username" required></label>
    <label>Password (8+ huroof)<input type="password" name="password" required></label>
    <button class="btn btn-block" type="submit">Create Admin</button>
  </form>
<?php endif; ?>
</div></body></html>
