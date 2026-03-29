<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$user_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Prevent deleting yourself
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

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $user) {
    try {
        // Get user's posts to delete associated images
        $stmt = $pdo->prepare("SELECT image FROM posts WHERE user_id = ? AND image IS NOT NULL");
        $stmt->execute([$user_id]);
        $posts_with_images = $stmt->fetchAll();
        
        // Delete image files
        foreach ($posts_with_images as $post) {
            if ($post['image'] && file_exists(__DIR__ . '/' . $post['image'])) {
                unlink(__DIR__ . '/' . $post['image']);
            }
        }
        
        // Delete user (posts and messages will be deleted via CASCADE)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $success = 'User deleted successfully!';
        header('Refresh: 1; url=admin_users.php');
    } catch (PDOException $e) {
        $error = 'Error deleting user: ' . $e->getMessage();
    }
}

if (!$user) {
    header('Location: admin_users.php');
    exit;
}

$page_title = 'Delete User';
$current_page = 'admin_delete_user';
require_once 'includes/header.php';
?>

<!-- Delete User Confirmation -->
<section class="container container-form">
  <h1 class="page-title">Delete User</h1>

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
    <div class="card card-form">
      <div class="delete-warning">
        <div class="delete-icon">⚠️</div>
        <h2 class="delete-title">Are you sure you want to delete this user?</h2>
        <p class="delete-text">This will permanently delete the user account, all their posts, messages, and associated images. This action cannot be undone.</p>
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
            <span class="admin-blocked-label">(Blocked)</span>
          <?php endif; ?>
        </p>
        <p class="post-meta" style="margin-top: 8px;">
          <strong>Account created:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
        </p>
      </div>

      <form method="POST" action="" class="delete-form">
        <button type="submit" name="confirm_delete" value="1" class="login delete-btn-confirm">
          Yes, Delete User
        </button>
        <a href="admin_users.php" class="login btn-cancel">
          Cancel
        </a>
      </form>
    </div>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
