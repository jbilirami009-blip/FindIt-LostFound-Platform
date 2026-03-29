<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_admin();

// Fetch all users
try {
    $stmt = $pdo->query("
        SELECT u.*, COUNT(p.id) as post_count
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading users: ' . $e->getMessage();
    $users = [];
}

$page_title = 'Manage Users';
$current_page = 'admin_users';
require_once 'includes/header.php';
?>

<!-- Manage Users -->
<section class="container admin-container">
  <h1 class="admin-title admin-title-page">
    Manage Users
  </h1>

  <div class="admin-back-link">
    <a href="admin_dashboard.php" class="login admin-back-btn">← Back to Dashboard</a>
  </div>

  <?php if (isset($error)): ?>
    <div class="card admin-error-card">
      <p class="admin-error-text"><?php echo htmlspecialchars($error); ?></p>
    </div>
  <?php endif; ?>

  <?php if (empty($users)): ?>
    <div class="card admin-empty">
      <p>No users found</p>
    </div>
  <?php else: ?>
    <div class="card admin-table-wrapper">
      <table class="admin-table">
        <thead>
          <tr class="admin-table-header">
            <th class="admin-table-th">Name</th>
            <th class="admin-table-th">Email</th>
            <th class="admin-table-th">Role</th>
            <th class="admin-table-th">Status</th>
            <th class="admin-table-th">Posts</th>
            <th class="admin-table-th">Created</th>
            <th class="admin-table-th admin-table-th-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr class="admin-table-row">
              <td class="admin-table-td admin-table-td-name"><?php echo htmlspecialchars($user['name']); ?></td>
              <td class="admin-table-td"><?php echo htmlspecialchars($user['email']); ?></td>
              <td class="admin-table-td">
                <span class="admin-role-badge" style="background: <?php echo $user['role'] === 'admin' ? '#e3f2fd' : '#f3e5f5'; ?>; color: <?php echo $user['role'] === 'admin' ? '#1976d2' : '#7b1fa2'; ?>;">
                  <?php echo ucfirst($user['role']); ?>
                </span>
              </td>
              <td class="admin-table-td">
                <?php if ($user['blocked'] == 1): ?>
                  <span class="admin-status-blocked">Blocked</span>
                <?php else: ?>
                  <span class="admin-status-active">Active</span>
                <?php endif; ?>
              </td>
              <td class="admin-table-td"><?php echo $user['post_count']; ?></td>
              <td class="admin-table-td admin-table-td-date"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
              <td class="admin-table-td admin-table-td-center">
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
                <?php else: ?>
                  <span class="admin-current-user">Current User</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>

