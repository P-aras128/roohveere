<?php
require_once __DIR__ . '/includes/functions.php';
$user = requireUser($pdo);

$st = $pdo->prepare('SELECT * FROM orders WHERE order_number = ? AND user_id = ?');
$st->execute([(string)($_GET['o'] ?? ''), $user['id']]);
$order = $st->fetch();
if (!$order) redirect('account.php');

$st = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$st->execute([$order['id']]);
$items = $st->fetchAll();

$pageTitle = 'Order ' . $order['order_number'] . ' | RoohVeere';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container success-box" style="text-align:left">
    <p class="crumbs"><a href="<?= BASE_URL ?>/account.php">← My Account</a></p>
    <h1>Order <?= e($order['order_number']) ?></h1>
    <p style="margin:8px 0 22px"><?= statusBadge($order['status']) ?> <span class="muted">· <?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></span></p>
    <div class="summary" style="position:static">
      <?php foreach ($items as $it): ?>
        <div class="sum-row"><span><?= e($it['product_name']) ?> × <?= (int)$it['quantity'] ?></span><span><?= money((int)$it['price'] * (int)$it['quantity']) ?></span></div>
      <?php endforeach; ?>
      <div class="sum-row" style="border-top:1px solid var(--line);margin-top:8px"><span>Subtotal</span><span><?= money((int)$order['subtotal']) ?></span></div>
      <?php if ($order['discount'] > 0): ?>
        <div class="sum-row green"><span>Bundle Discount</span><span>- <?= money((int)$order['discount']) ?></span></div>
      <?php endif; ?>
      <div class="sum-row total"><span>Total (COD)</span><span><?= money((int)$order['total']) ?></span></div>
      <p class="muted" style="font-size:13px;margin-top:12px"><?= e($order['customer_name']) ?> · <?= e($order['phone']) ?><br><?= e($order['address']) ?>, <?= e($order['city']) ?></p>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
