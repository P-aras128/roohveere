<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('');

$id  = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Ye product maujood nahi hai.');
    redirect('');
}

if ((int)$product['stock'] < 1) {
    flash('error', $product['name'] . ' abhi out of stock hai.');
    redirect('cart.php');
}

$current = $_SESSION['cart'][$id] ?? 0;
$limit   = min((int)$product['stock'], MAX_QTY_PER_ITEM);
$newQty  = min($current + $qty, $limit);

$_SESSION['cart'][$id] = $newQty;

if ($current + $qty > $limit) {
    flash('info', $product['name'] . ': is se zyada quantity available nahi (max ' . $limit . ').');
} else {
    flash('success', $product['name'] . ' cart ma add ho gaya.');
}

// Jis page se aaye the wahin wapis, warna cart page
$back = $_SERVER['HTTP_REFERER'] ?? '';
if ($back !== '' && strpos($back, BASE_URL) === 0) {
    header('Location: ' . $back);
    exit;
}
redirect('cart.php'); 