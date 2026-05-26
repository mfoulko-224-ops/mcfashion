<?php
/* =====================================================
   PAGE FAVORIS — wishlist.php
   Affiche les produits aimés par l'utilisateur
   Fonctionne pour invités (session) et connectés (BDD)
   ===================================================== */

include 'db.php';
session_start();

if (!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];

// --- Action : Retirer un produit des favoris ---
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if (isset($_SESSION['user_id'])) {
        // Supprimer de la BDD pour les utilisateurs connectés
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$_SESSION['user_id'], $id]);
        // Mettre à jour la session
        $wRows = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $wRows->execute([$_SESSION['user_id']]);
        $_SESSION['wishlist'] = array_column($wRows->fetchAll(), 'product_id');
    } else {
        // Supprimer de la session pour les invités
        $_SESSION['wishlist'] = array_values(array_diff($_SESSION['wishlist'], [$id]));
    }

    $_SESSION['flash'] = ['type' => 'info', 'msg' => 'Item removed from your wishlist.'];
    header("Location: wishlist.php");
    exit;
}

// --- Action : Ajouter l'article au panier depuis la wishlist ---
if (isset($_GET['action']) && $_GET['action'] === 'add_to_cart' && isset($_GET['id'])) {
    $id   = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $id) { $item['qty']++; $found = true; break; }
        }
        unset($item);
        if (!$found) { $product['qty'] = 1; $_SESSION['cart'][] = $product; }
        $_SESSION['flash'] = ['type' => 'success', 'msg' => '"' . $product['name'] . '" added to cart!'];
    }
    header("Location: wishlist.php");
    exit;
}

// Charger les produits de la wishlist
$wishlist_products = [];
if (!empty($_SESSION['wishlist'])) {
    // Construire la requête avec les IDs de la wishlist
    $ids         = implode(',', array_map('intval', $_SESSION['wishlist']));
    $wishlist_products = $pdo->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll();
}

// Sync BDD si connecté
if (isset($_SESSION['user_id'])) {
    $wRows = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wRows->execute([$_SESSION['user_id']]);
    $_SESSION['wishlist'] = array_column($wRows->fetchAll(), 'product_id');
}

$page_title = 'My Wishlist';
include 'includes/header.php';
?>

<!-- Message flash -->
<?php if (isset($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="flash-bar <?= $f['type'] ?>"><?= htmlspecialchars($f['msg']) ?></div>
<?php endif; ?>
<!-- En-tête de page -->
<div class="page-hero">
  <div class="container">
    <h1>My Wishlist</h1>
    <p class="breadcrumb"><a href="index.php">Home</a> / Wishlist</p>
  </div>
</div>

<!-- ===================== CONTENU WISHLIST ===================== -->
<section class="section">
  <div class="container">

    <?php if (empty($wishlist_products)): ?>
      <!-- Wishlist vide -->
      <div class="empty-state">
        <i class="fas fa-heart"></i>
        <h3>Your wishlist is empty</h3>
        <p>Save items you love and come back to them anytime.</p>
        <a href="index.php#products" class="btn">Explore Products</a>
      </div>

    <?php else: ?>
      <p style="margin-bottom:24px; color:var(--mid);">
        <?= count($wishlist_products) ?> item<?= count($wishlist_products) > 1 ? 's' : '' ?> saved
      </p>

      <div class="products-grid">
        <?php foreach ($wishlist_products as $p): ?>
          <div class="product-card">
            <div class="product-img-wrap">
              <img src="assests/mcfashionimage/<?= htmlspecialchars($p['image']) ?>" 
                   alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
              <span class="cat-badge"><?= ucfirst($p['category']) ?></span>
            </div>
            <div class="product-info">
              <div>
                <h4><?= htmlspecialchars($p['name']) ?></h4>
                <span class="product-price"><?= number_format($p['price'], 2) ?> MAD</span>
              </div>
              <!-- Boutons d'action -->
              <div style="display:flex; gap:10px; margin-top:14px;">
                <a href="wishlist.php?action=add_to_cart&id=<?= $p['id'] ?>" 
                   class="btn btn-sm" style="flex:1;">
                  <i class="fas fa-cart-plus"></i> Add to Cart
                </a>
                <a href="wishlist.php?action=remove&id=<?= $p['id'] ?>" 
                   class="btn btn-outline btn-sm"
                   title="Remove from wishlist"
                   onclick="return confirm('Remove from wishlist?')"
                   style="padding: 8px 12px;">
                  <i class="fas fa-heart-broken"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Bouton vers la boutique -->
      <div style="text-align:center; margin-top:40px;">
        <a href="index.php#products" class="btn btn-outline">
          <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>