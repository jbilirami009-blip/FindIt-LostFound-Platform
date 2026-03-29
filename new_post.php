<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create New Post — FindIt</title>
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

  <!-- Create Post -->
  <section class="container" style="max-width:820px">
    <h1 class="page-title" style="text-align:center;margin:26px 0 14px;font-size:2rem;font-weight:800;color:#0f172a">
      Create New Post
    </h1>

    <div class="card" style="padding:22px;border-radius:14px">
      <form>
        <label>Status</label>
        <select class="form-select">
          <option>Lost Item</option>
          <option>Found Item</option>
        </select>

        <label>Title</label>
        <div class="input-icon"><span>📝</span>
          <input type="text" placeholder="e.g., Black iPhone 13" required>
        </div>

        <label>Category</label>
        <select class="form-select">
          <option selected disabled>Select category</option>
          <option>Electronics</option><option>Documents</option>
          <option>Clothing</option><option>Bags</option>
          <option>Accessories</option><option>Other</option>
        </select>

        <label>Description</label>
        <textarea rows="6" placeholder="Provide detailed description..." required></textarea>

        <label>Location</label>
        <div class="input-icon"><span>📍</span>
          <input type="text" placeholder="e.g., FH Technikum Wien, Building A" required>
        </div>

        <label>Date</label>
        <div class="input-icon"><span>📅</span>
          <input type="date" required>
        </div>

        <label>Image URL (optional)</label>
        <div class="input-icon"><span>🔗</span>
          <input type="url" placeholder="https://example.com/image.jpg">
        </div>

        <button class="login wide-btn" type="submit">Create Post</button>
      </form>
    </div>
  </section>

  <footer>
    <div class="container">© 2025 FindIt — Developed by Jeffrey Hladi &amp; Rami Jbili | Computer Science @ FH Technikum Wien</div>
  </footer>
</body>
</html>

