<?php
require_once __DIR__ . '/_layout.php';
$me = (int)$_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = (string)($_POST['action'] ?? '');
    $id  = (int)($_POST['id'] ?? 0);
    $pass = (string)($_POST['password'] ?? '');

    if ($act === 'add') {
        $u = trim((string)($_POST['username'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $u)) {
            flash('error', 'Username 3-50 huroof ka ho (sirf a-z, 0-9, _ . -).');
        } elseif (strlen($pass) < 8) {
            flash('error', 'Password kam az kam 8 huroof ka ho.');
        } else {
            $st = $pdo->prepare('SELECT id FROM admins WHERE username = ?');
            $st->execute([$u]);
            if ($st->fetch()) {
                flash('error', 'Ye username pehle se maujood hai.');
            } else {
                $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
                    ->execute([$u, password_hash($pass, PASSWORD_DEFAULT)]);
                flash('success', 'Naya admin "' . $u . '" ban gaya.');
            }
        }
    } elseif ($act === 'reset') {
        if (strlen($pass) < 8) {
            flash('error', 'Naya password kam az kam 8 huroof ka ho.');
        } else {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
            flash('success', 'Password badal diya.');
        }
    } elseif ($act === 'delete') {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        if ($id === $me) {
            flash('error', 'Aap apna khud ka account delete nahi kar sakte.');
        } elseif ($count <= 1) {
            flash('error', 'Aakhri admin delete nahi ho sakta.');
        } else {
            $pdo->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
            flash('success', 'Admin delete ho gaya.');
        }
    }
    header('Location: ' . BASE_URL . '/admin/admins.php');
    exit;
}

$admins = $pdo->query('SELECT id, username, created_at FROM admins ORDER BY id')->fetchAll();
adminHead('Admins (' . count($admins) . ')', 'admins');
?>
<div class="panel" style="margin-bottom:22px">
  <h3>Naya Admin Add Karein</h3>
  <form method="post" class="inline-form" style="flex-wrap:wrap">
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <input type="text" name="username" placeholder="Username" required style="padding:9px;border:1px solid var(--line)">
    <input type="password" name="password" placeholder="Password (8+ huroof)" required style="padding:9px;border:1px solid var(--line);width:200px">
    <button class="btn" type="submit">Add Admin</button>
  </form>
</div>

<div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>Username</th><th>Bana</th><th>Naya password</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($admins as $a): ?>
      <tr>
        <td><b><?= e($a['username']) ?></b><?= (int)$a['id'] === $me ? ' <span class="badge badge-delivered">Aap</span>' : '' ?></td>
        <td><?= e(date('d M Y', strtotime($a['created_at']))) ?></td>
        <td>
          <form method="post" class="inline-form"><?= csrf_field() ?>
            <input type="hidden" name="action" value="reset"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <input type="password" name="password" placeholder="Naya password" required style="width:160px;padding:6px 8px;border:1px solid var(--line)">
            <button class="btn btn-outline" type="submit">Badlein</button>
          </form>
        </td>
        <td>
          <?php if ((int)$a['id'] !== $me): ?>
          <form method="post" class="inline-form" onsubmit="return confirm('Is admin ko delete karein?')"><?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <button class="btn btn-outline" type="submit" style="color:#9b2c1f">Delete</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php adminFoot(); 