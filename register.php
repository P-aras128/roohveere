<?php
require_once __DIR__ . '/includes/functions.php';
if (currentUser($pdo)) redirect('account.php');

$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($old as $k => $_) $old[$k] = trim((string)($_POST[$k] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');

    if (mb_strlen($old['name']) < 3 || mb_strlen($old['name']) > 100) $errors['name'] = 'Poora naam likhein (kam az kam 3 huroof).';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Sahi email likhein.';
    $phone = null;
    if ($old['phone'] !== '') {
        $phone = normalizePhone($old['phone']);
        if (!$phone) $errors['phone'] = 'Sahi mobile number likhein, jaise 03XX XXXXXXX.';
    }
    if (strlen($pass) < 8) $errors['password'] = 'Password kam az kam 8 huroof ka ho.';
    elseif ($pass !== $pass2) $errors['password2'] = 'Dono password ek jaise nahi hain.';

    if (!$errors) {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([strtolower($old['email'])]);
        if ($st->fetch()) $errors['email'] = 'Is email se account pehle se maujood hai. Login karein.';
    }

    if (!$errors) {
        $pdo->prepare('INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)')
            ->execute([$old['name'], strtolower($old['email']), $phone, password_hash($pass, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$pdo->lastInsertId();
        flash('success', 'Account ban gaya. Khush amdeed!');
        redirect(!empty($_SESSION['cart']) ? 'checkout.php' : 'account.php');
    }
}

function fe(array $errors, string $k): string {
    return isset($errors[$k]) ? '<small class="field-error">' . e($errors[$k]) . '</small>' : '';
}
$pageTitle = 'Create Account | RoohVeere';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <form method="post" class="auth-card checkout-form" novalidate>
      <?= csrf_field() ?>
      <h1>Create Account</h1>
      <p class="muted" style="margin-bottom:22px">Apne orders track karein aur checkout tez karein.</p>
      <label>Poora naam *<input type="text" name="name" value="<?= e($old['name']) ?>" required><?= fe($errors, 'name') ?></label>
      <label>Email *<input type="email" name="email" value="<?= e($old['email']) ?>" required><?= fe($errors, 'email') ?></label>
      <label>Mobile (optional)<input type="tel" name="phone" value="<?= e($old['phone']) ?>" placeholder="03XX XXXXXXX"><?= fe($errors, 'phone') ?></label>
      <label>Password * (kam az kam 8 huroof)<input type="password" name="password" required><?= fe($errors, 'password') ?></label>
      <label>Password dobara *<input type="password" name="password2" required><?= fe($errors, 'password2') ?></label>
      <button type="submit" class="btn btn-block">Create Account</button>
      <p class="auth-alt">Pehle se account hai? <a href="<?= BASE_URL ?>/login.php">Login</a></p>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
