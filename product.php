<?php


include 'db.php';
session_start();

if (!isset($_SESSION['cart']))    $_SESSION['cart']    = [];
if (!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];

// Récupérer l'ID du produit depuis l'URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header("Location: index.php");
    exit;
}

// Charger le produit depuis la BDD
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

// Si le produit n'existe pas, rediriger
if (!$product) {
    header("Location: index.php");
    exit;
}

// Produits similaires (même catégorie, sauf le produit actuel)
$similar = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$similar->execute([$product['category'], $id]);
$similar_products = $similar->fetchAll();

$is_wished = in_array($id, $_SESSION['wishlist']);

$page_title = $product['name'];
include 'includes/header.php';
?>
<!-- En-tête de page -->
<div class="page-hero">
  <div class="container">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p class="breadcrumb">
      <a href="index.php">Home</a> / 
      <a href="index.php#products">Products</a> / 
      <?= htmlspecialchars($product['name']) ?>
    </p>
  </div>
</div>

<!-- ===================== DÉTAIL PRODUIT ===================== -->
<section class="section">
  <div class="container">

    <div class="product-detail-grid">

      <!-- Image du produit -->
      <div>
        <img src="assests/mcfashionimage/<?= htmlspecialchars($product['image']) ?>" 
             alt="<?= htmlspecialchars($product['name']) ?>"
             style="width:100%; height:480px; object-fit:cover; border-radius:10px;">
      </div>

      <!-- Informations du produit -->
      <div style="padding: 10px 0;">
        <span style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--gold); font-weight:600;">
          <?= ucfirst($product['category']) ?>
        </span>

        <h1 style="font-family:var(--font-serif); font-size:2.2rem; color:var(--green); margin:10px 0;">
          <?= htmlspecialchars($product['name']) ?>
        </h1>

        <div style="font-family:var(--font-serif); font-size:2rem; color:var(--dark); font-weight:600; margin-bottom:20px;">
          <?= number_format($product['price'], 2) ?> <span style="font-size:1rem; font-weight:400;">MAD</span>
        </div>

        <!-- Ligne décorative -->
        <div style="width:50px; height:2px; background:var(--gold); margin-bottom:20px;"></div>

        <p style="color:var(--mid); line-height:1.8; margin-bottom:28px;">
          <?= htmlspecialchars($product['description']) ?>
        </p>

        <!-- Indicateur de stock -->
        <p style="font-size:0.85rem; color:var(--green); margin-bottom:20px;">
          <i class="fas fa-check-circle"></i> 
          <?= $product['stock'] > 0 ? 'In stock (' . $product['stock'] . ' available)' : '<span style="color:red;">Out of stock</span>' ?>
        </p>

        <!-- Boutons d'action -->
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
          <?php if ($product['stock'] > 0): ?>
            <a href="index.php?action=add&id=<?= $product['id'] ?>" class="btn">
              <i class="fas fa-shopping-cart"></i> Add to Cart
            </a>
          <?php endif; ?>

          <a href="index.php?action=toggle_wishlist&id=<?= $product['id'] ?>" 
             class="btn btn-outline <?= $is_wished ? 'btn-gold' : '' ?>"
             title="<?= $is_wished ? 'Remove from wishlist' : 'Add to wishlist' ?>">
            <i class="fas fa-heart"></i> 
            <?= $is_wished ? 'Saved' : 'Save' ?>
          </a>
        </div>

        <!-- Informations livraison -->
        <div style="margin-top:30px; padding:18px; background:var(--bg); border-radius:8px; font-size:0.88rem;">
          <p style="margin-bottom:8px;"><i class="fas fa-truck" style="color:var(--green); margin-right:8px;"></i> Free shipping on orders over 500 MAD</p>
          <p><i class="fas fa-undo" style="color:var(--green); margin-right:8px;"></i> Easy 30-day returns</p>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- ===================== PRODUITS SIMILAIRES ===================== -->
<?php if (!empty($similar_products)): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <h2>You May Also Like</h2>
      <div class="section-divider"></div>
    </div>
    <div class="products-grid">
      <?php foreach ($similar_products as $p): ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <img src="assests/mcfashionimage/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            <span class="cat-badge"><?= ucfirst($p['category']) ?></span>
            <div class="product-overlay">
              <a href="index.php?action=add&id=<?= $p['id'] ?>" class="overlay-btn" title="Add to cart">
                <i class="fas fa-shopping-cart"></i>
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
            <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">View Details</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
