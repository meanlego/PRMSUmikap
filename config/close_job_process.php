<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $employer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employer) {
        echo json_encode(['status' => 'error', 'message' => 'Employer profile not found.']);
        exit;
    }

    $employer_id = $employer['employer_id'];

    $stmt = $pdo->prepare("UPDATE jobs SET status = 'Closed' WHERE job_id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $employer_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Job closed successfully.', 'job_id' => $job_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Job not found or you are not authorized to close it.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit;
?>