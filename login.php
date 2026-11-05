<?php
require_once __DIR__ . '/includes/functions.php';
if (currentUser($pdo)) redirect('account.php');

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if (loginLocked('user')) {
        $error = 'Bohat zyada koshishein. Ek minute baad dobara try karein.';
    } else {
        $st = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $st->execute([strtolower($email)]);
        $u = $st->fetch();
        if ($u && password_verify($pass, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            unset($_SESSION['fails_user']);
            flash('success', 'Login ho gaye.');
            redirect(!empty($_SESSION['cart']) ? 'cart.php' : 'account.php');
        }
        loginFailed('user');
        $error = 'Email ya password sahi nahi hai.';
    }
}

$pageTitle = 'Login | RoohVeere';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <form method="post" class="auth-card checkout-form">
      <?= csrf_field() ?>
      <h1>Login</h1>
      <p class="muted" style="margin-bottom:22px">Apne account ma dakhil hon.</p>
      <?php if ($error): ?><div class="flash flash-error" style="margin-bottom:16px"><?= e($error) ?></div><?php endif; ?>
      <label>Email<input type="email" name="email" value="<?= e($email) ?>" required autofocus></label>
      <label>Password<input type="password" name="password" required></label>
      <button type="submit" class="btn btn-block">Login</button>
      <p class="auth-alt"><a href="<?= BASE_URL ?>/admin/forgot.php">Password bhool gaye?</a></p> 
      <p class="auth-alt">Naye hain? <a href="<?= BASE_URL ?>/register.php">Account banayein</a></p>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
