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

    $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id = ? AND employer_id = (
        SELECT employer_id FROM employers_profile WHERE user_id = ?
    )");
    $stmt->execute([$job_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Job deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete job or unauthorized.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit;
?>
