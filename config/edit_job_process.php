<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch employer ID
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
$employer_id = $employer['employer_id'] ?? null;

if (!$employer_id) {
    echo json_encode(['status' => 'error', 'message' => 'Employer record not found.']);
    exit;
}

// Process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $job_id = $_POST['job_id'] ?? null;
        $title = trim($_POST['job_title'] ?? '');
        $description = trim($_POST['job_description'] ?? '');
        $location = trim($_POST['job_location'] ?? '');
        $type = $_POST['job_type'] ?? '';
        $arrangement = $_POST['work_arrangement'] ?? '';
        $min_salary = $_POST['min_salary'] ?? null;
        $max_salary = $_POST['max_salary'] ?? null;
        $qualifications = trim($_POST['job_qualifications'] ?? '');
        $responsibilities = trim($_POST['job_responsibilities'] ?? '');
        $status = $_POST['status'] ?? 'Active'; // Get status from form

        // Validation
        if (empty($job_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Job ID is required.']);
            exit;
        }

        if (empty($title) || empty($description) || empty($location)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            exit;
        }

        // Verify job belongs to this employer
        $checkStmt = $pdo->prepare("SELECT job_id FROM jobs WHERE job_id = ? AND employer_id = ?");
        $checkStmt->execute([$job_id, $employer_id]);
        if (!$checkStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Job not found or unauthorized.']);
            exit;
        }

        // Update the job with status
        $stmt = $pdo->prepare("UPDATE jobs 
            SET job_title = ?, 
                job_description = ?, 
                job_location = ?, 
                job_type = ?, 
                work_arrangement = ?, 
                min_salary = ?, 
                max_salary = ?, 
                job_qualifications = ?, 
                job_responsibilities = ?,
                status = ?,
                date_posted = NOW()
            WHERE job_id = ? AND employer_id = ?");
        
        $stmt->execute([
            $title, 
            $description, 
            $location, 
            $type, 
            $arrangement, 
            $min_salary, 
            $max_salary, 
            $qualifications, 
            $responsibilities,
            $status,
            $job_id, 
            $employer_id
        ]);

        $message = $status === 'Draft' 
            ? 'Job saved as draft successfully.' 
            : 'Job updated and published successfully.';

        echo json_encode([
            'status' => 'success', 
            'message' => $message,
            'job_status' => $status
        ]);

    } catch (PDOException $e) {
        error_log("Database error in edit_job_process.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred. Please try again.']);
    } catch (Exception $e) {
        error_log("Error in edit_job_process.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
exit;
?>