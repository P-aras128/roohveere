<?php
$pageTitle = 'RoohVeere | Rooted In Soul, Worn With Pride';
require_once __DIR__ . '/includes/header.php';

// Filter: ?gender=her / him, warna sab
$filter = $_GET['gender'] ?? 'all';
$products = getProducts($pdo, $filter === 'all' ? null : $filter);

$labels = ['her' => 'For Her', 'him' => 'For Him', 'unisex' => 'Unisex'];
?>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <p class="eyebrow">A LITTLE LUXURY, A LASTING MEMORY</p>
    <h1>Find the scent that feels like you.</h1>
    <a href="#collection" class="btn">Shop the collection →</a>
  </div>
</section>

<!-- Collection -->
<section class="section" id="collection">
  <div class="container">
    <p class="eyebrow center">The Fragrance Collection</p>
    <h2 class="center">Find Your Signature Scent</h2>
    <p class="center muted">Discover a fragrance that tells your story.</p>

    <div class="filters">
      <a href="?gender=all#collection"  class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
      <a href="?gender=her#collection"  class="<?= $filter === 'her' ? 'active' : '' ?>">For Her</a>
      <a href="?gender=him#collection"  class="<?= $filter === 'him' ? 'active' : '' ?>">For Him</a>
    </div>

    <div class="grid">
      <?php foreach ($products as $p):
        $hasImg = !empty($p['image']) && file_exists(__DIR__ . '/' . $p['image']);
      ?>
        <article class="card">
          <a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>" class="card-img">
            <?php if ($hasImg): ?>
              <img src="<?= BASE_URL . '/' . e($p['image']) ?>" alt="<?= e($p['name']) ?>">
            <?php else: ?>
              <div class="img-placeholder"><?= e($p['name']) ?></div>
            <?php endif; ?>
          </a>
          <div class="card-body">
            <span class="tag"><?= e($labels[$p['gender']]) ?></span>
            <h3><?= e($p['name']) ?></h3>
            <p class="price"><?= money($p['price']) ?></p>
            <div class="card-actions">
              <a href="<?= BASE_URL ?>/product.php?slug=<?= e($p['slug']) ?>" class="btn btn-outline">View Details</a>
              <form action="<?= BASE_URL ?>/cart_add.php" method="post">
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="btn">Add to Cart</button>
              </form>
            </div>
          </div>
        </article>
      <?php endforeach; ?>

      <?php if (!$products): ?>
        <p class="center muted">Is category ma abhi koi product nahi hai.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Story -->
<section class="section story" id="story">
  <div class="container story-inner">
    <p class="eyebrow">OUR STORY</p>
    <h2>RoohVeere by Sirishti Chhabria</h2>
    <p class="quote">Rooted In Soul, Worn With Pride.</p>
    <p>RoohVeere is a heartfelt tribute: <em>Rooh</em>, meaning "soul," and <em>Veere</em>, lovingly taken from the name of Sirishti's mother, <em>Veeri</em>. Together, they signify <strong>"the soul of Veeri"</strong>, a brand built on love, inspiration, and cherished values.</p>
    <p>Founded in <strong>2025</strong> by Sirishti Chhabria, RoohVeere blends timeless elegance with a personal touch. Each creation is more than just a product. It is a story, a memory, and a reflection of individuality.</p>
    <p><strong>At RoohVeere, beauty begins with the soul.</strong></p>
    <p class="muted">Sirishti Chhabria, Founder &amp; Creative Director</p>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 