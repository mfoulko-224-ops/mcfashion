<?php


include 'db.php';
session_start();

// Protéger la page : connexion obligatoire
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    $_SESSION['flash'] = ['type' => 'info', 'msg' => 'Please sign in to place your order.'];
    header("Location: auth.php");
    exit;
}

// Rediriger si panier vide
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Calculer les totaux
$cart_total = 0;
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['qty'];
    $cart_count += $item['qty'];
}
$shipping     = ($cart_total < 500) ? 30 : 0;
$grand_total  = $cart_total + $shipping;

$order_error   = '';
$order_success = false;

// --- Traitement du formulaire de commande ---
if (isset($_POST['place_order'])) {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city    = trim($_POST['city']);

    // Validation simple
    if (empty($name) || empty($email) || empty($address) || empty($city)) {
        $order_error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $order_error = 'Please enter a valid email address.';
    } else {
        // Insérer la commande dans la BDD
        $full_address = "$address, $city";
        $ins = $pdo->prepare(
            "INSERT INTO orders (user_id, customer_name, customer_email, customer_address, total_amount)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ins->execute([$_SESSION['user_id'], $name, $email, $full_address, $grand_total]);
        $order_id = $pdo->lastInsertId();

        // Insérer chaque article de la commande
        foreach ($_SESSION['cart'] as $item) {
            $insItem = $pdo->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $insItem->execute([$order_id, $item['id'], $item['name'], $item['price'], $item['qty']]);
        }

        // Vider le panier
        $_SESSION['cart'] = [];
        $order_success     = true;
        $order_number      = str_pad($order_id, 6, '0', STR_PAD_LEFT);
    }
}

$page_title = 'Checkout';
include 'includes/header.php';
?>

<!-- En-tête de page -->
<div class="page-hero">
  <div class="container">
    <h1>Checkout</h1>
    <p class="breadcrumb">
      <a href="index.php">Home</a> / <a href="cart.php">Cart</a> / Checkout
    </p>
  </div>
</div>

<!-- ===================== CHECKOUT ===================== -->
<section class="section">
  <div class="container">

    <?php if ($order_success): ?>
      <!-- ===== CONFIRMATION DE COMMANDE ===== -->
      <div style="max-width:600px; margin:0 auto; text-align:center; padding:60px 20px;">
        <div style="width:80px; height:80px; background:#eafaf1; border-radius:50%; 
                    display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
          <i class="fas fa-check" style="font-size:2rem; color:#1d6a3e;"></i>
        </div>
        <h2 style="font-family:var(--font-serif); font-size:2rem; color:var(--green); margin-bottom:12px;">
          Order Confirmed!
        </h2>
        <p style="color:var(--mid); margin-bottom:8px;">
          Thank you for your purchase. Your order has been placed successfully.
        </p>
        <p style="color:var(--light-text); font-size:0.9rem; margin-bottom:30px;">
          Order #<?= $order_number ?> • We'll contact you soon with shipping details.
        </p>
        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
          <a href="orders.php" class="btn">View My Orders</a>
          <a href="index.php" class="btn btn-outline">Continue Shopping</a>
        </div>
      </div>

    <?php else: ?>
      <!-- ===== FORMULAIRE CHECKOUT ===== -->
      <?php if ($order_error): ?>
        <div class="alert alert-error" style="max-width:900px; margin:0 auto 20px;">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($order_error) ?>
        </div>
      <?php endif; ?>

      <form action="checkout.php" method="POST">
        <div class="checkout-layout">

          <!-- Formulaire de livraison -->
          <div>
            <div class="checkout-section">
              <h3>Shipping Information</h3>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                  <label class="form-label">Full Name *</label>
                  <input type="text" name="name" class="form-input" 
                         value="<?= htmlspecialchars($_SESSION['user']) ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Phone Number</label>
                  <input type="tel" name="phone" class="form-input" placeholder="+212 ...">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-input" 
                       placeholder="your@email.com" required>
              </div>

              <div class="form-group">
                <label class="form-label">Street Address *</label>
                <input type="text" name="address" class="form-input" 
                       placeholder="Street, building, apartment..." required>
              </div>

              <div class="form-group">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-input" placeholder="City" required>
              </div>
            </div>

            <!-- Méthode de paiement (indicatif pour la démo) -->
            <div class="checkout-section">
              <h3>Payment Method</h3>
              <div style="padding:16px; background:var(--bg); border-radius:8px; 
                          border:2px solid var(--green); display:flex; align-items:center; gap:12px;">
                <i class="fas fa-money-bill-wave" style="color:var(--green); font-size:1.4rem;"></i>
                <div>
                  <strong>Cash on Delivery</strong>
                  <p style="font-size:0.82rem; color:var(--light-text); margin:0;">Pay when your order arrives</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Résumé de la commande -->
          <div class="order-summary">
            <h3>Your Order</h3>

            <?php foreach ($_SESSION['cart'] as $item): ?>
              <div style="display:flex; gap:12px; margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid var(--border);">
                <img src="assests/mcfashionimage/<?= htmlspecialchars($item['image']) ?>" 
                     style="width:55px; height:65px; object-fit:cover; border-radius:4px;"
                     alt="<?= htmlspecialchars($item['name']) ?>">
                <div style="flex:1;">
                  <p style="font-weight:500; font-size:0.9rem; margin-bottom:3px;"><?= htmlspecialchars($item['name']) ?></p>
                  <small style="color:var(--light-text);">Qty: <?= $item['qty'] ?></small>
                </div>
                <strong style="font-size:0.9rem;"><?= number_format($item['price'] * $item['qty'], 2) ?> MAD</strong>
              </div>
            <?php endforeach; ?>

            <div class="summary-row">
              <span>Subtotal</span>
              <span><?= number_format($cart_total, 2) ?> MAD</span>
            </div>
            <div class="summary-row">
              <span>Shipping</span>
              <span><?= $shipping > 0 ? $shipping . ' MAD' : 'Free' ?></span>
            </div>
            <div class="summary-row total">
              <span>Total</span>
              <span><?= number_format($grand_total, 2) ?> MAD</span>
            </div>

            <button type="submit" name="place_order" class="btn btn-full" style="margin-top:20px;">
              <i class="fas fa-lock"></i> Place Order
            </button>

            <p style="text-align:center; font-size:0.78rem; color:var(--light-text); margin-top:12px;">
              <i class="fas fa-shield-alt"></i> Secure checkout
            </p>
          </div>

        </div>
      </form>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
