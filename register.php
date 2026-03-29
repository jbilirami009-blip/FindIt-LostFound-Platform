<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
        } else {
            try {
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered.';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$name, $email, $password_hash]);
                
                $success = 'Registration successful! Redirecting to login...';
                header('Refresh: 2; url=login.php');
            }
        } catch (PDOException $e) {
            $error = 'Registration error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Register';
$current_page = 'register';
require_once 'includes/header.php';
?>

  <!-- Register Section -->
  <section class="login-page container">
    <?php if ($error): ?>
      <div class="login-card error-card" style="max-width: 380px;">
        <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
      </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="login-card success-card" style="max-width: 380px;">
        <p class="success-text"><?php echo htmlspecialchars($success); ?></p>
      </div>
    <?php endif; ?>
    
    <div class="login-card">
      <h2>Create Account</h2>

      <form method="POST" action="">
        <label>Full Name</label>
        <div class="input-icon">
          <span>👤</span>
          <input type="text" name="name" placeholder="Rami Jbili" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>

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

        <label>Confirm Password</label>
        <div class="input-icon">
          <span>🔒</span>
          <input type="password" name="confirm_password" placeholder="•••••••" required>
        </div>

        <button type="submit" class="login-btn">Create Account</button>
      </form>

      <p class="register-text">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </div>
  </section>

<?php require_once 'includes/footer.php'; ?>
