<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$user_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Prevent blocking/deleting yourself
if ($user_id == get_current_user_id()) {
    header('Location: admin_users.php');
    exit;
}

// Fetch user
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: admin_users.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Error loading user: ' . $e->getMessage();
    $user = null;
}

// Handle block/unblock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_action']) && $user) {
    try {
        $new_blocked_status = $user['blocked'] == 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE users SET blocked = ? WHERE id = ?");
        $stmt->execute([$new_blocked_status, $user_id]);
        
        $action = $new_blocked_status == 1 ? 'blocked' : 'unblocked';
        $success = "User {$action} successfully!";
        header('Refresh: 1; url=admin_users.php');
    } catch (PDOException $e) {
        $error = 'Error updating user: ' . $e->getMessage();
    }
}

$page_title = $user && $user['blocked'] == 1 ? 'Unblock User' : 'Block User';
$current_page = 'admin_block_user';
require_once 'includes/header.php';
?>

<section class="container container-form">
  <h1 class="page-title"><?php echo $user && $user['blocked'] == 1 ? 'Unblock User' : 'Block User'; ?></h1>

  <?php if ($error): ?>
    <div class="card error-card">
      <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="card success-card">
      <p class="success-text"><?php echo htmlspecialchars($success); ?></p>
    </div>
  <?php else: ?>
    <?php if ($user): ?>
      <div class="card card-form">
        <div class="delete-warning">
          <div class="delete-icon"><?php echo $user['blocked'] == 1 ? '✅' : '🚫'; ?></div>
          <h2 class="delete-title">
            <?php echo $user['blocked'] == 1 ? 'Unblock User' : 'Block User'; ?>
          </h2>
          <p class="delete-text">
            <?php if ($user['blocked'] == 1): ?>
              This will allow the user to access their account again.
            <?php else: ?>
              This will prevent the user from logging in and using the platform.
            <?php endif; ?>
          </p>
        </div>

        <!-- User Info -->
        <div class="delete-preview">
          <h3 class="post-title" style="font-size: 1.2rem;"><?php echo htmlspecialchars($user['name']); ?></h3>
          <p class="post-meta">
            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?> | 
            <strong>Role:</strong> 
            <span class="admin-role-badge" style="background: <?php echo $user['role'] === 'admin' ? '#e3f2fd' : '#f3e5f5'; ?>; color: <?php echo $user['role'] === 'admin' ? '#1976d2' : '#7b1fa2'; ?>;">
              <?php echo ucfirst($user['role']); ?>
            </span>
            <?php if ($user['blocked'] == 1): ?>
              <span class="admin-blocked-label">(Currently Blocked)</span>
            <?php endif; ?>
          </p>
        </div>

        <form method="POST" action="" class="delete-form">
          <button type="submit" name="confirm_action" value="1" class="login <?php echo $user['blocked'] == 1 ? 'admin-block-btn' : 'delete-btn-confirm'; ?>" style="background: <?php echo $user['blocked'] == 1 ? '#ffc107' : '#dc3545'; ?>;">
            <?php echo $user['blocked'] == 1 ? 'Yes, Unblock User' : 'Yes, Block User'; ?>
          </button>
          <a href="admin_users.php" class="login btn-cancel">
            Cancel
          </a>
        </form>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
