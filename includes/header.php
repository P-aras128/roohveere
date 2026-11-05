<?php
require_once __DIR__ . '/functions.php';

$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$navUser = currentUser($pdo);
$pageTitle = $pageTitle ?? SITE_NAME . ' | ' . SITE_TAGLINE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="Discover soulful fragrances by RoohVeere, founded by Sirishti Chhabria.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Announcement bar -->
<div class="announce">
  <div class="announce-track">
    <?php for ($i = 0; $i < 4; $i++): ?>
      <span>Kiss Launch</span><span>◆</span>
      <span><b>10% OFF DUO BUNDLES</b></span><span>◆</span>
      <span>Rooted In Soul, Worn With Pride</span><span>◆</span>
      <span><b>Complimentary gift packaging</b></span><span>◆</span>
    <?php endfor; ?>
  </div>
</div>

<!-- Header -->
<header class="site-header">
  <div class="container header-inner">
    <a href="<?= BASE_URL ?>/" class="logo">
      <span class="logo-main">ROOHVEERE</span>
      <span class="logo-sub">by Sirishti Chhabria</span>
    </a>
    <nav class="nav">
      <a href="<?= BASE_URL ?>/#collection">Shop</a>
      <a href="<?= BASE_URL ?>/#story">Our Story</a>
      <?php if ($navUser): ?>
        <a href="<?= BASE_URL ?>/account.php">Hi, <?= e(explode(' ', $navUser['name'])[0]) ?></a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php">Login</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/cart.php" class="cart-link">
        Cart <span class="cart-count"><?= (int)$cartCount ?></span>
      </a>
    </nav>
  </div>
</header>
<?php showFlash(); ?>
