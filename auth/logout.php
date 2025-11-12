<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';
include __DIR__ . '/../functions/auth_functions.php';

// Remove Remember Me token from database & cookie
if (!empty($_SESSION['user_id'])) {
    removeRememberMeCookie($pdo, $_SESSION['user_id']);
}

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to login page
header("Location: ../auth/login.php?success=" . urlencode("Logged out successfully."));
exit;
?>
