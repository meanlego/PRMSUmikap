<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

require_once '../database/prmsumikap_db.php';

try {
    $stmt = $pdo->prepare("SELECT student_id FROM students_profile WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student_profile) {
        header("Location: browse_job.php?error=" . urlencode("Student profile not found. Please complete your profile first."));
        exit;
    }
    
    $student_id = $student_profile['student_id'];
    $studentName = $_SESSION['name'];
} catch(PDOException $e) {
    header("Location: browse_job.php?error=" . urlencode("Database error: Could not find student profile."));
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: browse_job.php?error=" . urlencode("No job specified."));
    exit;
}

$job_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("
        SELECT j.*, e.company_name, e.contact_person, j.employer_id
        FROM jobs j 
        LEFT JOIN employers_profile e ON j.employer_id = e.employer_id 
        WHERE j.job_id = ? AND j.status = 'Active'
    ");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job) {
        header("Location: browse_job.php?error=" . urlencode("Job not found or no longer available."));
        exit;
    }
} catch(PDOException $e) {
    header("Location: browse_job.php?error=" . urlencode("Database error."));
    exit;
}

try {
    $checkStmt = $pdo->prepare("SELECT * FROM applications WHERE student_id = ? AND job_id = ?");
    $checkStmt->execute([$student_id, $job_id]);
    $existingApplication = $checkStmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $existingApplication = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {

        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND job_id = ?");
        $checkStmt->execute([$student_id, $job_id]);
        
        if ($checkStmt->fetchColumn() == 0) {

            $insertStmt = $pdo->prepare("
                INSERT INTO applications (student_id, job_id, status, date_applied, employer_id, user_id) 
                VALUES (?, ?, 'Pending', NOW(), ?, ?)
            ");
            $insertStmt->execute([
                $student_id,         
                $job_id, 
                $job['employer_id'],  
                $_SESSION['user_id']   
            ]);
            
            header("Location: job_applications.php?success=" . urlencode("Application submitted successfully!"));
            exit;
        } else {
            $error = "You have already applied for this position.";
        }
    } catch(PDOException $e) {
        error_log("Application error: " . $e->getMessage());
        if ($e->getCode() == 23000) { 
            $error = "You have already applied for this position.";
        } else {
            $error = "Failed to submit application. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for <?php echo htmlspecialchars($job['job_title']); ?> | PRMSUmikap</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap-rebase/assets/images/favicon.png">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
<style>
.job-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; }
.application-card { border-left: 4px solid #2575fc; }
</style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

<div id="main-content">
    <div class="container-fluid">
        
        <div class="job-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($job['job_title']); ?></h1>
                    <p class="mb-1 fs-5"><?php echo htmlspecialchars($job['company_name']); ?></p>
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($job['job_location']); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($job['job_type']); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            ₱<?php echo number_format($job['min_salary']); ?> - ₱<?php echo number_format($job['max_salary']); ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="view_details.php?id=<?php echo $job_id; ?>" class="btn btn-outline-light">
                        <i class="bi bi-eye me-1"></i>View Job Details
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <?php if ($existingApplication): ?>
                    <div class="card application-card shadow-sm">
                        <div class="card-body text-center p-5">
                            <i class="bi bi-check-circle-fill display-4 text-success mb-3"></i>
                            <h3 class="text-success mb-3">Already Applied!</h3>
                            <p class="text-muted mb-4">
                                You applied for this position on <?php echo date('F j, Y', strtotime($existingApplication['date_applied'])); ?>.
                                Current status: <span class="badge bg-warning"><?php echo $existingApplication['status']; ?></span>
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="job_applications.php" class="btn btn-primary">
                                    <i class="bi bi-list-ul me-1"></i>View My Applications
                                </a>
                                <a href="browse_job.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-search me-1"></i>Browse More Jobs
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card application-card shadow-sm">
                        <div class="card-header bg-white">
                            <h4 class="fw-bold mb-0"><i class="bi bi-send me-2"></i>Submit Your Application</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="applicationForm">
                                <div class="alert alert-info">
                                    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Application Notice</h6>
                                    <p class="mb-0">You are about to apply for <strong><?php echo htmlspecialchars($job['job_title']); ?></strong> at <strong><?php echo htmlspecialchars($job['company_name']); ?></strong>. Your application will be reviewed by the employer.</p>
                                </div>

                                <div class="d-flex gap-2 justify-content-end mt-4">
                                    <a href="view_details.php?id=<?php echo $job_id; ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back to Job
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-1"></i>Submit Application
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Job Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="fw-semibold text-muted mb-2">Company</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($job['company_name']); ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-semibold text-muted mb-2">Location</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($job['job_location']); ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-semibold text-muted mb-2">Job Type</h6>
                            <p class="mb-0"><?php echo htmlspecialchars($job['job_type']); ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-semibold text-muted mb-2">Salary Range</h6>
                            <p class="mb-0 fw-bold text-success">
                                ₱<?php echo number_format($job['min_salary']); ?> - ₱<?php echo number_format($job['max_salary']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>