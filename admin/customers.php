<?php
require_once __DIR__ . '/_layout.php';
$rows = $pdo->query('SELECT u.name, u.email, u.phone, u.created_at, COUNT(o.id) orders, COALESCE(SUM(CASE WHEN o.status<>"cancelled" THEN o.total END),0) spent
                     FROM users u LEFT JOIN orders o ON o.user_id = u.id GROUP BY u.id ORDER BY u.id DESC')->fetchAll();
adminHead('Customers (' . count($rows) . ')', 'customers');
?>
<div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Orders</th><th>Spent</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td><b><?= e($r['name']) ?></b></td><td><?= e($r['email']) ?></td><td><?= e($r['phone'] ?? '-') ?></td>
          <td><?= e(date('d M Y', strtotime($r['created_at']))) ?></td><td><?= (int)$r['orders'] ?></td><td><?= money((int)$r['spent']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" class="muted">Abhi koi registered customer nahi.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php adminFoot();
