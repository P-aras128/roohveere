<?php
// Admin password reset: config.php ki ADMIN_RECOVERY_KEY se
require_once __DIR__ . '/../includes/functions.php';

$msg = ''; $done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u    = trim((string)($_POST['username'] ?? ''));
    $key  = (string)($_POST['key'] ?? '');
    $p    = (string)($_POST['password'] ?? '');
    $p2   = (string)($_POST['password2'] ?? '');

    if (strlen(ADMIN_RECOVERY_KEY) < 12) {
        $msg = 'Recovery key set nahi hai. includes/config.php me ADMIN_RECOVERY_KEY likhein (kam az kam 12 huroof).';
    } elseif (loginLocked('forgot')) {
        $msg = 'Bohat zyada koshishein. Ek minute baad dobara try karein.';
    } elseif (strlen($p) < 8) {
        $msg = 'Naya password kam az kam 8 huroof ka ho.';
    } elseif ($p !== $p2) {
        $msg = 'Dono password ek jaise nahi hain.';
    } else {
        $st = $pdo->prepare('SELECT id FROM admins WHERE username = ?');
        $st->execute([$u]);
        $a = $st->fetch();
        if ($a && hash_equals(ADMIN_RECOVERY_KEY, $key)) {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($p, PASSWORD_DEFAULT), $a['id']]);
            unset($_SESSION['fails_forgot']);
            $done = true;
        } else {
            loginFailed('forgot');
            $msg = 'Username ya recovery key sahi nahi hai.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Password Reset | RoohVeere Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/assets/admin.css">
</head><body>
<div class="login-wrap">
<?php if ($done): ?>
  <div class="checkout-form"><h1 style="font-size:28px">Password badal gaya ✓</h1>
    <p style="margin:14px 0">Ab naye password se login karein.</p>
    <a class="btn btn-block" href="<?= BASE_URL ?>/admin/login.php">Admin Login</a></div>
<?php else: ?>
  <form method="post" class="checkout-form"><?= csrf_field() ?>
    <h1 style="font-size:28px">Password Reset</h1>
    <p class="muted" style="margin-bottom:18px">Config file wali recovery key chahiye.</p>
    <?php if ($msg): ?><div class="flash flash-error" style="margin-bottom:14px"><?= e($msg) ?></div><?php endif; ?>
    <label>Admin username<input type="text" name="username" required></label>
    <label>Recovery key<input type="password" name="key" required></label>
    <label>Naya password (8+ huroof)<input type="password" name="password" required></label>
    <label>Naya password dobara<input type="password" name="password2" required></label>
    <button class="btn btn-block" type="submit">Password badlein</button>
    <p class="auth-alt"><a href="<?= BASE_URL ?>/admin/login.php">← Login par wapis</a></p>
  </form>
<?php endif; ?>
</div></body></html> 