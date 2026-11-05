<?php
require_once __DIR__ . '/includes/functions.php';
$user = requireUser($pdo);

$st = $pdo->prepare('SELECT order_number, total, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$st->execute([$user['id']]);
$orders = $st->fetchAll();

$pageTitle = 'My Account | RoohVeere';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="account-head">
      <div>
        <h1>My Account</h1>
        <p class="muted"><?= e($user['name']) ?> · <?= e($user['email']) ?><?= $user['phone'] ? ' · ' . e($user['phone']) : '' ?></p>
      </div>
      <form action="<?= BASE_URL ?>/logout.php" method="post"><?= csrf_field() ?><button class="btn btn-outline" type="submit">Logout</button></form>
    </div>

    <h3 style="margin:36px 0 14px">My Orders</h3>
    <?php if (!$orders): ?>
      <p class="muted">Abhi tak koi order nahi. <a href="<?= BASE_URL ?>/#collection" style="color:var(--gold)">Shop karein →</a></p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="tbl">
          <thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><b><?= e($o['order_number']) ?></b></td>
              <td><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
              <td><?= money((int)$o['total']) ?></td>
              <td><?= statusBadge($o['status']) ?></td>
              <td><a href="<?= BASE_URL ?>/account_order.php?o=<?= urlencode($o['order_number']) ?>" style="color:var(--gold)">Details</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
