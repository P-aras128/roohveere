<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('cart.php');

// Remove button
if (isset($_POST['remove'])) {
    unset($_SESSION['cart'][(int)$_POST['remove']]);
    flash('info', 'Item cart se hata diya.');
    redirect('cart.php');
}

// Quantity update
foreach (($_POST['qty'] ?? []) as $id => $qty) {
    $id  = (int)$id;
    $qty = (int)$qty;

    if (!isset($_SESSION['cart'][$id])) continue;

    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
        continue;
    }

    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([$id]);
    $stock = $stmt->fetchColumn();

    if ($stock === false) {
        unset($_SESSION['cart'][$id]);
        continue;
    }
    $_SESSION['cart'][$id] = min($qty, (int)$stock, MAX_QTY_PER_ITEM);
}

flash('success', 'Cart update ho gaya.');
redirect('cart.php'); 