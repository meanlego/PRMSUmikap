<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        header("Location: ../auth/forgot_password.php?error=" . urlencode("Email is required."));
        exit;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../auth/forgot_password.php?error=" . urlencode("Email not found."));
        exit;
    }

    // Store email in session for reset
    $_SESSION['reset_email'] = $email;

    // Redirect to reset form
    header("Location: ../auth/reset_password_form.php");
    exit;
}
?>
