<?php
require_once __DIR__ . '/includes/functions.php';

$number = (string)($_GET['o'] ?? '');

// Sirf wohi order dikhao jo isi browser session ne abhi diya hai
if ($number === '' || ($_SESSION['last_order'] ?? '') !== $number) {
    redirect('');
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = ?');
$stmt->execute([$number]);
$order = $stmt->fetch();
if (!$order) redirect('');

$stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute([$order['id']]);
$items = $stmt->fetchAll();

$msg = "Assalam o Alaikum RoohVeere! Maine order place kiya hai.\n"
     . "Order: {$order['order_number']}\n"
     . "Naam: {$order['customer_name']}\n"
     . "Total: " . money((int)$order['total']) . "\n"
     . "Meherbani karke confirm kar dein.";

$pageTitle = 'Order Placed | RoohVeere';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container success-box">
    <div class="tick">✓</div>
    <h1>Shukriya, <?= e(explode(' ', $order['customer_name'])[0]) ?>!</h1>
    <p class="muted">Aap ka order mil gaya hai. Hum jald aap se raabta karenge.</p>
    <p class="order-no">Order number: <b><?= e($order['order_number']) ?></b></p>

    <div class="summary" style="position:static;text-align:left;margin:28px 0">
      <?php foreach ($items as $it): ?>
        <div class="sum-row"><span><?= e($it['product_name']) ?> × <?= (int)$it['quantity'] ?></span><span><?= money((int)$it['price'] * (int)$it['quantity']) ?></span></div>
      <?php endforeach; ?>
      <div class="sum-row" style="border-top:1px solid var(--line);margin-top:8px"><span>Subtotal</span><span><?= money((int)$order['subtotal']) ?></span></div>
      <?php if ($order['discount'] > 0): ?>
        <div class="sum-row green"><span>Bundle Discount</span><span>- <?= money((int)$order['discount']) ?></span></div>
      <?php endif; ?>
      <div class="sum-row total"><span>Total (COD)</span><span><?= money((int)$order['total']) ?></span></div>
      <p class="muted" style="font-size:13px;margin-top:12px">
        <?= e($order['customer_name']) ?> · <?= e($order['phone']) ?><br>
        <?= e($order['address']) ?>, <?= e($order['city']) ?>
      </p>
    </div>

    <a href="<?= e(waLink($msg)) ?>" target="_blank" rel="noopener" class="btn">WhatsApp pe confirm karein</a>
    <a href="<?= BASE_URL ?>/" class="btn btn-outline">Shopping jaari rakhein</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>