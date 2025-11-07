<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch employer ID
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
$employer_id = $employer['employer_id'] ?? null;

if (!$employer_id) {
    die("Employer record not found.");
}

// Process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'];
    $title = $_POST['job_title'];
    $description = $_POST['job_description'];
    $location = $_POST['job_location'];
    $type = $_POST['job_type'];
    $arrangement = $_POST['work_arrangement'];
    $min_salary = $_POST['min_salary'];
    $max_salary = $_POST['max_salary'];
    $qualifications = $_POST['job_qualifications'];
    $responsibilities = $_POST['job_responsibilities'];

    $stmt = $pdo->prepare("UPDATE jobs 
        SET job_title = ?, job_description = ?, job_location = ?, job_type = ?, 
            work_arrangement = ?, min_salary = ?, max_salary = ?, 
            job_qualifications = ?, job_responsibilities = ?, date_posted = NOW()
        WHERE job_id = ? AND employer_id = ?");
    $stmt->execute([$title, $description, $location, $type, $arrangement, $min_salary, $max_salary, $qualifications, $responsibilities, $job_id, $employer_id]);

    header("Location: ../employer/manage_jobs.php?success=" . urlencode("Job updated successfully."));
    exit;
} else {
    header("Location: ../employer/manage_jobs.php?error=" . urlencode("Invalid request."));
    exit;
}
?>
