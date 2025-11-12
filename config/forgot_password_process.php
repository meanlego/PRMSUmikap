<?php
session_start();

// ✅ adjust depending on actual location
include __DIR__ . '/../database/prmsumikap_db.php'; 

// ✅ this is correct based on your folder tree
include __DIR__ . '/../assets/functions/auth_functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/forgot_password.php');
    exit;
}

// Get email from form
$email = trim($_POST['email']);

// Validate email format
if (empty($email)) {
    header('Location: ../auth/forgot_password.php?error=' . urlencode('Please enter your email address'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../auth/forgot_password.php?error=' . urlencode('Please enter a valid email address'));
    exit;
}

// Optional rate limiting
if (isset($_SESSION['last_reset_request'])) {
    $timeSinceLastRequest = time() - $_SESSION['last_reset_request'];
    if ($timeSinceLastRequest < 10) {
        $waitTime = 10 - $timeSinceLastRequest;
        header('Location: ../auth/forgot_password.php?error=' . urlencode('Please wait ' . $waitTime . ' seconds before requesting again'));
        exit;
    }
}

// ✅ Send password reset link
$result = sendPasswordResetLink($pdo, $email);

// Store timestamp
$_SESSION['last_reset_request'] = time();

if ($result['success']) {
    header('Location: ../auth/forgot_password.php?success=' . urlencode($result['message']));
} else {
    header('Location: ../auth/forgot_password.php?error=' . urlencode($result['message']));
}
exit;
?>
