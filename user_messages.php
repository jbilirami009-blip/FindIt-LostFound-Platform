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
$error = '';
$success = '';

// Get post_id from URL if provided
$post_id_param = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$post_info = null;
$to_user_id = null;

// If post_id is provided, fetch post and owner information
if ($post_id_param > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.id as owner_id, u.name as owner_name, u.email as owner_email
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$post_id_param]);
        $post_info = $stmt->fetch();
        
        if ($post_info) {
            $to_user_id = $post_info['owner_id'];
            // Prevent users from messaging themselves
            if ($to_user_id == $user_id) {
                $error = 'You cannot send a message to yourself.';
                $post_info = null;
            }
        } else {
            $error = 'Post not found.';
        }
    } catch (PDOException $e) {
        $error = 'Error loading post: ' . $e->getMessage();
    }
}

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $to_user = intval($_POST['to_user'] ?? 0);
    $post_id = intval($_POST['post_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if (empty($to_user) || empty($post_id) || empty($message)) {
        $error = 'Please fill in all fields.';
    } else {
        // Prevent users from messaging themselves
        if ($to_user == $user_id) {
            $error = 'You cannot send a message to yourself.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO messages (from_user, to_user, post_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $to_user, $post_id, $message]);
                $success = 'Message sent successfully!';
                // Clear form after successful submission
                $post_info = null;
                $post_id_param = 0;
            } catch (PDOException $e) {
                $error = 'Error sending message: ' . $e->getMessage();
            }
        }
    }
}

// Fetch received messages
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.name as from_name, u.email as from_email, p.title as post_title, p.id as post_id
        FROM messages m
        JOIN users u ON m.from_user = u.id
        JOIN posts p ON m.post_id = p.id
        WHERE m.to_user = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $received_messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $received_messages = [];
    $error = 'Error loading messages: ' . $e->getMessage();
}

// Fetch sent messages
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.name as to_name, u.email as to_email, p.title as post_title, p.id as post_id
        FROM messages m
        JOIN users u ON m.to_user = u.id
        JOIN posts p ON m.post_id = p.id
        WHERE m.from_user = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $sent_messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $sent_messages = [];
}

$page_title = 'Messages';
$current_page = 'messages';
require_once 'includes/header.php';
?>

<!-- Messages -->
<section class="container container-list">
  <h1 class="page-title-list">Messages</h1>

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

  <!-- Send Message Form (shown when post_id is provided) -->
  <?php if ($post_info && $to_user_id): ?>
    <div class="card card-form" style="margin-bottom: 30px;">
      <h2 class="section-header" style="margin-top: 0;">Send Message</h2>
      
      <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #009fb7;">
        <p style="margin: 0 0 8px 0; font-weight: 600; color: #0f172a;">
          About: <?php echo htmlspecialchars($post_info['title']); ?>
        </p>
        <p style="margin: 0; color: #666; font-size: 0.9rem;">
          To: <?php echo htmlspecialchars($post_info['owner_name']); ?> (<?php echo htmlspecialchars($post_info['owner_email']); ?>)
        </p>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="post_id" value="<?php echo $post_id_param; ?>">
        <input type="hidden" name="to_user" value="<?php echo $to_user_id; ?>">
        
        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Your Message</label>
        <textarea 
          name="message" 
          rows="6" 
          placeholder="Write your message here..." 
          required
          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 15px; resize: vertical;"
        ><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        
        <div style="margin-top: 16px; display: flex; gap: 12px;">
          <button type="submit" name="send_message" class="login" style="flex: 1;">
            Send Message
          </button>
          <a href="user_messages.php" class="login btn-cancel" style="text-decoration: none; padding: 10px 20px;">
            Cancel
          </a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- Received Messages -->
  <div style="margin-bottom: 40px;">
    <h2 class="section-header">Received Messages</h2>
    <?php if (empty($received_messages)): ?>
      <div class="card card-empty">
        <p>No messages received yet.</p>
      </div>
    <?php else: ?>
      <div class="grid-messages">
        <?php foreach ($received_messages as $msg): ?>
          <div class="card card-message">
            <div class="message-header">
              <div>
                <p class="message-from">From: <?php echo htmlspecialchars($msg['from_name']); ?></p>
                <p class="message-email"><?php echo htmlspecialchars($msg['from_email']); ?></p>
                <p class="message-email">About: <a href="user_my_posts.php" class="message-link"><?php echo htmlspecialchars($msg['post_title']); ?></a></p>
              </div>
              <p class="message-time"><?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?></p>
            </div>
            <p class="message-content">
              <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Sent Messages -->
  <div>
    <h2 class="section-header">Sent Messages</h2>
    <?php if (empty($sent_messages)): ?>
      <div class="card card-empty">
        <p>No messages sent yet.</p>
      </div>
    <?php else: ?>
      <div class="grid-messages">
        <?php foreach ($sent_messages as $msg): ?>
          <div class="card card-message">
            <div class="message-header">
              <div>
                <p class="message-from">To: <?php echo htmlspecialchars($msg['to_name']); ?></p>
                <p class="message-email"><?php echo htmlspecialchars($msg['to_email']); ?></p>
                <p class="message-email">About: <a href="user_my_posts.php" class="message-link"><?php echo htmlspecialchars($msg['post_title']); ?></a></p>
              </div>
              <p class="message-time"><?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?></p>
            </div>
            <p class="message-content">
              <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>

