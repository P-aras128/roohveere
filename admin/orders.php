<?php
require_once __DIR__ . '/_layout.php';

$status = (string)($_GET['status'] ?? '');
$q      = trim((string)($_GET['q'] ?? ''));
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 20;

$where = []; $args = [];
if (in_array($status, ORDER_STATUSES, true)) { $where[] = 'status = ?'; $args[] = $status; }
if ($q !== '') {
    $where[] = '(order_number LIKE ? OR customer_name LIKE ? OR phone LIKE ?)';
    array_push($args, "%$q%", "%$q%", "%$q%");
}
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare("SELECT COUNT(*) FROM orders $w");
$st->execute($args);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);
$off   = ($page - 1) * $per;

$st = $pdo->prepare("SELECT order_number, customer_name, phone, city, total, status, created_at FROM orders $w ORDER BY id DESC LIMIT $per OFFSET $off");
$st->execute($args);
$orders = $st->fetchAll();

adminHead('Orders (' . $total . ')', 'orders');
?>
<form class="toolbar" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Order no, naam ya phone">
  <select name="status">
    <option value="">Saare status</option>
    <?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Filter</button>
</form>
<div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Phone</th><th>City</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><b><?= e($o['order_number']) ?></b></td>
        <td><?= e(date('d M, h:i A', strtotime($o['created_at']))) ?></td>
        <td><?= e($o['customer_name']) ?></td><td><?= e($o['phone']) ?></td><td><?= e($o['city']) ?></td>
        <td><?= money((int)$o['total']) ?></td><td><?= statusBadge($o['status']) ?></td>
        <td><a class="link" href="order.php?o=<?= urlencode($o['order_number']) ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?><tr><td colspan="8" class="muted">Koi order nahi mila.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?= pager($page, $pages, ['q' => $q, 'status' => $status]) ?>
<?php adminFoot();
