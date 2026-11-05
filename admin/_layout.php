<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$st = $pdo->prepare('SELECT username FROM admins WHERE id = ?');
$st->execute([(int)$_SESSION['admin_id']]);
$adminName = $st->fetchColumn();
if ($adminName === false) {
    unset($_SESSION['admin_id']);
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
} 

const ORDER_STATUSES = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

function adminHead(string $title, string $active): void {
   global $adminName; 
    $nav = [
        'dashboard' => ['index.php', 'Dashboard'],
        'orders'    => ['orders.php', 'Orders'],
        'products'  => ['products.php', 'Products'],
        'customers' => ['customers.php', 'Customers'],
        'admins'    => ['admins.php', 'Admins'], 
    ];
    ?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | RoohVeere Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/assets/admin.css">
</head><body class="admin">
<aside class="sidebar">
  <div class="brand">ROOHVEERE<small>Admin Panel · <?= e($adminName) ?></small></div> 
  <nav>
    <?php foreach ($nav as $key => [$href, $label]): ?>
      <a href="<?= BASE_URL ?>/admin/<?= $href ?>" class="<?= $key === $active ? 'on' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/" target="_blank">View Website ↗</a>
  </nav>
  <form action="<?= BASE_URL ?>/admin/logout.php" method="post"><?= csrf_field() ?><button type="submit">Logout</button></form>
</aside>
<main class="main">
  <?php showFlash(); ?>
  <h1 class="page-title"><?= e($title) ?></h1>
<?php
}

function adminFoot(): void { echo "</main></body></html>"; }

function pager(int $page, int $pages, array $params): string {
    if ($pages <= 1) return '';
    $out = '<div class="pager">';
    for ($i = 1; $i <= $pages; $i++) {
        $q = http_build_query(array_merge($params, ['page' => $i]));
        $out .= '<a href="?' . e($q) . '" class="' . ($i === $page ? 'on' : '') . '">' . $i . '</a>';
    }
    return $out . '</div>';
}
