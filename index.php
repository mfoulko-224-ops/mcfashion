<?php
/* =====================================================
   PAGE D'ACCUEIL — index.php
   ===================================================== */

include 'db.php';
session_start();

if (!isset($_SESSION['cart']))     $_SESSION['cart']    = [];
if (!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];

// --- Action : Ajouter au panier ---
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $id   = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if ($product) {
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $id) { $item['qty']++; $found = true; break; }
        }
        unset($item);
        if (!$found) { $product['qty'] = 1; $_SESSION['cart'][] = $product; }
        $_SESSION['flash'] = ['type' => 'success', 'msg' => '"' . $product['name'] . '" added to cart!'];
    }
    header("Location: index.php");
    exit;
}

// --- Action : Toggle Wishlist ---
if (isset($_GET['action']) && $_GET['action'] === 'toggle_wishlist' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if (isset($_SESSION['user_id'])) {
        $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$_SESSION['user_id'], $id]);
        if ($check->rowCount() > 0) {
            $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$_SESSION['user_id'], $id]);
        } else {
            $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $id]);
        }
    } else {
        if (in_array($id, $_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$id]);
        } else {
            $_SESSION['wishlist'][] = $id;
        }
    }
    header("Location: index.php");
    exit;
}

$products    = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();
$bestsellers = $pdo->query("SELECT * FROM products WHERE is_bestseller = 1 LIMIT 3")->fetchAll();

if (isset($_SESSION['user_id'])) {
    $wRows = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wRows->execute([$_SESSION['user_id']]);
    $_SESSION['wishlist'] = array_column($wRows->fetchAll(), 'product_id');
}

$page_title = 'Home';
include 'includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="flash-bar <?= $f['type'] ?>"><?= htmlspecialchars($f['msg']) ?></div>
<?php endif; ?>

<!-- ===== HERO ===== -->
<section class="hero">

  <div class="hero-slide active" style="background-image: url('assests/mcfashionimage/SLIDER.png');">
    <div class="hero-content">
      <span class="hero-tag">Up to 75% off</span>
      <h1>Discover &<br>Special Offer<br>Fashion</h1>
      <a href="#products" class="btn">Shop Now</a>
    </div>
  </div>

  <div class="hero-slide" style="background-image: url('assests/mcfashionimage/SLIDER.png');">
    <div class="hero-content">
      <span class="hero-tag">New Collection</span>
      <h1>Style &<br>Comfort<br>Everyday</h1>
      <a href="#products" class="btn">Explore</a>
    </div>
  </div>

  <div class="hero-slide" style="background-image: url('assests/mcfashionimage/SLIDER.png');">
    <div class="hero-content">
      <span class="hero-tag">Premium Quality</span>
      <h1>Quality<br>Materials<br>For You</h1>
      <a href="#products" class="btn">Discover</a>
    </div>
  </div>

  <button class="slider-prev" id="sliderPrev">&#8592;</button>
  <button class="slider-next" id="sliderNext">&#8594;</button>
  <div class="slider-dots">
    <button class="slider-dot active"></button>
    <button class="slider-dot"></button>
    <button class="slider-dot"></button>
  </div>

</section>

<!-- ===== PRODUCTS ===== -->
<section class="section" id="products">
  <div class="container">
    <div class="section-header">
      <h2>Our Products</h2>
      <div class="section-divider"></div>
      <p>Discover our latest collection</p>
    </div>

    <div class="filters">
      <button class="filter-btn active" data-filter="all">All</button>
      <button class="filter-btn" data-filter="men">Men</button>
      <button class="filter-btn" data-filter="women">Women</button>
    </div>

    <div class="products-grid">
      <?php foreach ($products as $p):
        $is_wished = in_array($p['id'], $_SESSION['wishlist']);
      ?>
        <div class="product-card" data-cat="<?= htmlspecialchars($p['category']) ?>">
          <div class="product-img-wrap">
            <img src="assests/mcfashionimage/<?= htmlspecialchars($p['image']) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            <span class="cat-badge"><?= ucfirst($p['category']) ?></span>
            <div class="product-overlay">
              <a href="index.php?action=add&id=<?= $p['id'] ?>" class="overlay-btn" title="Add to cart">
                <i class="fas fa-shopping-cart"></i>
              </a>
              <a href="index.php?action=toggle_wishlist&id=<?= $p['id'] ?>"
                 class="overlay-btn <?= $is_wished ? 'wished' : '' ?>" title="Wishlist">
                <i class="fas fa-heart"></i>
              </a>
              <a href="product.php?id=<?= $p['id'] ?>" class="overlay-btn" title="View details">
                <i class="fas fa-eye"></i>
              </a>
            </div>
          </div>
          <div class="product-info">
            <div>
              <h4><?= htmlspecialchars($p['name']) ?></h4>
              <span class="product-price"><?= number_format($p['price'], 2) ?> MAD</span>
            </div>
            <a href="index.php?action=add&id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Add to Cart</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== BEST SELLERS ===== -->
<?php if (!empty($bestsellers)): ?>
<section class="section section-alt" id="bestsellers">
  <div class="container">
    <div class="section-header">
      <h2>Best Sellers</h2>
      <div class="section-divider"></div>
    </div>
    <div class="products-grid">
      <?php foreach ($bestsellers as $p): ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <img src="assests/mcfashionimage/<?= htmlspecialchars($p['image']) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>">
            <span class="cat-badge"><?= ucfirst($p['category']) ?></span>
          </div>
          <div class="product-info">
            <div>
              <h4><?= htmlspecialchars($p['name']) ?></h4>
              <span class="product-price"><?= number_format($p['price'], 2) ?> MAD</span>
            </div>
            <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-sm">View Details</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== CTA ===== -->
<section class="section" style="background: #2d5a27; color: #fff; text-align: center; padding: 70px 20px;">
  <h2 style="font-family: var(--font-serif); font-size: 2.5rem; margin-bottom: 12px;">Join Our Community</h2>
  <p style="margin-bottom: 24px; opacity: 0.85;">Get exclusive access to new arrivals.</p>
  <a href="auth.php?tab=register" class="btn btn-gold">Create Free Account</a>
</section>

<?php include 'includes/footer.php'; ?>