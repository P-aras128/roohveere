<?php
require_once __DIR__ . '/../includes/functions.php';
if (!empty($_SESSION['admin_id'])) { header('Location: ' . BASE_URL . '/admin/index.php'); exit; }

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');
    if (loginLocked('admin')) {
        $error = 'Bohat zyada koshishein. Ek minute baad dobara try karein.';
    } else {
        $st = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
        $st->execute([$username]);
        $a = $st->fetch();
        if ($a && password_verify($pass, $a['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$a['id'];
            unset($_SESSION['fails_admin']);
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        }
        loginFailed('admin');
        $error = 'Username ya password sahi nahi hai.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | RoohVeere</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/assets/admin.css">
</head><body>
<div class="login-wrap">
  <form method="post" class="checkout-form">
    <?= csrf_field() ?>
    <h1 style="font-size:30px">Admin Login</h1>
    <p class="muted" style="margin-bottom:20px">RoohVeere control panel</p>
    <?php if ($error): ?><div class="flash flash-error" style="margin-bottom:16px"><?= e($error) ?></div><?php endif; ?>
    <label>Username<input type="text" name="username" value="<?= e($username) ?>" required autofocus></label>
    <label>Password<input type="password" name="password" required></label>
    <button type="submit" class="btn btn-block">Login</button>
  </form>
</div>
</body></html>
