<?php


include 'db.php';
session_start();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// --- Action : Augmenter la quantité ---
if (isset($_GET['action']) && $_GET['action'] === 'increase' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) { $item['qty']++; break; }
    }
    unset($item);
    header("Location: cart.php");
    exit;
}

// --- Action : Diminuer la quantité (minimum 1) ---
if (isset($_GET['action']) && $_GET['action'] === 'decrease' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            if ($item['qty'] > 1) $item['qty']--;
            break;
        }
    }
    unset($item);
    header("Location: cart.php");
    exit;
}

// --- Action : Supprimer un article ---
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $id) { unset($_SESSION['cart'][$key]); break; }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Réindexer
    header("Location: cart.php");
    exit;
}

// --- Action : Vider entièrement le panier ---
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// Calcul du total
$cart_total  = 0;
$cart_count  = 0;
$shipping    = 0; // Livraison gratuite par défaut
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['qty'];
    $cart_count += $item['qty'];
}
// Frais de livraison si total < 500 MAD
if ($cart_total > 0 && $cart_total < 500) {
    $shipping = 30;
}

$page_title = 'My Cart';
include 'includes/header.php';
?>

<!-- En-tête de page -->
<div class="page-hero">
  <div class="container">
    <h1>My Cart</h1>
    <p class="breadcrumb">
      <a href="index.php">Home</a> / My Cart
    </p>
  </div>
</div>

<!-- ===================== CONTENU PANIER ===================== -->
<section class="section">
  <div class="container">

    <?php if (empty($_SESSION['cart'])): ?>
      <!-- Panier vide -->
      <div class="empty-state">
        <i class="fas fa-shopping-bag"></i>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything yet.</p>
        <a href="index.php#products" class="btn">Start Shopping</a>
      </div>

    <?php else: ?>
      <div class="cart-layout">

        <!-- Colonne gauche : tableau des articles -->
        <div>
          <table class="cart-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($_SESSION['cart'] as $item): ?>
              <tr>
                <td>
                  <div class="cart-product">
                    <img src="assests/mcfashionimage/<?= htmlspecialchars($item['image']) ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>">
                    <div>
                      <h5><?= htmlspecialchars($item['name']) ?></h5>
                      <small><?= ucfirst($item['category']) ?></small>
                    </div>
                  </div>
                </td>
                <td><?= number_format($item['price'], 2) ?> MAD</td>
                <td>
                  <div class="qty-control">
                    <a href="cart.php?action=decrease&id=<?= $item['id'] ?>" class="qty-btn" title="Decrease">−</a>
                    <span class="qty-value"><?= $item['qty'] ?></span>
                    <a href="cart.php?action=increase&id=<?= $item['id'] ?>" class="qty-btn" title="Increase">+</a>
                  </div>
                </td>
                <td><strong><?= number_format($item['price'] * $item['qty'], 2) ?> MAD</strong></td>
                <td>
                  <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="remove-btn" 
                     title="Remove" onclick="return confirm('Remove this item?')">
                    <i class="fas fa-times"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <!-- Boutons sous le tableau -->
          <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; flex-wrap:wrap; gap:12px;">
            <a href="index.php#products" class="btn btn-outline btn-sm">
              <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
            <a href="cart.php?action=clear" class="btn btn-sm" 
               style="background:#e74c3c; border-color:#e74c3c;"
               onclick="return confirm('Clear your entire cart?')">
              <i class="fas fa-trash"></i> Clear Cart
            </a>
          </div>
        </div>

        <!-- Colonne droite : résumé de commande -->
        <div class="order-summary">
          <h3>Order Summary</h3>

          <div class="summary-row">
            <span>Items (<?= $cart_count ?>)</span>
            <span><?= number_format($cart_total, 2) ?> MAD</span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span><?= $shipping > 0 ? $shipping . ' MAD' : '<span style="color:var(--green);">Free</span>' ?></span>
          </div>
          <?php if ($shipping == 0 && $cart_total > 0): ?>
            <p style="font-size:0.78rem; color:var(--green); margin-bottom:8px;">
              <i class="fas fa-check-circle"></i> You qualify for free shipping!
            </p>
          <?php elseif ($cart_total > 0): ?>
            <p style="font-size:0.78rem; color:var(--light-text); margin-bottom:8px;">
              Add <?= number_format(500 - $cart_total, 2) ?> MAD more for free shipping
            </p>
          <?php endif; ?>

          <div class="summary-row total">
            <span>Total</span>
            <span><?= number_format($cart_total + $shipping, 2) ?> MAD</span>
          </div>

          <!-- Bouton Checkout -->
          <?php if (isset($_SESSION['user'])): ?>
            <a href="checkout.php" class="btn btn-full" style="margin-top:20px;">
              Proceed to Checkout <i class="fas fa-arrow-right"></i>
            </a>
          <?php else: ?>
            <p style="font-size:0.85rem; text-align:center; color:var(--mid); margin-top:16px; margin-bottom:10px;">
              Please sign in to place your order
            </p>
            <a href="auth.php" class="btn btn-full">
              Sign In to Checkout
            </a>
          <?php endif; ?>
        </div>

      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
