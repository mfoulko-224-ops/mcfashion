<?php
/* =====================================================
   PAGE CONTACT — contact.php
   Formulaire de contact qui sauvegarde en BDD
   ===================================================== */

include 'db.php';
session_start();

$success = false;
$error   = '';

// Traitement du formulaire de contact
if (isset($_POST['send_contact'])) {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Sauvegarder le message en BDD
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $success = true;
    }
}

$page_title = 'Contact';
include 'includes/header.php';
?>

<!-- En-tête de page -->
<div class="page-hero">
  <div class="container">
    <h1>Contact Us</h1>
    <p class="breadcrumb"><a href="index.php">Home</a> / Contact</p>
  </div>
</div>

<!-- ===================== CONTACT ===================== -->
<section class="section">
  <div class="container">
    <div class="contact-grid">

      <!-- Informations de contact -->
      <div class="contact-info">
        <h3>Get In Touch</h3>
        <p style="color:var(--mid); margin-bottom:32px; line-height:1.8;">
          Have a question, feedback, or need help with your order? 
          We'd love to hear from you. Our team usually responds within 24 hours.
        </p>

        <div class="contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <div>
            <strong>Address</strong>
            <p style="color:var(--mid); margin:0;">Fashion District, Oujda, Morocco</p>
          </div>
        </div>

        <div class="contact-item">
          <i class="fas fa-phone"></i>
          <div>
            <strong>Phone</strong>
            <p style="color:var(--mid); margin:0;">+212 722 582 598</p>
          </div>
        </div>

        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <div>
            <strong>Email</strong>
            <p style="color:var(--mid); margin:0;">m.foulko-224@ump.ac.ma</p>
          </div>
        </div>

        <div class="contact-item">
          <i class="fab fa-whatsapp"></i>
          <div>
            <strong>WhatsApp</strong>
            <a href="https://wa.me/212722582598" style="color:var(--green);">Chat with us</a>
          </div>
        </div>

        <!-- Horaires -->
        <div style="margin-top:30px; padding:20px; background:var(--bg); border-radius:8px;">
          <h4 style="color:var(--green); margin-bottom:12px;">Opening Hours</h4>
          <p style="color:var(--mid); font-size:0.9rem; margin-bottom:6px;">Monday – Friday: 9:00 – 18:00</p>
          <p style="color:var(--mid); font-size:0.9rem;">Saturday: 10:00 – 15:00</p>
        </div>
      </div>

      <!-- Formulaire de contact -->
      <div>
        <?php if ($success): ?>
          <!-- Message de succès -->
          <div style="text-align:center; padding:60px 30px; background:var(--white); border-radius:10px; box-shadow:var(--shadow);">
            <div style="width:70px; height:70px; background:#eafaf1; border-radius:50%; 
                        display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
              <i class="fas fa-check" style="font-size:1.8rem; color:#1d6a3e;"></i>
            </div>
            <h3 style="font-family:var(--font-serif); color:var(--green); margin-bottom:10px;">Message Sent!</h3>
            <p style="color:var(--mid); margin-bottom:24px;">Thank you for reaching out. We'll get back to you soon.</p>
            <a href="contact.php" class="btn btn-outline">Send Another Message</a>
          </div>
        <?php else: ?>
          <!-- Formulaire -->
          <div style="background:var(--white); padding:40px; border-radius:10px; box-shadow:var(--shadow);">
            <h3 style="font-family:var(--font-serif); color:var(--green); margin-bottom:24px; font-size:1.5rem;">
              Send a Message
            </h3>

            <?php if ($error): ?>
              <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="contact.php" method="POST">
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                  <label class="form-label">Your Name *</label>
                  <input type="text" name="name" class="form-input" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Email Address *</label>
                  <input type="email" name="email" class="form-input" placeholder="your@email.com" required>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-input" placeholder="What's this about?">
              </div>

              <div class="form-group">
                <label class="form-label">Message *</label>
                <textarea name="message" class="form-input" rows="6" 
                          placeholder="Write your message here..." required
                          style="resize:vertical;"></textarea>
              </div>

              <button type="submit" name="send_contact" class="btn btn-full">
                <i class="fas fa-paper-plane"></i> Send Message
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>