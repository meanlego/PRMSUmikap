<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php'; // PDO connection
include __DIR__ . '/../functions/auth_functions.php'; // for Remember Me functions

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../auth/login.php");
    exit;
}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$remember = isset($_POST['remember']);

// Validate input
if (empty($email) || empty($password)) {
    header("Location: ../auth/login.php?error=" . urlencode("Please fill in all fields."));
    exit;
}

try {
    // Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // Set Remember Me cookie if requested
            if ($remember) {
                setRememberMeCookie($pdo, $user['user_id'], $user['email']);
            }

            // Redirect based on role
            $base_url = "http://localhost/prmsumikap";
            switch (strtolower($user['role'])) {
                case 'student':
                    header("Location: {$base_url}/employee/dashboard.php");
                    exit;
                case 'employer':
                    header("Location: {$base_url}/employer/employer_dashboard.php");
                    exit;
                default:
                    header("Location: {$base_url}/auth/login.php?error=" . urlencode("Invalid role detected."));
                    exit;
            }

        } else {
            header("Location: ../auth/login.php?error=" . urlencode("Invalid password."));
            exit;
        }

    } else {
        header("Location: ../auth/login.php?error=" . urlencode("Email not found."));
        exit;
    }

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    header("Location: ../auth/login.php?error=" . urlencode("Database error. Please try again later."));
    exit;
}
