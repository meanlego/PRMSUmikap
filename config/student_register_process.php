<?php
include __DIR__ . '/../database/prmsumikap_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/student_register.php');
    exit;
}

$student_number = trim(filter_input(INPUT_POST, 'student_number', FILTER_SANITIZE_SPECIAL_CHARS));
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
$course = trim(filter_input(INPUT_POST, 'course', FILTER_SANITIZE_SPECIAL_CHARS));
$year_level = trim(filter_input(INPUT_POST, 'year_level', FILTER_SANITIZE_SPECIAL_CHARS));
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';

$errors = [];

if (empty($student_number)) $errors[] = 'Student number is required';
if (empty($name)) $errors[] = 'Full name is required';
if (empty($course)) $errors[] = 'Course is required';
if (empty($year_level)) $errors[] = 'Year level is required';
if (!$email) $errors[] = 'Valid email is required';
if (empty($password)) $errors[] = 'Password is required';
if ($role !== 'student') $errors[] = 'Invalid role';

if (!empty($student_number)) {
    $check_student_stmt = $pdo->prepare("SELECT COUNT(*) FROM students_profile WHERE student_number = ?");
    $check_student_stmt->execute([$student_number]);
    if ($check_student_stmt->fetchColumn() > 0) {
        $errors[] = 'This student number is already registered';
    }
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long';
}

if (!empty($errors)) {
    $errorString = implode(', ', $errors);
    header('Location: ../auth/student_register.php?error=' . urlencode($errorString));
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $check_stmt->execute([$email]);
    if ($check_stmt->fetchColumn() > 0) {
        throw new Exception('This email is already registered.');
    }

    $insert_user_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert_user_stmt->execute([$name, $email, $hashed_password, $role]);
    $user_id = $pdo->lastInsertId();

    $insert_profile_stmt = $pdo->prepare("INSERT INTO students_profile (user_id, student_number, course, year_level) VALUES (?, ?, ?, ?)");
    $insert_profile_stmt->execute([$user_id, $student_number, $course, $year_level]);

    $pdo->commit();

 
    header('Location: ../auth/login.php?success=' . urlencode('Registration successful! You can now log in.'));
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Registration PDO Error: " . $e->getMessage());
    header('Location: ../auth/student_register.php?error=' . urlencode('A database error occurred. Please try again.'));
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../auth/student_register.php?error=' . urlencode($e->getMessage()));
    exit;
}