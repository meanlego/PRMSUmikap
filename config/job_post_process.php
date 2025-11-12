<?php
include __DIR__ . '/../database/prmsumikap_db.php';
session_start();

// --- Security Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User session not found. Please log in again.");
}

// --- Get Employer ID ---
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employer) {
    die("Employer record not found.");
}

$employer_id = $employer['employer_id'];
$message = "";

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['job_title'] ?? ''));
    $description = htmlspecialchars(trim($_POST['job_description'] ?? ''));
    $location = htmlspecialchars(trim($_POST['job_location'] ?? ''));
    $type = htmlspecialchars($_POST['job_type'] ?? '');
    $arrangement = htmlspecialchars($_POST['work_arrangement'] ?? '');
    $minsalary = trim($_POST['min_salary'] ?? '');
    $maxsalary = trim($_POST['max_salary'] ?? '');
    $qualifications = htmlspecialchars(trim($_POST['job_qualifications'] ?? ''));
    $responsibilities = htmlspecialchars(trim($_POST['job_responsibilities'] ?? ''));
    $status = $_POST['status'] ?? 'draft';
    $status = strtolower(trim($status)) === 'active' ? 'active' : 'draft';


    if (!empty($title) && !empty($description)) {
        $stmt = $pdo->prepare("INSERT INTO jobs 
            (employer_id, job_title, job_description, job_location, job_type, work_arrangement, min_salary, max_salary, status, job_qualifications, job_responsibilities, date_posted) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->execute([
            $employer_id,
            $title,
            $description,
            $location,
            $type,
            $arrangement,
            $minsalary,
            $maxsalary,
            $status,
            $qualifications,
            $responsibilities
        ]);

        if ($status === 'active') {
        echo json_encode(['status' => 'success', 'message' => 'Job posted successfully!']);
        } else {
        echo json_encode(['status' => 'success', 'message' => 'Job saved as draft.']);
        }
        
    } else {
        $message = '<div class="alert alert-danger text-center">Please fill in all required fields.</div>';
    }
}
?>
