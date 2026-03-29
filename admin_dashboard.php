<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

// Fetch statistics
try {
    // Total Posts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
    $total_posts = $stmt->fetch()['count'];
    
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $total_users = $stmt->fetch()['count'];
    
    // Lost Items count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM posts WHERE status = 'lost'");
    $stmt->execute();
    $lost_items = $stmt->fetch()['count'];
    
    // Recent Posts (last 5)
    $stmt = $pdo->query("
        SELECT p.*, u.name as user_name 
        FROM posts p 
        LEFT JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ");
    $recent_posts = $stmt->fetchAll();
    
    // Recent Users (last 5)
    $stmt = $pdo->query("
        SELECT * FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading statistics: ' . $e->getMessage();
    $total_posts = 0;
    $total_users = 0;
    $lost_items = 0;
    $recent_posts = [];
    $recent_users = [];
}

$page_title = 'Admin Dashboard';
$current_page = 'admin_dashboard';
require_once 'includes/header.php';
?>

<!-- Admin Dashboard -->
<section class="container admin-container">
  <h1 class="admin-title">
    Admin Dashboard
  </h1>

  <?php if (isset($error)): ?>
    <div class="card admin-error-card">
      <p class="admin-error-text"><?php echo htmlspecialchars($error); ?></p>
    </div>
  <?php endif; ?>

  <!-- Stats Cards -->
  <div class="admin-stats">
    <div class="card admin-stat-card">
      <div class="admin-stat-icon">📝</div>
      <h3 class="admin-stat-title">Total Posts</h3>
      <p class="admin-stat-number"><?php echo $total_posts; ?></p>
    </div>
    
    <div class="card admin-stat-card">
      <div class="admin-stat-icon">👥</div>
      <h3 class="admin-stat-title">Total Users</h3>
      <p class="admin-stat-number"><?php echo $total_users; ?></p>
    </div>
    
    <div class="card admin-stat-card">
      <div class="admin-stat-icon">🔍</div>
      <h3 class="admin-stat-title">Lost Items</h3>
      <p class="admin-stat-number"><?php echo $lost_items; ?></p>
    </div>
  </div>

  <!-- Quick Links -->
  <div class="admin-quick-links">
    <a href="admin_posts.php" class="login admin-quick-link">Manage Posts</a>
    <a href="admin_users.php" class="login admin-quick-link">Manage Users</a>
  </div>

  <!-- Recent Posts -->
  <div class="admin-section">
    <div class="admin-section-head">
      <h2 class="admin-section-title">Recent Posts</h2>
      <a href="admin_posts.php" class="login admin-view-all">View All →</a>
    </div>
    
    <?php if (empty($recent_posts)): ?>
      <div class="card admin-empty">
        <p>No posts found</p>
      </div>
    <?php else: ?>
      <div class="admin-list">
        <?php foreach ($recent_posts as $post): ?>
          <div class="card admin-list-item">
            <div class="admin-list-content">
              <div class="admin-list-main">
                <span class="admin-status-badge" style="background: <?php echo $post['status'] === 'lost' ? '#fee' : '#efe'; ?>; color: <?php echo $post['status'] === 'lost' ? '#c33' : '#3c3'; ?>;">
                  <?php echo ucfirst($post['status']); ?>
                </span>
                <h3 class="admin-list-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                <p class="admin-list-meta">
                  <strong>User:</strong> <?php echo htmlspecialchars($post['user_name'] ?? 'Unknown'); ?> | 
                  <strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?> | 
                  <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?>
                </p>
              </div>
              <a href="admin_delete_post.php?id=<?php echo $post['id']; ?>" 
                 class="login admin-action-btn admin-delete-btn">
                Delete
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Recent Users -->
  <div class="admin-section">
    <div class="admin-section-head">
      <h2 class="admin-section-title">Recent Users</h2>
      <a href="admin_users.php" class="login admin-view-all">View All →</a>
    </div>
    
    <?php if (empty($recent_users)): ?>
      <div class="card admin-empty">
        <p>No users found</p>
      </div>
    <?php else: ?>
      <div class="admin-list">
        <?php foreach ($recent_users as $user): ?>
          <div class="card admin-list-item">
            <div class="admin-list-content">
              <div class="admin-list-main">
                <h3 class="admin-list-title"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="admin-list-meta">
                  <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?> | 
                  <strong>Role:</strong> 
                  <span class="admin-role-badge" style="background: <?php echo $user['role'] === 'admin' ? '#e3f2fd' : '#f3e5f5'; ?>; color: <?php echo $user['role'] === 'admin' ? '#1976d2' : '#7b1fa2'; ?>;">
                    <?php echo ucfirst($user['role']); ?>
                  </span>
                  <?php if ($user['blocked'] == 1): ?>
                    <span class="admin-blocked-label">(Blocked)</span>
                  <?php endif; ?>
                </p>
              </div>
              <?php if ($user['id'] != get_current_user_id()): ?>
                <div class="admin-action-group">
                  <a href="admin_block_user.php?id=<?php echo $user['id']; ?>" 
                     class="login admin-action-btn admin-block-btn">
                    <?php echo $user['blocked'] == 1 ? 'Unblock' : 'Block'; ?>
                  </a>
                  <a href="admin_delete_user.php?id=<?php echo $user['id']; ?>" 
                     class="login admin-action-btn admin-delete-btn">
                    Delete
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

