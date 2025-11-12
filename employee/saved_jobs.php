<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

// Include database connection
require_once '../database/prmsumikap_db.php';

$studentName = $_SESSION['name'];  
$accountType = ucfirst($_SESSION['role']);
$student_id = $_SESSION['user_id'];

// Fetch saved jobs
try {
    // Count total saved jobs
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM saved_jobs WHERE student_id = ?");
    $countStmt->execute([$student_id]);
    $totalSaved = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Fetch saved jobs with details
    $savedStmt = $pdo->prepare("
        SELECT sj.*, j.job_title, j.job_description, j.job_location, j.min_salary, j.max_salary, 
               j.job_type, j.work_arrangement, j.date_posted, j.status as job_status,
               e.company_name, e.contact_person,
               (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id AND a.student_id = ?) as has_applied
        FROM saved_jobs sj
        JOIN jobs j ON sj.job_id = j.job_id
        LEFT JOIN employers_profile e ON j.employer_id = e.employer_id
        WHERE sj.student_id = ? AND j.status = 'Active'
        ORDER BY sj.saved_date DESC
    ");
    $savedStmt->execute([$student_id, $student_id]);
    $savedJobs = $savedStmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $totalSaved = 0;
    $savedJobs = [];
    error_log("Saved jobs error: " . $e->getMessage());
}

// Check for messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Saved Jobs | PRMSUmikap</title>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

<!-- Tab Icon -->
<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap/assets/images/favicon.png">

<!-- Custom CSS -->
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
<style>
.job-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    border-left: 4px solid #2575fc;
}
.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.company-logo {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
.salary-range {
    font-weight: 600;
    color: #198754;
}
.saved-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
</style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

<div id="main-content">

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Welcome / Header + Stats -->
    <div class="welcome-card mb-4">
        <h1 class="display-5 fw-bold mb-2">Saved Jobs</h1>
        <p class="fs-5 mb-4">Jobs you've bookmarked for later</p>

        <!-- Stats inside welcome-card -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3 p-3 shadow-sm bg-white bg-opacity-75">
                    <i class="bi bi-bookmark-fill fs-2 text-black"></i>
                    <div>
                        <h6 class="text-black opacity-75 mb-1">Total Saved</h6>
                        <h3 class="fw-bold mb-0 text-black"><?php echo $totalSaved; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saved Jobs List -->
    <div class="row g-4">
        <div class="col-12">
            <?php if (!empty($savedJobs)): ?>
                <?php foreach($savedJobs as $job): ?>
                    <div class="job-card card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="company-logo bg-light d-flex align-items-center justify-content-center">
                                            <i class="bi bi-building text-muted fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($job['job_title']); ?></h4>
                                                <span class="badge saved-badge">
                                                    <i class="bi bi-bookmark-fill me-1"></i>Saved
                                                </span>
                                            </div>
                                            <p class="text-muted mb-1 fs-5"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($job['job_location']); ?>
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($job['job_type']); ?>
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-laptop me-1"></i><?php echo htmlspecialchars($job['work_arrangement']); ?>
                                                </span>
                                            </div>
                                            <p class="text-muted mb-2"><?php echo substr(strip_tags($job['job_description']), 0, 150); ?>...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column h-100">
                                        <div class="mb-3">
                                            <span class="salary-range fs-5">
                                                ₱<?php echo number_format($job['min_salary']); ?> - ₱<?php echo number_format($job['max_salary']); ?>
                                            </span>
                                            <small class="text-muted d-block">per month</small>
                                        </div>
                                        <div class="mt-auto">
                                            <div class="d-grid gap-2">
                                                <?php if($job['has_applied']): ?>
                                                    <button class="btn btn-success" disabled>
                                                        <i class="bi bi-check-circle me-1"></i>Applied
                                                    </button>
                                                <?php else: ?>
                                                    <a href="apply_job.php?id=<?php echo $job['job_id']; ?>" 
                                                       class="btn btn-primary">
                                                        <i class="bi bi-send me-1"></i>Apply Now
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <form method="POST" action="../employee/save_job_process.php" class="d-inline w-100">
                                                    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                                    <input type="hidden" name="redirect_url" value="saved_jobs.php">
                                                    <button type="submit" name="action" value="unsave" class="btn btn-outline-danger w-100">
                                                        <i class="bi bi-bookmark-x me-1"></i>Remove
                                                    </button>
                                                </form>
                                                
                                                <a href="view_details.php?id=<?php echo $job['job_id']; ?>" 
                                                   class="btn btn-outline-secondary">
                                                    <i class="bi bi-eye me-1"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border-top pt-3 mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>Posted on <?php echo date('M j, Y', strtotime($job['date_posted'])); ?>
                                    • <i class="bi bi-bookmark me-1"></i>Saved on <?php echo date('M j, Y', strtotime($job['saved_date'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center p-5 bg-white rounded-3 shadow-sm">
                    <i class="bi bi-bookmark display-4 text-muted"></i>
                    <h4 class="fw-semibold mt-3">No saved jobs yet</h4>
                    <p class="text-muted mb-4">Save jobs while browsing to review them later</p>
                    <a href="browse_job.php" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Browse Jobs
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>