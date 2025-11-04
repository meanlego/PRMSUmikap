<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php'; // includes your PDO connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        header("Location: ../auth/login.php?error=" . urlencode("Please fill in all fields."));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            $base_url = "http://localhost/prmsumikap";

            if ($user['role'] === 'student') {
                header("Location: {$base_url}/student/dashboard.php");
                exit;
            } 
            else if ($user['role'] === 'employer') {
                header("Location: {$base_url}/employer/employer_dashboard.php");
                exit;
            } 
            else {
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
}
?>
