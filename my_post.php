<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Posts — FindIt</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <div class="topbar">
    <div class="container nav">
      <a class="brand" href="index.php"><span class="logo">⌕</span><span>FindIt</span></a>
      <div class="menu">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
      </div>
      <div class="actions">
        <a class="login" href="new_post.php">+ New Post</a>
      </div>
    </div>
  </div>

  <!-- My Posts -->
  <section class="container" style="max-width:1100px">
    <h1 class="page-title" style="margin:26px 0 16px;font-size:2rem;font-weight:800;color:#0f172a">My Posts</h1>

    <div class="card" style="padding:40px;border-radius:14px;text-align:center">
      <p style="margin:0 0 14px;color:#334155;font-size:1.05rem">
        You haven't posted any items yet
      </p>
      <a href="new_post.php" class="login" style="display:inline-block;padding:10px 18px;border-radius:8px">
        Create Your First Post
      </a>
    </div>
  </section>

  <footer>
    <div class="container">© 2025 FindIt — Developed by Jeffrey Hladi &amp; Rami Jbili | Computer Science @ FH Technikum Wien</div>
  </footer>
</body>
</html>

