<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

$post_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Fetch post (admin can delete any post)
try {
    $stmt = $pdo->prepare("SELECT p.*, u.name as user_name FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('Location: admin_dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Error loading post: ' . $e->getMessage();
    $post = null;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $post) {
    try {
        // Delete image file if exists
        if ($post['image'] && file_exists(__DIR__ . '/' . $post['image'])) {
            unlink(__DIR__ . '/' . $post['image']);
        }
        
        // Delete post (messages will be deleted via CASCADE)
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        
        $success = 'Post deleted successfully!';
        header('Refresh: 1; url=admin_dashboard.php');
    } catch (PDOException $e) {
        $error = 'Error deleting post: ' . $e->getMessage();
    }
}

if (!$post) {
    header('Location: admin_dashboard.php');
    exit;
}

$page_title = 'Delete Post';
$current_page = 'admin_delete_post';
require_once 'includes/header.php';
?>

<!-- Delete Post Confirmation -->
<section class="container container-form">
  <h1 class="page-title">Delete Post</h1>

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
        <h2 class="delete-title">Are you sure you want to delete this post?</h2>
        <p class="delete-text">This action cannot be undone.</p>
      </div>

      <!-- Post Preview -->
      <div class="delete-preview">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
          <div>
            <span class="status-badge" style="background: <?php echo $post['status'] === 'lost' ? '#fee' : '#efe'; ?>; color: <?php echo $post['status'] === 'lost' ? '#c33' : '#3c3'; ?>;">
              <?php echo ucfirst($post['status']); ?>
            </span>
            <h3 class="post-title" style="font-size: 1.2rem;"><?php echo htmlspecialchars($post['title']); ?></h3>
            <p class="post-meta">
              <strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?> | 
              <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?>
              <?php if (!empty($post['user_name'])): ?>
                | <strong>Posted by:</strong> <?php echo htmlspecialchars($post['user_name']); ?>
              <?php endif; ?>
            </p>
          </div>
        </div>
        <p class="post-description">
          <?php echo nl2br(htmlspecialchars(substr($post['description'], 0, 150))); ?><?php echo strlen($post['description']) > 150 ? '...' : ''; ?>
        </p>
      </div>

      <form method="POST" action="" class="delete-form">
        <button type="submit" name="confirm_delete" value="1" class="login delete-btn-confirm">
          Yes, Delete Post
        </button>
        <a href="admin_dashboard.php" class="login btn-cancel">
          Cancel
        </a>
      </form>
    </div>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
