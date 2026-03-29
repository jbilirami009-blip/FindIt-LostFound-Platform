<?php
require_once 'includes/auth.php';

// Destroy session
session_destroy();

// Redirect to home page
header('Location: index.php');
exit;
?>

