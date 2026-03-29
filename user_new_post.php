<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
require_login();

// Check if user is blocked
if (is_user_blocked(get_current_user_id())) {
    header('Location: logout.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date = $_POST['date'] ?? '';
    
    // Validation
    if (empty($status) || !in_array($status, ['lost', 'found'])) {
        $error = 'Please select a valid status.';
    } elseif (empty($title)) {
        $error = 'Title is required.';
    } elseif (empty($category)) {
        $error = 'Category is required.';
    } elseif (empty($description)) {
        $error = 'Description is required.';
    } elseif (empty($location)) {
        $error = 'Location is required.';
    } elseif (empty($date)) {
        $error = 'Date is required.';
    } else {
        // Handle file upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $error = 'Invalid file type. Only JPG, JPEG, and PNG are allowed.';
            } elseif ($file['size'] > $max_size) {
                $error = 'File size too large. Maximum size is 5MB.';
            } else {
                // Ensure uploads directory exists
                $uploads_dir = __DIR__ . '/uploads';
                if (!is_dir($uploads_dir)) {
                    if (!mkdir($uploads_dir, 0755, true)) {
                        $error = 'Failed to create uploads directory.';
                    }
                }
                
                if (empty($error)) {
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '_' . time() . '.' . $extension;
                    $upload_path = $uploads_dir . '/' . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/' . $filename;
                    } else {
                        $error = 'Failed to upload image. Please check directory permissions.';
                    }
                }
            }
        }
        
        if (empty($error)) {
            // Insert post
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO posts (user_id, status, title, category, description, location, date, image, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    get_current_user_id(),
                    $status,
                    $title,
                    $category,
                    $description,
                    $location,
                    $date,
                    $image_path
                ]);
                
                $success = 'Post created successfully!';
                header('Refresh: 1; url=user_my_posts.php');
            } catch (PDOException $e) {
                $error = 'Error creating post: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Create New Post';
$current_page = 'new_post';
require_once 'includes/header.php';
?>

<!-- Create Post -->
<section class="container container-form">
  <h1 class="page-title">Create New Post</h1>

  <?php if ($error): ?>
    <div class="card error-card">
      <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="card success-card">
      <p class="success-text"><?php echo htmlspecialchars($success); ?></p>
    </div>
  <?php endif; ?>

  <div class="card card-form">
    <form method="POST" action="" enctype="multipart/form-data">
      <label>Status</label>
      <select name="status" class="form-select" required>
        <option value="">Select status</option>
        <option value="lost" <?php 
          $selected_status = $_POST['status'] ?? $_GET['status'] ?? '';
          echo ($selected_status === 'lost') ? 'selected' : ''; 
        ?>>Lost Item</option>
        <option value="found" <?php 
          $selected_status = $_POST['status'] ?? $_GET['status'] ?? '';
          echo ($selected_status === 'found') ? 'selected' : ''; 
        ?>>Found Item</option>
      </select>

      <label>Title</label>
      <div class="input-icon"><span>📝</span>
        <input type="text" name="title" placeholder="e.g., Black iPhone 13" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
      </div>

      <label>Category</label>
      <select name="category" class="form-select" required>
        <option value="">Select category</option>
        <option value="Electronics" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
        <option value="Documents" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Documents') ? 'selected' : ''; ?>>Documents</option>
        <option value="Clothing" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Clothing') ? 'selected' : ''; ?>>Clothing</option>
        <option value="Bags" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Bags') ? 'selected' : ''; ?>>Bags</option>
        <option value="Accessories" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
        <option value="Other" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
      </select>

      <label>Description</label>
      <textarea name="description" rows="6" placeholder="Provide detailed description..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

      <label>Location</label>
      <div class="input-icon"><span>📍</span>
        <input type="text" name="location" placeholder="e.g., FH Technikum Wien, Building A" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
      </div>

      <label>Date</label>
      <div class="input-icon"><span>📅</span>
        <input type="date" name="date" value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" required>
      </div>

      <label>Image (optional - JPG, PNG, max 5MB)</label>
      <div id="image-preview-container"></div>
      <input type="file" name="image" id="image-input" accept="image/jpeg,image/jpg,image/png" class="file-input">

      <button class="login wide-btn" type="submit">Create Post</button>
    </form>
  </div>
</section>

<script>
// Image preview functionality
document.getElementById('image-input').addEventListener('change', function(e) {
  const container = document.getElementById('image-preview-container');
  container.innerHTML = '';
  
  if (this.files && this.files[0]) {
    const file = this.files[0];
    const reader = new FileReader();
    
    reader.onload = function(e) {
      const preview = document.createElement('div');
      preview.className = 'image-preview';
      preview.innerHTML = '<p class="image-preview-label">Selected image:</p><img src="' + e.target.result + '" alt="Preview">';
      container.appendChild(preview);
    };
    
    reader.readAsDataURL(file);
  }
});
</script>

<?php require_once 'includes/footer.php'; ?>

