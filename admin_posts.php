<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

// Get filter parameters
$filter_status = $_GET['status'] ?? '';
$filter_category = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($filter_status) && in_array($filter_status, ['lost', 'found'])) {
    $where_conditions[] = "p.status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_category) && $filter_category !== 'All') {
    $where_conditions[] = "p.category = ?";
    $params[] = $filter_category;
}

if (!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
    $search_term = "%{$search}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Fetch all posts
try {
    $sql = "SELECT p.*, u.name as user_name, u.email as user_email
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id";
    
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
    
    // Get unique categories for filter dropdown
    $stmt = $pdo->query("SELECT DISTINCT category FROM posts ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = 'Error loading posts: ' . $e->getMessage();
    $posts = [];
    $categories = [];
}

$page_title = 'Manage Posts';
$current_page = 'admin_posts';
require_once 'includes/header.php';
?>

<!-- Manage Posts -->
<section class="container admin-container">
  <h1 class="admin-title admin-title-page">
    Manage Posts
  </h1>

  <div class="admin-back-link">
    <a href="admin_dashboard.php" class="login admin-back-btn">← Back to Dashboard</a>
  </div>

  <?php if (isset($error)): ?>
    <div class="card admin-error-card">
      <p class="admin-error-text"><?php echo htmlspecialchars($error); ?></p>
    </div>
  <?php endif; ?>

  <!-- Filters -->
  <div class="card admin-filters">
    <form method="GET" action="admin_posts.php" class="admin-filters-form">
      <div class="admin-filter-group">
        <label class="admin-filter-label">Search</label>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search title, description, location..." 
               class="admin-filter-input">
      </div>
      
      <div class="admin-filter-group-small">
        <label class="admin-filter-label">Status</label>
        <select name="status" class="admin-filter-select">
          <option value="">All Status</option>
          <option value="lost" <?php echo $filter_status === 'lost' ? 'selected' : ''; ?>>Lost</option>
          <option value="found" <?php echo $filter_status === 'found' ? 'selected' : ''; ?>>Found</option>
        </select>
      </div>
      
      <div class="admin-filter-group-small">
        <label class="admin-filter-label">Category</label>
        <select name="category" class="admin-filter-select">
          <option value="All">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" 
                    <?php echo $filter_category === $cat ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div>
        <button type="submit" class="login admin-action-btn admin-filter-btn">
          Filter
        </button>
      </div>
      
      <?php if (!empty($search) || !empty($filter_status) || !empty($filter_category)): ?>
        <div>
          <a href="admin_posts.php" class="login btn-cancel admin-filter-btn">
            Clear
          </a>
        </div>
      <?php endif; ?>
    </form>
  </div>

  <?php if (empty($posts)): ?>
    <div class="card admin-empty">
      <p>No posts found</p>
    </div>
  <?php else: ?>
    <div class="card admin-table-wrapper">
      <table class="admin-table">
        <thead>
          <tr class="admin-table-header">
            <th class="admin-table-th">Title</th>
            <th class="admin-table-th">Status</th>
            <th class="admin-table-th">Category</th>
            <th class="admin-table-th">Location</th>
            <th class="admin-table-th">User</th>
            <th class="admin-table-th">Date</th>
            <th class="admin-table-th">Created</th>
            <th class="admin-table-th admin-table-th-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
            <tr class="admin-table-row">
              <td class="admin-table-td admin-table-td-name">
                <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                <?php if ($post['image']): ?>
                  <span style="color: #666; font-size: 0.85rem;">📷</span>
                <?php endif; ?>
              </td>
              <td class="admin-table-td">
                <span class="admin-status-badge" style="background: <?php echo $post['status'] === 'lost' ? '#fee' : '#efe'; ?>; color: <?php echo $post['status'] === 'lost' ? '#c33' : '#3c3'; ?>;">
                  <?php echo ucfirst($post['status']); ?>
                </span>
              </td>
              <td class="admin-table-td"><?php echo htmlspecialchars($post['category']); ?></td>
              <td class="admin-table-td"><?php echo htmlspecialchars($post['location']); ?></td>
              <td class="admin-table-td">
                <div>
                  <div><?php echo htmlspecialchars($post['user_name'] ?? 'Unknown'); ?></div>
                  <div class="admin-post-user"><?php echo htmlspecialchars($post['user_email'] ?? ''); ?></div>
                </div>
              </td>
              <td class="admin-table-td admin-table-td-date"><?php echo date('M d, Y', strtotime($post['date'])); ?></td>
              <td class="admin-table-td admin-table-td-date"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
              <td class="admin-table-td admin-table-td-center">
                <div class="admin-action-group">
                  <a href="index.php?view_post=<?php echo $post['id']; ?>" 
                     class="login admin-action-btn admin-view-btn">
                    View
                  </a>
                  <a href="admin_delete_post.php?id=<?php echo $post['id']; ?>" 
                     class="login admin-action-btn admin-delete-btn">
                    Delete
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="admin-post-count">
      <strong>Total:</strong> <?php echo count($posts); ?> post(s)
    </div>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
