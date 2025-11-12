<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];

    // Get employer_id of logged-in user
    $stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $employer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employer) {
        echo json_encode(['status' => 'error', 'message' => 'Employer profile not found.']);
        exit;
    }

    $employer_id = $employer['employer_id'];

    // Delete job if it belongs to this employer
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $employer_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Job deleted successfully.', 'job_id' => $job_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Job not found or you are not authorized to delete it.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit;
?>
