<?php
/* =====================================================
   FICHIER PARTIEL : Pied de page commun à toutes les pages
   ===================================================== */
?>

<!-- ===================== FOOTER ===================== -->


<footer class="site-footer">
  <div class="container footer-grid">

    <div class="footer-col">
      <h3 class="logo-footer">M<em>&amp;</em>C <span>FASHION</span></h3>
      <p>Your destination for timeless and contemporary fashion. Style for every moment.</p>
      <div class="social-links">
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
        <a href="https://wa.me/212722582598" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      </div>
    </div>

    <div class="footer-col">
      <h4>Quick Links</h4>
      <a href="index.php">Home</a>
      <a href="index.php#products">Products</a>
      <a href="index.php#bestsellers">Best Sellers</a>
      <a href="contact.php">Contact Us</a>
    </div>

    <div class="footer-col">
      <h4>My Account</h4>
      <?php if (isset($_SESSION['user'])): ?>
        <a href="orders.php">My Orders</a>
        <a href="wishlist.php">My Wishlist</a>
        <a href="cart.php">My Cart</a>
        <a href="auth.php?action=logout">Logout</a>
      <?php else: ?>
        <a href="auth.php">Login</a>
        <a href="auth.php?tab=register">Create Account</a>
        <a href="cart.php">My Cart</a>
      <?php endif; ?>
    </div>

    <div class="footer-col">
      <h4>Contact</h4>
      <p><i class="fas fa-map-marker-alt"></i> Fashion District, Oujda</p>
      <p><i class="fas fa-phone"></i> +212 722 582 598</p>
      <p><i class="fas fa-envelope"></i> m.foulko-224@ump.ac.ma</p>
    </div>

  </div>

  <div class="footer-bottom">
    <div class="container">
      <p>© <?= date('Y') ?> M&C Fashion. All rights reserved.</p>
    </div>
  </div>
</footer>

<!-- Swiper JS (utilisé sur la page d'accueil) -->
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<!-- Script JS principal -->
<script src="main.js"></script></body>
</html>
