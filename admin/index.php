<?php
require_once __DIR__ . '/_layout.php';

$s = $pdo->query("SELECT COUNT(*) total,
    COALESCE(SUM(status='pending'),0) pending,
    COALESCE(SUM(DATE(created_at)=CURDATE()),0) today,
    COALESCE(SUM(CASE WHEN status<>'cancelled' THEN total END),0) revenue
  FROM orders")->fetch();
$customers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$low = $pdo->query('SELECT name, stock FROM products WHERE is_active = 1 AND stock <= 5 ORDER BY stock')->fetchAll();

// Pichle 7 din ke orders
$days = [];
for ($i = 6; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i day"))] = 0;
foreach ($pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM orders WHERE created_at >= CURDATE() - INTERVAL 6 DAY GROUP BY d") as $r) {
    if (isset($days[$r['d']])) $days[$r['d']] = (int)$r['c'];
}
$maxDay = max(1, max($days));

$recent = $pdo->query('SELECT order_number, customer_name, total, status, created_at FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
$top = $pdo->query("SELECT oi.product_name, SUM(oi.quantity) q FROM order_items oi JOIN orders o ON o.id = oi.order_id
   WHERE o.status <> 'cancelled' GROUP BY oi.product_name ORDER BY q DESC LIMIT 5")->fetchAll();

adminHead('Dashboard', 'dashboard');
?>
<div class="stats">
  <div class="stat"><span>Total Orders</span><b><?= (int)$s['total'] ?></b></div>
  <div class="stat"><span>Aaj ke Orders</span><b><?= (int)$s['today'] ?></b></div>
  <div class="stat <?= $s['pending'] ? 'warn' : '' ?>"><span>Pending</span><b><?= (int)$s['pending'] ?></b></div>
  <div class="stat"><span>Revenue</span><b><?= money((int)$s['revenue']) ?></b></div>
  <div class="stat"><span>Customers</span><b><?= $customers ?></b></div>
</div>

<div class="panels">
  <div class="panel">
    <h3>Orders (pichle 7 din)</h3>
    <div class="bars">
      <?php foreach ($days as $d => $c): ?>
        <div class="bar"><em><?= $c ?></em><i style="height:<?= round($c / $maxDay * 100) ?>%"></i><?= e(date('D', strtotime($d))) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="panel">
    <h3>Top Selling</h3>
    <?php foreach ($top as $t): ?>
      <div class="list-row"><span><?= e($t['product_name']) ?></span><b><?= (int)$t['q'] ?> sold</b></div>
    <?php endforeach; ?>
    <?php if (!$top): ?><p class="muted">Abhi koi sale nahi.</p><?php endif; ?>
  </div>
</div>

<div class="panels">
  <div class="panel">
    <h3>Recent Orders</h3>
    <div class="table-wrap" style="border:0">
      <table class="tbl">
        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recent as $o): ?>
          <tr>
            <td><b><?= e($o['order_number']) ?></b></td><td><?= e($o['customer_name']) ?></td>
            <td><?= money((int)$o['total']) ?></td><td><?= statusBadge($o['status']) ?></td>
            <td><a class="link" href="order.php?o=<?= urlencode($o['order_number']) ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?><tr><td colspan="5" class="muted">Abhi koi order nahi aya.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <p style="margin-top:12px"><a class="link" href="orders.php">Saare orders →</a></p>
  </div>
  <div class="panel">
    <h3>Low Stock (5 ya kam)</h3>
    <?php foreach ($low as $p): ?>
      <div class="list-row"><span><?= e($p['name']) ?></span><b style="color:#9b2c1f"><?= (int)$p['stock'] ?> left</b></div>
    <?php endforeach; ?>
    <?php if (!$low): ?><p class="muted">Sab products ka stock theek hai.</p><?php endif; ?>
  </div>
</div>
<?php adminFoot();
