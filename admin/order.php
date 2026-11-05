<?php
require_once __DIR__ . '/_layout.php';

$num = (string)($_GET['o'] ?? '');
$st = $pdo->prepare('SELECT * FROM orders WHERE order_number = ?');
$st->execute([$num]);
$order = $st->fetch();
if (!$order) { flash('error', 'Order nahi mila.'); header('Location: ' . BASE_URL . '/admin/orders.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $new = (string)($_POST['status'] ?? '');
    $old = $order['status'];
    if (!in_array($new, ORDER_STATUSES, true) || $new === $old) {
        flash('info', 'Status mein koi tabdeeli nahi.');
    } elseif ($old === 'cancelled') {
        flash('error', 'Cancel hua order dobara active nahi ho sakta (stock wapis ja chuka hai).');
    } else {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$new, $order['id']]);
        if ($new === 'cancelled') {   // cancel par stock wapis
            $pdo->prepare('UPDATE products p JOIN order_items oi ON oi.product_id = p.id
                           SET p.stock = p.stock + oi.quantity WHERE oi.order_id = ?')->execute([$order['id']]);
        }
        $pdo->commit();
        flash('success', 'Status "' . $new . '" kar diya.');
    }
    header('Location: ' . BASE_URL . '/admin/order.php?o=' . urlencode($num));
    exit;
}

$st = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$st->execute([$order['id']]);
$items = $st->fetchAll();

adminHead('Order ' . $order['order_number'], 'orders');
?>
<p style="margin-bottom:18px"><a class="link" href="orders.php">← Saare orders</a></p>
<div class="panels">
  <div class="panel">
    <h3>Items</h3>
    <?php foreach ($items as $it): ?>
      <div class="list-row"><span><?= e($it['product_name']) ?> × <?= (int)$it['quantity'] ?></span><span><?= money((int)$it['price'] * (int)$it['quantity']) ?></span></div>
    <?php endforeach; ?>
    <div class="list-row"><span>Subtotal</span><span><?= money((int)$order['subtotal']) ?></span></div>
    <?php if ($order['discount'] > 0): ?><div class="list-row" style="color:#1e6b2e"><span>Bundle Discount</span><span>- <?= money((int)$order['discount']) ?></span></div><?php endif; ?>
    <div class="list-row"><b>Total (<?= e(strtoupper($order['payment_method'])) ?>)</b><b><?= money((int)$order['total']) ?></b></div>
    <?php if ($order['notes']): ?><p style="margin-top:14px"><b>Notes:</b> <?= nl2br(e($order['notes'])) ?></p><?php endif; ?>
  </div>
  <div class="panel">
    <h3>Customer</h3>
    <p><b><?= e($order['customer_name']) ?></b></p>
    <p><?= e($order['phone']) ?><?= $order['email'] ? '<br>' . e($order['email']) : '' ?></p>
    <p style="margin-top:8px"><?= nl2br(e($order['address'])) ?>, <?= e($order['city']) ?></p>
    <p class="muted" style="margin-top:8px;font-size:13px"><?= e(date('d M Y, h:i A', strtotime($order['created_at']))) ?></p>
    <p style="margin-top:10px"><a class="link" target="_blank" rel="noopener" href="https://wa.me/92<?= e(substr((string)$order['phone'], 1)) ?>">WhatsApp customer ↗</a></p>

    <h3 style="margin-top:24px">Status: <?= statusBadge($order['status']) ?></h3>
    <?php if ($order['status'] !== 'cancelled'): ?>
    <form method="post" class="inline-form" style="margin-top:6px">
      <?= csrf_field() ?>
      <select name="status" style="padding:9px;border:1px solid var(--line)">
        <?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $s === $order['status'] ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
      </select>
      <button class="btn" type="submit">Update</button>
    </form>
    <p class="muted" style="font-size:12px;margin-top:8px">Cancel karne par stock khud wapis add ho jata hai.</p>
    <?php endif; ?>
  </div>
</div>
<?php adminFoot();
