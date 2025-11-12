<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php'; // PDO connection
include __DIR__ . '/../functions/auth_functions.php'; // Remember Me functions

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/login.php');
    exit;
}

// Clear all Remember Me tokens if requested
if (!empty($_POST['action']) && $_POST['action'] === 'clear_all') {
    if (!empty($_SESSION['user_id'])) {
        removeRememberMeCookie($pdo, $_SESSION['user_id']);
        header('Location: ../auth/login.php?success=' . urlencode('All remember me tokens cleared.'));
    } else {
        header('Location: ../auth/login.php?error=' . urlencode('You must be logged in to clear tokens.'));
    }
    exit;
}

// Get login form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($email) || empty($password)) {
    header('Location: ../auth/login.php?error=' . urlencode('Please fill in all fields.'));
    exit;
}

// Determine user table (user or users)
$tableName = 'user';
try {
    $pdo->query("SELECT 1 FROM user LIMIT 1");
} catch (PDOException $e) {
    $tableName = 'users';
}

try {
    // Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM {$tableName} WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Set session data
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Set Remember Me cookie if checked
        if ($remember) {
            $cookieSet = setRememberMeCookie($pdo, $user['user_id'], $user['email']);
            $redirectMsg = $cookieSet 
                ? 'Logged in successfully with Remember Me enabled.' 
                : 'Logged in successfully (Remember Me could not be set).';
        } else {
            $redirectMsg = 'Logged in successfully.';
        }

        // Redirect based on role
        $role = strtolower($user['role']);
        $redirectUrls = [
            'student' => '../student/student_dashboard.php',
            'employer' => '../employer/employer_dashboard.php',
            'admin' => '../admin/admin_dashboard.php'
        ];
        $redirectTo = $redirectUrls[$role] ?? '../auth/login.php';
        header("Location: {$redirectTo}?success=" . urlencode($redirectMsg));
        exit;
    }

    // Invalid credentials
    header('Location: ../auth/login.php?error=' . urlencode('Invalid email or password.'));
    exit;

} catch (PDOException $e) {
    error_log("Remember Me Login Error: " . $e->getMessage());
    header('Location: ../auth/login.php?error=' . urlencode('A database error occurred. Please try again later.'));
    exit;
}
