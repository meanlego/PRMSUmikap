<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../database/prmsumikap_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id']) && isset($_POST['action'])) {
    $student_id = $_SESSION['user_id'];
    $job_id = intval($_POST['job_id']);
    $action = $_POST['action'];
    
    $redirect_url = 'browse_job.php';

    try {
        if ($action === 'save') {
            $stmt = $pdo->prepare("INSERT IGNORE INTO saved_jobs (student_id, job_id, saved_date) VALUES (?, ?, NOW())");
            $stmt->execute([$student_id, $job_id]);
            $_SESSION['success'] = "Job saved successfully!";
            
        } elseif ($action === 'unsave') {
            $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE student_id = ? AND job_id = ?");
            $stmt->execute([$student_id, $job_id]);
            $_SESSION['success'] = "Job removed from saved jobs!";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "An error occurred. Please try again.";
    }

    header("Location: " . $redirect_url);
    exit;
} else {
    header("Location: browse_job.php");
    exit;
}
?>