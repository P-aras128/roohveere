<?php
$pageTitle = 'Your Cart | RoohVeere';
require_once __DIR__ . '/includes/header.php';

$items  = cartItems($pdo);
$totals = cartTotals($items);
?>

<section class="section">
  <div class="container">
    <h1 class="center" style="margin-bottom:36px">Your Cart</h1>

    <?php if (!$items): ?>
      <div class="center">
        <p class="muted" style="margin-bottom:24px">Aap ka cart abhi khali hai.</p>
        <a href="<?= BASE_URL ?>/#collection" class="btn">Shop the collection →</a>
      </div>

    <?php else: ?>
      <div class="cart-layout">

        <form action="<?= BASE_URL ?>/cart_update.php" method="post" class="cart-items">
          <?php foreach ($items as $it): $p = $it['product']; ?>
            <div class="cart-row">
              <a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>" class="cart-thumb"><?= productImageHtml($p) ?></a>
              <div class="cart-name">
                <a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>"><b><?= e($p['name']) ?></b></a>
                <span class="muted"><?= money((int)$p['price']) ?></span>
              </div>
              <input type="number" name="qty[<?= (int)$p['id'] ?>]" value="<?= (int)$it['qty'] ?>"
                     min="0" max="<?= MAX_QTY_PER_ITEM ?>" class="qty-input">
              <div class="cart-line"><?= money($it['line_total']) ?></div>
              <button type="submit" name="remove" value="<?= (int)$p['id'] ?>" class="remove" title="Remove">✕</button>
            </div>
          <?php endforeach; ?>

          <button type="submit" class="btn btn-outline" style="margin-top:18px">Update Cart</button>
        </form>

        <aside class="summary">
          <h3>Order Summary</h3>

          <?php if ($totals['count'] === 1): ?>
            <p class="hint">Ek aur scent add karo aur <b><?= BUNDLE_DISCOUNT_PERCENT ?>% OFF</b> pao.</p>
          <?php elseif ($totals['discount'] > 0): ?>
            <p class="hint ok">Perfect Pairs Bundle unlock ho gaya.</p>
          <?php endif; ?>

          <div class="sum-row"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
          <?php if ($totals['discount'] > 0): ?>
            <div class="sum-row green"><span>Bundle Discount (<?= BUNDLE_DISCOUNT_PERCENT ?>%)</span><span>- <?= money($totals['discount']) ?></span></div>
          <?php endif; ?>
          <div class="sum-row"><span>Shipping</span><span>Checkout pe</span></div>
          <div class="sum-row total"><span>Total</span><span><?= money($totals['total']) ?></span></div>

          <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-block">Proceed to Checkout</a>
          <a href="<?= BASE_URL ?>/#collection" class="continue">← Continue shopping</a>
        </aside>

      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 
