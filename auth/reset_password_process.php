<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';
include __DIR__ . '/../functions/auth_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    $_SESSION['reset_email'] = $email; // preserve email in form

    // Validation
    if ($password !== $confirmPassword) {
        $_SESSION['reset_error'] = "Passwords do not match.";
        header("Location: reset_password_form.php");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['reset_error'] = "Password must be at least 8 characters.";
        header("Location: reset_password_form.php");
        exit;
    }

    // Reset password using the auth function
    $result = resetPasswordByEmail($pdo, $email, $password, $confirmPassword);

    if ($result['success']) {
        $_SESSION['reset_success'] = $result['message'];
        unset($_SESSION['reset_email']); // clear email after success
    } else {
        $_SESSION['reset_error'] = $result['message'];
    }

    header("Location: reset_password_form.php");
    exit;
}
