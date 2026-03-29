<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo isset($page_title) ? $page_title . ' — ' : ''; ?>FindIt</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Navbar -->
  <div class="topbar">
    <div class="container nav">
      <a class="brand" href="index.php"><span class="logo">⌕</span><span>FindIt</span></a>
      <div class="menu">
        <a href="index.php" <?php echo (isset($current_page) && $current_page === 'index') ? 'class="active"' : ''; ?>>Home</a>
        <a href="about.php" <?php echo (isset($current_page) && $current_page === 'about') ? 'class="active"' : ''; ?>>About</a>
        <a href="contact.php" <?php echo (isset($current_page) && $current_page === 'contact') ? 'class="active"' : ''; ?>>Contact</a>
      </div>
      <div class="actions">
        <?php 
        require_once __DIR__ . '/auth.php';
        if (is_logged_in()): 
          $user = get_current_user_data();
        ?>
          <?php if (is_admin()): ?>
            <a class="login" href="admin_dashboard.php">Admin</a>
          <?php endif; ?>
          <a class="login" href="user_new_post.php">+ New Post</a>
          <a class="login" href="user_my_posts.php">My Posts</a>
          <a class="login" href="user_messages.php">Messages</a>
          <a class="login" href="logout.php">Logout</a>
        <?php else: ?>
          <button class="login" onclick="window.location.href='login.php'">Login</button>
        <?php endif; ?>
      </div>
    </div>
  </div>


