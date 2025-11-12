<?php
include __DIR__ . '/../database/prmsumikap_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/employer_register.php');
    exit;
}

// Collect form data
$company_name = trim(filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_SPECIAL_CHARS));
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';

// Validate inputs
if (empty($company_name) || empty($name) || !$email || empty($password) || empty($confirm_password) || $role !== 'employer') {
    header('Location: ../auth/employer_register.php?error=' . urlencode('Please fill in all required fields correctly.'));
    exit;
}

if ($password !== $confirm_password) {
    header('Location: ../auth/employer_register.php?error=' . urlencode('Passwords do not match.'));
    exit;
}

if (strlen($password) < 8) {
    header('Location: ../auth/employer_register.php?error=' . urlencode('Password must be at least 8 characters long.'));
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    // Check if email already exists
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $check_stmt->execute([$email]);
    if ($check_stmt->fetchColumn() > 0) {
        $pdo->rollBack();
        header('Location: ../auth/employer_register.php?error=' . urlencode('This email is already registered.'));
        exit;
    }

    // Insert into users table
    $insert_user_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert_user_stmt->execute([$name, $email, $hashed_password, $role]);

    $user_id = $pdo->lastInsertId();

    // Insert into employers_profile table
    $insert_profile_stmt = $pdo->prepare("INSERT INTO employers_profile (user_id, company_name, contact_person) VALUES (?, ?, ?)");
    $insert_profile_stmt->execute([$user_id, $company_name, $name]);

    $pdo->commit();

    // ✅ Redirect to login on success
    header('Location: ../auth/login.php?success=' . urlencode('Registration successful! You can now log in.'));
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: ../auth/employer_register.php?error=' . urlencode('A database error occurred: ' . $e->getMessage()));
    exit;
}
