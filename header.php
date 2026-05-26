<?php
/* =====================================================
   FICHIER PARTIEL : En-tête commun à toutes les pages
   Inclus avec : include 'includes/header.php';
   ===================================================== */

// Calculer le nombre d'articles dans le panier pour le badge
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}

// Calculer le nombre de favoris (selon méthode : session ou BDD)
$wish_count = 0;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    // Utilisateur connecté → lire depuis la BDD
    $wStmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $wStmt->execute([$_SESSION['user_id']]);
    $wish_count = $wStmt->fetchColumn();
} elseif (isset($_SESSION['wishlist'])) {
    // Invité → lire depuis la session
    $wish_count = count($_SESSION['wishlist']);
}

// Déterminer la page active pour la navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= isset($page_title) ? $page_title . ' — M&C Fashion' : 'M&C Fashion' ?></title>

  <!-- Police Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <!-- Font Awesome pour les icônes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" />
  <!-- Swiper CSS (slider hero) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
  <!-- Feuille de style principale -->
  <link rel="stylesheet" href="assets/style.css" />
  <?= isset($extra_css) ? $extra_css : '' ?>
</head>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>My Fashion Store</title>
    <link rel="stylesheet" href="style5.css">
</head>
<body>
    <?php echo "<h1>Hello World</h1>"; ?>


<!-- ===================== HEADER ===================== -->

<header class="site-header" id="site-header">
  <div class="container header-inner">

    <!-- Logo -->
    <a href="index.php" class="logo">M<em>&amp;</em>C <span>FASHION</span></a>

    <!-- Navigation principale -->
    <nav class="main-nav" id="main-nav">
      <a href="index.php"         class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a>
      <a href="index.php#products" >Products</a>
      <a href="index.php#bestsellers">Best Sellers</a>
      <a href="contact.php"       class="<?= $current_page == 'contact.php' ? 'active' : '' ?>">Contact</a>
    </nav>

    <!-- Boutons à droite : wishlist, panier, auth -->
    <div class="header-actions">

      <!-- Bouton Wishlist -->
      <a href="wishlist.php" class="icon-btn" title="My Wishlist">
        <i class="fas fa-heart"></i>
        <?php if ($wish_count > 0): ?>
          <span class="badge"><?= $wish_count ?></span>
        <?php endif; ?>
      </a>

      <!-- Bouton Panier -->
      <a href="cart.php" class="icon-btn" title="My Cart">
        <i class="fas fa-shopping-bag"></i>
        <?php if ($cart_count > 0): ?>
          <span class="badge"><?= $cart_count ?></span>
        <?php endif; ?>
      </a>

      <!-- Auth : connecté ou non -->
      <?php if (isset($_SESSION['user'])): ?>
        <div class="user-menu">
          <button class="icon-btn user-toggle" title="Account">
            <i class="fas fa-user-circle"></i>
          </button>
          <div class="user-dropdown">
            <span class="user-name">Hello, <?= htmlspecialchars($_SESSION['user']) ?></span>
            <a href="orders.php"><i class="fas fa-box"></i> My Orders</a>
            <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
            <a href="auth.php?action=logout" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="auth.php" class="btn-login">Login</a>
      <?php endif; ?>

      <!-- Bouton hamburger mobile -->
      <button class="menu-toggle" id="menu-toggle" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>
</header>
<!-- Spacer pour compenser le header fixe -->
<div class="header-spacer"></div>
