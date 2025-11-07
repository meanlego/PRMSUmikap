<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

$user_id = $_SESSION['user_id'];

// Get employer ID
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
$employer_id = $employer['employer_id'] ?? null;

if (!$employer_id) {
    die("Employer record not found.");
}

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];

    $stmt = $pdo->prepare("UPDATE jobs SET status = 'closed' WHERE job_id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $employer_id]);

    header("Location: ../employer/manage_jobs.php?success=" . urlencode("Job successfully closed."));
    exit;
} else {
    header("Location: ../employer/manage_jobs.php?error=" . urlencode("Invalid request."));
    exit;
}
?>
