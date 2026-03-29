<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_login();

// Check if user is blocked
if (is_user_blocked(get_current_user_id())) {
    header('Location: logout.php');
    exit;
}

$user_id = get_current_user_id();

// Fetch user's posts
try {
    $stmt = $pdo->prepare("
        SELECT id, status, title, category, description, location, date, image, created_at
        FROM posts
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $error = 'Error loading posts: ' . $e->getMessage();
}

$page_title = 'My Posts';
$current_page = 'my_posts';
require_once 'includes/header.php';
?>

<!-- My Posts -->
<section class="container container-list">
  <h1 class="page-title-list">My Posts</h1>

  <?php if (isset($error)): ?>
    <div class="card error-card">
      <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
    </div>
  <?php endif; ?>

  <?php if (empty($posts)): ?>
    <div class="card empty-state">
      <p class="empty-state-text">
        You haven't posted any items yet
      </p>
      <a href="user_new_post.php" class="login empty-state-btn">
        Create Your First Post
      </a>
    </div>
  <?php else: ?>
    <div class="grid-list">
      <?php foreach ($posts as $post): ?>
        <div class="card card-list-item">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; flex-wrap: wrap; gap: 12px;">
            <div style="flex: 1;">
              <span class="status-badge" style="background: <?php echo $post['status'] === 'lost' ? '#fee' : '#efe'; ?>; color: <?php echo $post['status'] === 'lost' ? '#c33' : '#3c3'; ?>;">
                <?php echo ucfirst($post['status']); ?>
              </span>
              <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
              <p class="post-meta">
                <strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?> | 
                <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?> | 
                <strong>Date:</strong> <?php echo date('M d, Y', strtotime($post['date'])); ?>
              </p>
            </div>
            <div class="btn-group">
              <a href="user_edit_post.php?id=<?php echo $post['id']; ?>" class="login btn-action">
                Edit
              </a>
              <a href="user_delete_post.php?id=<?php echo $post['id']; ?>" 
                 class="login btn-action btn-delete"
                 onclick="return confirm('Are you sure you want to delete this post?');">
                Delete
              </a>
            </div>
          </div>
          
          <p class="post-description">
            <?php echo nl2br(htmlspecialchars($post['description'])); ?>
          </p>
          
          <?php if ($post['image']): ?>
            <div class="post-image">
              <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
          <?php endif; ?>
          
          <p class="post-date">
            Posted on <?php echo date('M d, Y g:i A', strtotime($post['created_at'])); ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>

