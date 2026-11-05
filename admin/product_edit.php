<?php
require_once __DIR__ . '/_layout.php';

function slugify(string $s): string {
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s !== '' ? $s : 'product';
}

$id = (int)($_GET['id'] ?? 0);
$p = ['name' => '', 'slug' => '', 'gender' => 'unisex', 'tagline' => '', 'description' => '', 'price' => '', 'stock' => 100, 'is_active' => 1, 'image' => null];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p) { flash('error', 'Product nahi mila.'); header('Location: ' . BASE_URL . '/admin/products.php'); exit; }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f = [
        'name'        => trim((string)($_POST['name'] ?? '')),
        'slug'        => trim((string)($_POST['slug'] ?? '')),
        'gender'      => (string)($_POST['gender'] ?? ''),
        'tagline'     => trim((string)($_POST['tagline'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'price'       => (int)($_POST['price'] ?? 0),
        'stock'       => max(0, (int)($_POST['stock'] ?? 0)),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ];
    if ($f['name'] === '' || mb_strlen($f['name']) > 150) $errors[] = 'Product ka naam likhein.';
    if (!in_array($f['gender'], ['her', 'him', 'unisex'], true)) $errors[] = 'Gender chunein.';
    if ($f['price'] < 1) $errors[] = 'Price sahi likhein.';
    $f['slug'] = slugify($f['slug'] !== '' ? $f['slug'] : $f['name']);

    $st = $pdo->prepare('SELECT id FROM products WHERE slug = ? AND id <> ?');
    $st->execute([$f['slug'], $id]);
    if ($st->fetch()) $errors[] = 'Ye slug pehle se kisi aur product ka hai. Slug badal dein.';

    // Image upload
    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Photo upload nahi ho saki (shayad file bohat badi hai).';
        } elseif ($file['size'] > 3 * 1024 * 1024) {
            $errors[] = 'Photo 3MB se choti honi chahiye.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
            if (!$ext || !@getimagesize($file['tmp_name'])) {
                $errors[] = 'Sirf JPG, PNG ya WEBP photo upload karein.';
            } else {
                $newImage = 'uploads/products/' . $f['slug'] . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            }
        }
    }

    if (!$errors) {
        if ($newImage) {
            $dir = __DIR__ . '/../uploads/products';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../' . $newImage)) {
                $errors[] = 'Photo save nahi ho saki. uploads/products folder ki permission check karein.';
                $newImage = null;
            }
        }
    }

    if (!$errors) {
        $img = $newImage ?: $p['image'];
        if ($id) {
            $pdo->prepare('UPDATE products SET name=?, slug=?, gender=?, tagline=?, description=?, price=?, stock=?, is_active=?, image=? WHERE id=?')
                ->execute([$f['name'], $f['slug'], $f['gender'], $f['tagline'] ?: null, $f['description'] ?: null, $f['price'], $f['stock'], $f['is_active'], $img, $id]);
            if ($newImage && $p['image'] && str_starts_with($p['image'], 'uploads/products/')) {
                @unlink(__DIR__ . '/../' . $p['image']);   // purani photo hatao
            }
        } else {
            $pdo->prepare('INSERT INTO products (name, slug, gender, tagline, description, price, stock, is_active, image) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$f['name'], $f['slug'], $f['gender'], $f['tagline'] ?: null, $f['description'] ?: null, $f['price'], $f['stock'], $f['is_active'], $img]);
        }
        flash('success', 'Product save ho gaya.');
        header('Location: ' . BASE_URL . '/admin/products.php');
        exit;
    }
    $p = array_merge($p, $f);
}

adminHead($id ? 'Edit Product' : 'Naya Product', 'products');
?>
<?php foreach ($errors as $er): ?><div class="flash flash-error" style="margin-bottom:10px"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="admin-form">
  <?= csrf_field() ?>
  <label>Naam *<input type="text" name="name" value="<?= e($p['name']) ?>" required></label>
  <div class="two">
    <label>Slug (URL, khali chhorein to naam se ban jayega)<input type="text" name="slug" value="<?= e($p['slug']) ?>"></label>
    <label>Gender
      <select name="gender">
        <?php foreach (['her' => 'For Her', 'him' => 'For Him', 'unisex' => 'Unisex'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= $p['gender'] === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
  <label>Tagline<input type="text" name="tagline" value="<?= e($p['tagline']) ?>" maxlength="255"></label>
  <label>Description<textarea name="description" rows="5"><?= e($p['description']) ?></textarea></label>
  <div class="two">
    <label>Price (PKR) *<input type="number" name="price" min="1" value="<?= e($p['price']) ?>" required></label>
    <label>Stock<input type="number" name="stock" min="0" value="<?= (int)$p['stock'] ?>"></label>
  </div>
  <label>Product ki photo (JPG/PNG/WEBP, 3MB tak)
    <?php if (!empty($p['image']) && file_exists(__DIR__ . '/../' . $p['image'])): ?>
      <img class="cur-img" src="<?= BASE_URL . '/' . e($p['image']) ?>" alt="">
      <small>Nayi photo chunengi to purani badal jayegi.</small>
    <?php endif; ?>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
  </label>
  <label class="check"><input type="checkbox" name="is_active" <?= $p['is_active'] ? 'checked' : '' ?>> Website par dikhayein</label>
  <div><button class="btn" type="submit">Save Product</button> <a class="btn btn-outline" href="products.php">Cancel</a></div>
</form>
<?php adminFoot();
