<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    $user = get_current_user_data();
    if ($user['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_my_posts.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Check if user is blocked
                if ($user['blocked'] == 1) {
                    $error = 'Your account has been blocked. Please contact an administrator.';
                } elseif (password_verify($password, $user['password'])) {
                    // Set session
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: user_my_posts.php');
                    }
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Login';
$current_page = 'login';
require_once 'includes/header.php';
?>

  <!-- Login Section -->
  <section class="login-page container">
    <?php if ($error): ?>
      <div class="login-card error-card" style="max-width: 380px;">
        <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
      </div>
    <?php endif; ?>
    
    <div class="login-card">
      <h2>Welcome Back</h2>

      <form method="POST" action="">
        <label>Email</label>
        <div class="input-icon">
          <span>📧</span>
          <input type="email" name="email" placeholder="your.email@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <label>Password</label>
        <div class="input-icon">
          <span>🔒</span>
          <input type="password" name="password" placeholder="•••••••" required>
        </div>

        <button type="submit" class="login-btn">Login</button>
      </form>

     <p class="register-text">
  Don't have an account? <a href="register.php">Register here</a>
</p>

    </div>
  </section>

<?php require_once 'includes/footer.php'; ?>
