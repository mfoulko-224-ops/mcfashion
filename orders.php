<?php


include 'db.php';
session_start();

// Connexion obligatoire
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = 'orders.php';
    header("Location: auth.php");
    exit;
}

// --- Vue détail d'une commande ---
$order_detail   = null;
$order_items_detail = [];
if (isset($_GET['order_id'])) {
    $oid = intval($_GET['order_id']);
    // Vérifier que la commande appartient bien à l'utilisateur
    $stmtO = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmtO->execute([$oid, $_SESSION['user_id']]);
    $order_detail = $stmtO->fetch();

    if ($order_detail) {
        $stmtI = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtI->execute([$oid]);
        $order_items_detail = $stmtI->fetchAll();
    }
}

// Récupérer toutes les commandes de l'utilisateur (plus récentes en premier)
$stmtOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmtOrders->execute([$_SESSION['user_id']]);
$orders = $stmtOrders->fetchAll();

$page_title = 'My Orders';
include 'includes/header.php';
?>

<!-- En-tête de page -->
 <div class="page-hero">
  <div class="container">
    <h1>My Orders</h1>
    <p class="breadcrumb"><a href="index.php">Home</a> / My Orders</p>
  </div>
</div>

<!-- ===================== LISTE DES COMMANDES ===================== -->
<section class="section">
  <div class="container">

    <?php if (empty($orders)): ?>
      <!-- Aucune commande -->
      <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3>No orders yet</h3>
        <p>You haven't placed any orders. Start shopping!</p>
        <a href="index.php#products" class="btn">Shop Now</a>
      </div>

    <?php else: ?>
      <div style="background:var(--white); border-radius:8px; overflow:hidden; box-shadow:var(--shadow);">
        <table class="orders-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Date</th>
              <th>Items</th>
              <th>Total</th>
              <th>Status</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order):
              // Compter les articles de la commande
              $countStmt = $pdo->prepare("SELECT SUM(quantity) FROM order_items WHERE order_id = ?");
              $countStmt->execute([$order['id']]);
              $item_count = $countStmt->fetchColumn();
            ?>
              <tr>
                <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                <td><?= $item_count ?> item<?= $item_count > 1 ? 's' : '' ?></td>
                <td><strong><?= number_format($order['total_amount'], 2) ?> MAD</strong></td>
                <td>
                  <span class="status-badge status-<?= $order['status'] ?>">
                    <?= ucfirst($order['status']) ?>
                  </span>
                </td>
                <td>
                  <a href="orders.php?order_id=<?= $order['id'] ?>" 
                     class="btn btn-outline btn-sm">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- Modal détail commande (s'affiche si order_id dans l'URL) -->
<?php if ($order_detail): ?>
<div class="modal-overlay open" id="modalOrderDetail">
  <div class="modal modal-lg">
    <div class="modal-head">
      <h3>Order #<?= str_pad($order_detail['id'], 6, '0', STR_PAD_LEFT) ?></h3>
      <button class="modal-close" onclick="closeModal('modalOrderDetail')">&times;</button>
    </div>
    <div class="modal-body">

      <!-- Infos commande -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; 
                  background:var(--bg); padding:16px; border-radius:6px;">
        <div>
          <p style="font-size:0.8rem; color:var(--light-text); text-transform:uppercase; letter-spacing:0.08em;">Customer</p>
          <p style="font-weight:500;"><?= htmlspecialchars($order_detail['customer_name']) ?></p>
          <p style="font-size:0.88rem; color:var(--mid);"><?= htmlspecialchars($order_detail['customer_email']) ?></p>
        </div>
        <div>
          <p style="font-size:0.8rem; color:var(--light-text); text-transform:uppercase; letter-spacing:0.08em;">Shipping to</p>
          <p style="font-weight:500;"><?= htmlspecialchars($order_detail['customer_address']) ?></p>
        </div>
        <div>
          <p style="font-size:0.8rem; color:var(--light-text); text-transform:uppercase; letter-spacing:0.08em;">Order Date</p>
          <p><?= date('d F Y, H:i', strtotime($order_detail['created_at'])) ?></p>
        </div>
        <div>
          <p style="font-size:0.8rem; color:var(--light-text); text-transform:uppercase; letter-spacing:0.08em;">Status</p>
          <span class="status-badge status-<?= $order_detail['status'] ?>"><?= ucfirst($order_detail['status']) ?></span>
        </div>
      </div>

      <!-- Articles commandés -->
      <h4 style="margin-bottom:14px; color:var(--green); font-family:var(--font-serif);">Items Ordered</h4>
      <?php foreach ($order_items_detail as $item): ?>
        <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--border);">
          <div>
            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
            <small style="display:block; color:var(--light-text);">Qty: <?= $item['quantity'] ?> × <?= number_format($item['product_price'], 2) ?> MAD</small>
          </div>
          <strong><?= number_format($item['product_price'] * $item['quantity'], 2) ?> MAD</strong>
        </div>
      <?php endforeach; ?>

      <!-- Total -->
      <div style="display:flex; justify-content:flex-end; margin-top:16px; font-size:1.1rem; font-weight:700; color:var(--green);">
        Total: <?= number_format($order_detail['total_amount'], 2) ?> MAD
      </div>

      <div style="margin-top:24px;">
        <a href="orders.php" class="btn btn-outline btn-sm">← Back to Orders</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
