<?php
require_once 'config/db.php';

// Get search parameters
$keyword = trim($_GET['q'] ?? '');
$category = $_GET['cat'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($keyword)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $search_term = "%{$keyword}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($category) && $category !== 'All') {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

// Build final query
$sql = "SELECT p.*, u.name as user_name 
        FROM posts p 
        LEFT JOIN users u ON p.user_id = u.id";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY p.created_at DESC";

// Execute query
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $error = 'Error loading posts: ' . $e->getMessage();
}

$page_title = 'FindIt — Lost & Found';
$current_page = 'index';
require_once 'includes/header.php';
?>

  <!-- Hero -->
  <header class="hero container">
    <h1>Lost Something? Found Something?</h1>
    <p>Connect with your community to recover lost items</p>

    <div class="cta-row">
      <a href="user_new_post.php?status=lost" class="cta-primary">Report Lost Item</a>
      <a href="user_new_post.php?status=found" class="cta-primary">Found Something?</a>
    </div>

    <!-- Search bar -->
    <div class="search-wrap">
      <form class="search" method="get" action="index.php">
        <label class="field">
          <svg width="20" height="20" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          <input name="q" placeholder="Search items, locations, descriptions…" value="<?php echo htmlspecialchars($keyword); ?>">
        </label>
        <select name="cat">
          <option value="All" <?php echo ($category === '' || $category === 'All') ? 'selected' : ''; ?>>All</option>
          <option value="Electronics" <?php echo $category === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
          <option value="Documents" <?php echo $category === 'Documents' ? 'selected' : ''; ?>>Documents</option>
          <option value="Clothing" <?php echo $category === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
          <option value="Bags" <?php echo $category === 'Bags' ? 'selected' : ''; ?>>Bags</option>
          <option value="Accessories" <?php echo $category === 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
          <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>Other</option>
        </select>
        <button type="submit">Search</button>
      </form>
    </div>
  </header>

  <!-- Results -->
  <main class="container">
    <div class="section-head">
      <h2><?php echo (!empty($keyword) || (!empty($category) && $category !== 'All')) ? 'Search Results' : 'Recent Items'; ?></h2>
      <span class="count"><?php echo count($posts); ?> item<?php echo count($posts) !== 1 ? 's' : ''; ?> found</span>
    </div>

    <?php if (isset($error)): ?>
      <div class="card error-card">
        <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
      </div>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
      <section class="card empty">
        <div class="big-icon">🔍</div>
        <h3>No items found</h3>
        <p>Try adjusting your search or filters</p>
      </section>
    <?php else: ?>
      <div class="grid-list">
        <?php foreach ($posts as $post): ?>
          <div class="card card-list-item" style="cursor: pointer;" onclick="window.location.href='item_detail.php?id=<?php echo $post['id']; ?>'">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; flex-wrap: wrap; gap: 12px;">
              <div style="flex: 1;">
                <span class="status-badge" style="background: <?php echo $post['status'] === 'lost' ? '#fee' : '#efe'; ?>; color: <?php echo $post['status'] === 'lost' ? '#c33' : '#3c3'; ?>;">
                  <?php echo ucfirst($post['status']); ?>
                </span>
                <h3 class="post-title">
                  <a href="item_detail.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit;">
                    <?php echo htmlspecialchars($post['title']); ?>
                  </a>
                </h3>
                <p class="post-meta">
                  <strong>Category:</strong> <?php echo htmlspecialchars($post['category']); ?> | 
                  <strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?> | 
                  <strong>Date:</strong> <?php echo date('M d, Y', strtotime($post['date'])); ?>
                  <?php if (!empty($post['user_name'])): ?>
                    | <strong>Posted by:</strong> <?php echo htmlspecialchars($post['user_name']); ?>
                  <?php endif; ?>
                </p>
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
            
            <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #eee;">
              <a href="item_detail.php?id=<?php echo $post['id']; ?>" class="login" style="display: inline-block;">
                View Details →
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

<?php require_once 'includes/footer.php'; ?>

