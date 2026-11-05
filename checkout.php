<?php
require_once __DIR__ . '/includes/functions.php';

$items = cartItems($pdo);
if (!$items) {
    flash('info', 'Pehle cart ma kuch add karo.');
    redirect('cart.php');
}
$totals = cartTotals($items);

$errors = [];
$old = ['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'city' => '', 'notes' => ''];
$user = currentUser($pdo);
if ($user && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $old['name']  = $user['name'];
    $old['phone'] = (string)$user['phone'];
    $old['email'] = $user['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    foreach ($old as $k => $_) {
        $old[$k] = trim((string)($_POST[$k] ?? ''));
    }

    if (mb_strlen($old['name']) < 3 || mb_strlen($old['name']) > 100) {
        $errors['name'] = 'Poora naam likhein (kam az kam 3 huroof).';
    }
    $phone = normalizePhone($old['phone']);
    if (!$phone) {
        $errors['phone'] = 'Sahi mobile number likhein, jaise 03XX XXXXXXX.';
    }
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email sahi nahi hai (ya khali chhor dein).';
    }
    if (mb_strlen($old['address']) < 10) {
        $errors['address'] = 'Poora address likhein (ghar/street/area).';
    }
    if (mb_strlen($old['city']) < 2 || mb_strlen($old['city']) > 100) {
        $errors['city'] = 'City ka naam likhein.';
    }
    if (mb_strlen($old['notes']) > 500) {
        $errors['notes'] = 'Notes 500 huroof se kam rakhein.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Stock ek hi query ma check + kam (do log ek sath order karein to bhi safe)
            $dec = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND is_active = 1 AND stock >= ?');
            foreach ($items as $it) {
                $dec->execute([$it['qty'], $it['product']['id'], $it['qty']]);
                if ($dec->rowCount() !== 1) {
                    throw new RuntimeException($it['product']['name']);
                }
            }

            $orderNumber = 'RV-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

            $pdo->prepare(
                'INSERT INTO orders (user_id, order_number, customer_name, phone, email, address, city, subtotal, discount, total, payment_method, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $user['id'] ?? null, $orderNumber, $old['name'], $phone, $old['email'] ?: null, $old['address'], $old['city'],
                $totals['subtotal'], $totals['discount'], $totals['total'], 'cod', $old['notes'] ?: null,
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)');
            foreach ($items as $it) {
                $ins->execute([$orderId, $it['product']['id'], $it['product']['name'], $it['product']['price'], $it['qty']]);
            }

            $pdo->commit();

            $_SESSION['cart'] = [];
            $_SESSION['last_order'] = $orderNumber;
            redirect('order_success.php?o=' . urlencode($orderNumber));

        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error', $ex->getMessage() . ' ka stock kam hai. Cart check karke dobara try karein.');
            redirect('cart.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors['general'] = 'Order save nahi ho saka. Dobara try karein.';
        }
    }
}

$pageTitle = 'Checkout | RoohVeere';
require_once __DIR__ . '/includes/header.php';

function fieldError(array $errors, string $k): string {
    return isset($errors[$k]) ? '<small class="field-error">' . e($errors[$k]) . '</small>' : '';
}
?>

<section class="section">
  <div class="container">
    <h1 class="center" style="margin-bottom:36px">Checkout</h1>

    <?php if (isset($errors['general'])): ?>
      <div class="flash flash-error" style="margin-bottom:24px"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="cart-layout">
      <form method="post" class="checkout-form" novalidate>
        <?= csrf_field() ?>
        <h3>Delivery Details</h3>

        <label>Poora naam *
          <input type="text" name="name" value="<?= e($old['name']) ?>" maxlength="100" required>
          <?= fieldError($errors, 'name') ?>
        </label>

        <label>Mobile number *
          <input type="tel" name="phone" value="<?= e($old['phone']) ?>" placeholder="03XX XXXXXXX" required>
          <?= fieldError($errors, 'phone') ?>
        </label>

        <label>Email (optional)
          <input type="email" name="email" value="<?= e($old['email']) ?>" maxlength="150">
          <?= fieldError($errors, 'email') ?>
        </label>

        <label>Poora address *
          <textarea name="address" rows="3" required><?= e($old['address']) ?></textarea>
          <?= fieldError($errors, 'address') ?>
        </label>

        <label>City *
          <input type="text" name="city" value="<?= e($old['city']) ?>" maxlength="100" required>
          <?= fieldError($errors, 'city') ?>
        </label>

        <label>Notes (optional)
          <textarea name="notes" rows="2" maxlength="500" placeholder="Gift message ya koi khaas hidayat"><?= e($old['notes']) ?></textarea>
          <?= fieldError($errors, 'notes') ?>
        </label>

        <h3 style="margin-top:26px">Payment</h3>
        <div class="pay-box"><b>Cash on Delivery</b><span class="muted">Saman milne par payment karein.</span></div>

        <button type="submit" class="btn btn-block" style="margin-top:22px">Place Order</button>
      </form>

      <aside class="summary">
        <h3>Your Order</h3>
        <?php foreach ($items as $it): ?>
          <div class="sum-row">
            <span><?= e($it['product']['name']) ?> × <?= (int)$it['qty'] ?></span>
            <span><?= money($it['line_total']) ?></span>
          </div>
        <?php endforeach; ?>
        <div class="sum-row" style="border-top:1px solid var(--line);margin-top:8px"><span>Subtotal</span><span><?= money($totals['subtotal']) ?></span></div>
        <?php if ($totals['discount'] > 0): ?>
          <div class="sum-row green"><span>Bundle Discount (<?= BUNDLE_DISCOUNT_PERCENT ?>%)</span><span>- <?= money($totals['discount']) ?></span></div>
        <?php endif; ?>
        <div class="sum-row total"><span>Total</span><span><?= money($totals['total']) ?></span></div>
        <p class="muted" style="font-size:12px;margin-top:10px">Delivery charges order confirm karte waqt bata diye jayenge.</p>
        <a href="<?= BASE_URL ?>/cart.php" class="continue">← Cart ma wapis</a>
      </aside>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 
