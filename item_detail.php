<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Get and validate post ID from URL parameter
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($post_id <= 0) {
    $error = 'Invalid item ID. Please select a valid item.';
    $post = null;
} else {
    // Fetch post with user information using prepared statement
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.name as user_name, u.id as user_id
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            $error = 'Item not found. It may have been deleted or does not exist.';
        }
    } catch (PDOException $e) {
        $error = 'Error loading item: ' . $e->getMessage();
        $post = null;
    }
}

// Determine user permissions for action buttons
$is_logged_in = is_logged_in();
$current_user_id = $is_logged_in ? get_current_user_id() : null;
$is_admin = $is_logged_in && is_admin();
$is_owner = $post && $is_logged_in && $current_user_id == $post['user_id'];

$page_title = $post ? htmlspecialchars($post['title']) . ' — FindIt' : 'Item Not Found — FindIt';
$current_page = 'item_detail';
require_once 'includes/header.php';
?>

<!-- Item Detail -->
<section class="container container-list">
  <div style="margin-bottom: 20px;">
    <a href="index.php" class="login" style="text-decoration: none; display: inline-block; margin-bottom: 16px;">
      ← Back to results
    </a>
  </div>

  <?php if (isset($error) || !$post): ?>
    <div class="card error-card">
      <p class="error-text"><?php echo htmlspecialchars($error ?? 'Item not found.'); ?></p>
      <div style="margin-top: 16px;">
        <a href="index.php" class="login">Browse all items</a>
      </div>
    </div>
  <?php else: ?>
    <div class="card card-list-item">
      <!-- Header with status badge and title -->
      <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div style="flex: 1;">
          <span class="status-badge" style="background: <?php echo $post['status'] === 'lost' ? '#fee' : '#efe'; ?>; color: <?php echo $post['status'] === 'lost' ? '#c33' : '#3c3'; ?>;">
            <?php echo ucfirst(htmlspecialchars($post['status'])); ?>
          </span>
          <h1 class="post-title" style="margin-top: 12px; font-size: 1.8rem;">
            <?php echo htmlspecialchars($post['title']); ?>
          </h1>
        </div>
      </div>

      <!-- Item image -->
      <?php if ($post['image']): ?>
        <div class="post-image" style="margin-bottom: 20px;">
          <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
        </div>
      <?php endif; ?>

      <!-- Item details -->
      <div style="margin-bottom: 20px;">
        <p class="post-meta" style="margin-bottom: 8px;">
          <strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?>
        </p>
        <p class="post-meta" style="margin-bottom: 8px;">
          <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?>
        </p>
        <p class="post-meta" style="margin-bottom: 8px;">
          <strong>Date:</strong> <?php echo date('M d, Y', strtotime($post['date'])); ?>
        </p>
        <?php if (!empty($post['user_name'])): ?>
          <p class="post-meta" style="margin-bottom: 8px;">
            <strong>Posted by:</strong> <?php echo htmlspecialchars($post['user_name']); ?>
          </p>
        <?php endif; ?>
        <p class="post-meta">
          <strong>Posted on:</strong> <?php echo date('M d, Y g:i A', strtotime($post['created_at'])); ?>
        </p>
      </div>

      <!-- Description -->
      <div style="margin-bottom: 24px;">
        <h2 style="font-size: 1.2rem; margin-bottom: 12px; color: #0f172a;">Description</h2>
        <p class="post-description">
          <?php echo nl2br(htmlspecialchars($post['description'])); ?>
        </p>
      </div>

      <!-- Action buttons based on user role and ownership -->
      <div class="btn-group" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #eee;">
        <?php if ($is_logged_in): ?>
          <?php if ($is_owner): ?>
            <!-- Owner actions: Edit and Delete -->
            <a href="user_edit_post.php?id=<?php echo $post_id; ?>" class="login btn-action">
              Edit
            </a>
            <a href="user_delete_post.php?id=<?php echo $post_id; ?>" 
               class="login btn-action btn-delete"
               onclick="return confirm('Are you sure you want to delete this post?');">
              Delete
            </a>
          <?php elseif ($is_admin): ?>
            <!-- Admin actions: Delete (admin can delete any post) -->
            <a href="admin_delete_post.php?id=<?php echo $post_id; ?>" 
               class="login admin-action-btn admin-delete-btn">
              Delete (Admin)
            </a>
            <a href="user_messages.php?post_id=<?php echo $post_id; ?>" class="login btn-action">
              Contact Owner
            </a>
          <?php else: ?>
            <!-- Logged in user (not owner): Contact Owner -->
            <a href="user_messages.php?post_id=<?php echo $post_id; ?>" class="login btn-action">
              Contact Owner
            </a>
          <?php endif; ?>
        <?php else: ?>
          <!-- Not logged in: Show login prompt -->
          <p style="color: #666; margin-bottom: 12px;">
            Want to contact the owner? <a href="login.php" style="color: #009fb7;">Login or register</a>
          </p>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
