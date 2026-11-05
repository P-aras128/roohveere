<?php
require_once __DIR__ . '/includes/functions.php';

$product = getProductBySlug($pdo, $_GET['slug'] ?? '');

// Product na mile to 404 page
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product not found | ' . SITE_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container center">
            <h2>Product nahi mila</h2>
            <p class="muted" style="margin:12px 0 24px">Ye product maujood nahi hai ya hata diya gaya hai.</p>
            <a class="btn" href="' . BASE_URL . '/#collection">Shop par wapis jao</a>
          </div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $product['name'] . ' | ' . SITE_NAME;
$labels = ['her' => 'For Her', 'him' => 'For Him', 'unisex' => 'Unisex'];
$maxQty = max(0, min((int)$product['stock'], MAX_QTY_PER_ITEM));

// Aur products (suggestions)
$stmt = $pdo->prepare('SELECT * FROM products WHERE is_active = 1 AND id != ? ORDER BY RAND() LIMIT 3');
$stmt->execute([$product['id']]);
$related = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <p class="crumbs"><a href="<?= BASE_URL ?>/">Home</a> / <a href="<?= BASE_URL ?>/#collection">Shop</a> / <?= e($product['name']) ?></p>

    <div class="product-page">
      <div class="product-image"><?= productImageHtml($product) ?></div>

      <div class="product-info">
        <span class="tag"><?= e($labels[$product['gender']]) ?> · Eau de Parfum</span>
        <h1><?= e($product['name']) ?></h1>
        <p class="muted"><?= e($product['tagline']) ?></p>
        <p class="price big"><?= money((int)$product['price']) ?></p>
        <p class="desc"><?= nl2br(e($product['description'])) ?></p>

        <?php if ($maxQty > 0): ?>
          <form action="<?= BASE_URL ?>/cart_add.php" method="post" class="buy-form">
            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
            <label>Quantity
              <select name="qty">
                <?php for ($i = 1; $i <= $maxQty; $i++): ?>
                  <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
              </select>
            </label>
            <button type="submit" class="btn">Add to Cart</button>
          </form>
        <?php else: ?>
          <p class="out">Out of stock</p>
        <?php endif; ?>

        <div class="perks">
          <p>◆ Buy any 2 scents and get <b><?= BUNDLE_DISCOUNT_PERCENT ?>% OFF</b></p>
          <p>◆ Complimentary gift packaging</p>
        </div>
      </div>
    </div>

    <?php if ($related): ?>
      <h2 class="center" style="margin:70px 0 28px">You may also like</h2>
      <div class="grid">
        <?php foreach ($related as $r): ?>
          <article class="card">
            <a href="<?= BASE_URL ?>/product.php?slug=<?= e($r['slug']) ?>" class="card-img"><?= productImageHtml($r) ?></a>
            <div class="card-body">
              <span class="tag"><?= e($labels[$r['gender']]) ?></span>
              <h3><?= e($r['name']) ?></h3>
              <p class="price"><?= money((int)$r['price']) ?></p>
              <a href="<?= BASE_URL ?>/product.php?slug=<?= e($r['slug']) ?>" class="btn btn-outline">View Details</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?> 