<?php
include 'db.php';
session_start();

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// --- Action : Déconnexion ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: auth.php?msg=logged_out");
    exit;
}

$login_error    = '';
$register_error = '';
$active_tab     = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';

// --- Action : Connexion ---
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $login_error = 'Please fill in all fields.';
        $active_tab = 'login';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user']    = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['flash']   = ['type' => 'success', 'msg' => 'Welcome back, ' . $user['username'] . '!'];

            // Fusionner la wishlist invité avec la wishlist BDD
            if (!empty($_SESSION['wishlist'])) {
                foreach ($_SESSION['wishlist'] as $pid) {
                    $ins = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
                    $ins->execute([$user['id'], $pid]);
                }
            }
            // Recharger la wishlist depuis la BDD
            $wRows = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
            $wRows->execute([$user['id']]);
            $_SESSION['wishlist'] = array_column($wRows->fetchAll(), 'product_id');

            // Rediriger vers la page demandée ou l'accueil
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
            exit;
        } else {
            $login_error = 'Incorrect username or password.';
            $active_tab = 'login';
        }
    }
}

// --- Action : Inscription ---
if (isset($_POST['register'])) {
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $password2 = $_POST['password2'];
    $active_tab = 'register';

    // Validation simple
    if (empty($username) || empty($email) || empty($password)) {
        $register_error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $register_error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $register_error = 'Passwords do not match.';
    } else {
        // Vérifier si le nom d'utilisateur ou l'email existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);

        if ($check->rowCount() > 0) {
            $register_error = 'Username or email already taken.';
        } else {
            // Créer le compte avec mot de passe hashé
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins    = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $ins->execute([$username, $email, $hashed]);

            // Connecter automatiquement
            $new_id = $pdo->lastInsertId();
            $_SESSION['user']    = $username;
            $_SESSION['user_id'] = $new_id;
            $_SESSION['flash']   = ['type' => 'success', 'msg' => 'Account created! Welcome, ' . $username . '!'];

            header("Location: index.php");
            exit;
        }
    }
}

$page_title = 'Sign In';
include 'includes/header.php';
?>

<!-- Message flash -->
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out'): ?>
  <div class="flash-bar info">You have been logged out successfully.</div>
<?php endif; ?>

<!-- ===================== FORMULAIRE AUTH ===================== -->
<div class="auth-wrap">
  <div class="auth-card">

    <!-- Onglets : Login / Register -->
    <div class="auth-tabs">
      <button class="auth-tab <?= $active_tab === 'login' ? 'active' : '' ?>" data-tab="login">
        Sign In
      </button>
      <button class="auth-tab <?= $active_tab === 'register' ? 'active' : '' ?>" data-tab="register">
        Create Account
      </button>
    </div>

    <div class="auth-body">

      <!-- ===== FORMULAIRE LOGIN ===== -->
      <form id="form-login" class="auth-form" 
            style="<?= $active_tab !== 'login' ? 'display:none' : '' ?>"
            action="auth.php" method="POST">

        <?php if ($login_error): ?>
          <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="login-user">Username</label>
          <div class="input-icon-wrap">
            <i class="fas fa-user input-icon"></i>
            <input type="text" id="login-user" name="username" class="form-input"
                   placeholder="Your username" required autocomplete="username">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-pass">Password</label>
          <div class="input-icon-wrap">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="login-pass" name="password" class="form-input"
                   placeholder="Your password" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" name="login" class="btn btn-full" style="margin-top:8px;">Sign In</button>

        <p style="text-align:center; margin-top:16px; font-size:0.82rem; color:var(--light-text);">
          Don't have an account? 
          <a href="#" onclick="switchTab('register')" style="color:var(--green); font-weight:600;">Create one</a>
        </p>
      </form>

      <!-- ===== FORMULAIRE REGISTER ===== -->
      <form id="form-register" class="auth-form"
            style="<?= $active_tab !== 'register' ? 'display:none' : '' ?>"
            action="auth.php" method="POST">

        <?php if ($register_error): ?>
          <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($register_error) ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="reg-user">Username</label>
          <div class="input-icon-wrap">
            <i class="fas fa-user input-icon"></i>
            <input type="text" id="reg-user" name="username" class="form-input"
                   placeholder="Choose a username" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-email">Email</label>
          <div class="input-icon-wrap">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="reg-email" name="email" class="form-input"
                   placeholder="your@email.com" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-pass">Password</label>
          <div class="input-icon-wrap">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="reg-pass" name="password" class="form-input"
                   placeholder="Min. 6 characters" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-pass2">Confirm Password</label>
          <div class="input-icon-wrap">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="reg-pass2" name="password2" class="form-input"
                   placeholder="Repeat your password" required>
          </div>
        </div>

        <button type="submit" name="register" class="btn btn-full" style="margin-top:8px;">Create Account</button>

        <p style="text-align:center; margin-top:16px; font-size:0.82rem; color:var(--light-text);">
          Already have an account? 
          <a href="#" onclick="switchTab('login')" style="color:var(--green); font-weight:600;">Sign in</a>
        </p>
      </form>

    </div><!-- /.auth-body -->
  </div><!-- /.auth-card -->
</div><!-- /.auth-wrap -->

<script>
/* Changer d'onglet manuellement depuis les liens */
function switchTab(tab) {
  document.querySelectorAll('.auth-tab').forEach(function(t) {
    t.classList.toggle('active', t.getAttribute('data-tab') === tab);
  });
  document.getElementById('form-login').style.display    = (tab === 'login')    ? 'block' : 'none';
  document.getElementById('form-register').style.display = (tab === 'register') ? 'block' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
