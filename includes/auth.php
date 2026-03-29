<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

/**
 * Get current user data
 * @return array|null
 */
function get_current_user_data() {
    return $_SESSION['user'] ?? null;
}

/**
 * Get current user ID
 * @return int|null
 */
function get_current_user_id() {
    return $_SESSION['user']['id'] ?? null;
}

/**
 * Get current user role
 * @return string|null
 */
function get_current_user_role() {
    return $_SESSION['user']['role'] ?? null;
}

/**
 * Check if current user is admin
 * @return bool
 */
function is_admin() {
    return get_current_user_role() === 'admin';
}

/**
 * Require user to be logged in, redirect to login if not
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require user to be admin, redirect to index if not
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Check if user is blocked
 */
function is_user_blocked($user_id) {
    require_once __DIR__ . '/../config/db.php';
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT blocked FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        return $user && $user['blocked'] == 1;
    } catch (PDOException $e) {
        return false;
    }
}
?>


