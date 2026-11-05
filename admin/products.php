<?php
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['toggle'])) {
        $pdo->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('success', 'Product ka status badal diya.');
    } elseif (isset($_POST['stock'])) {
        $pdo->prepare('UPDATE products SET stock = ? WHERE id = ?')->execute([max(0, (int)$_POST['stock']), $id]);
        flash('success', 'Stock update ho gaya.');
    }
    header('Location: ' . BASE_URL . '/admin/products.php');
    exit;
}

$products = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
adminHead('Products', 'products');
?>
<p style="margin-bottom:16px"><a class="btn" href="product_edit.php">+ Naya Product</a></p>
<div class="table-wrap">
  <table class="tbl">
    <thead><tr><th>Photo</th><th>Name</th><th>For</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p):
      $has = !empty($p['image']) && file_exists(__DIR__ . '/../' . $p['image']); ?>
      <tr>
        <td><?php if ($has): ?><img class="thumb" src="<?= BASE_URL . '/' . e($p['image']) ?>" alt=""><?php else: ?><div class="thumb-ph">No photo</div><?php endif; ?></td>
        <td><b><?= e($p['name']) ?></b></td>
        <td><?= e(ucfirst($p['gender'])) ?></td>
        <td><?= money((int)$p['price']) ?></td>
        <td>
          <form method="post" class="inline-form"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <input type="number" name="stock" min="0" value="<?= (int)$p['stock'] ?>">
            <button class="btn btn-outline" type="submit">Save</button>
          </form>
        </td>
        <td>
          <form method="post" class="inline-form"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn btn-outline" name="toggle" value="1" type="submit"><?= $p['is_active'] ? 'Active ✓' : 'Hidden' ?></button>
          </form>
        </td>
        <td><a class="link" href="product_edit.php?id=<?= (int)$p['id'] ?>">Edit / Photo</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$products): ?><tr><td colspan="7" class="muted">Abhi koi product nahi. "Naya Product" se add karein.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php adminFoot();
