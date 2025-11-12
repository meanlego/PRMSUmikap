<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

// Include database connection
require_once '../database/prmsumikap_db.php';

$studentName = $_SESSION['name'];  
$accountType = ucfirst($_SESSION['role']);
$student_id = $_SESSION['user_id'];

// Fetch dashboard statistics
try {
    // Total applications count
    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE student_id = ?");
    $totalStmt->execute([$student_id]);
    $totalApplications = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Active applications count (Pending + Shortlisted)
    $activeStmt = $pdo->prepare("SELECT COUNT(*) as active FROM applications WHERE student_id = ? AND status IN ('Pending', 'Shortlisted')");
    $activeStmt->execute([$student_id]);
    $activeApplications = $activeStmt->fetch(PDO::FETCH_ASSOC)['active'];

    // Job offers count
    $offersStmt = $pdo->prepare("SELECT COUNT(*) as offers FROM applications WHERE student_id = ? AND status = 'Accepted'");
    $offersStmt->execute([$student_id]);
    $jobOffers = $offersStmt->fetch(PDO::FETCH_ASSOC)['offers'];

    // Saved jobs count (if you have a saved_jobs table)
    $savedJobs = 0; // Default to 0

    // Fetch recent applications (last 3)
    $recentStmt = $pdo->prepare("
        SELECT a.*, j.job_title, j.job_location, j.job_type, e.company_name
        FROM applications a
        JOIN jobs j ON a.job_id = j.job_id
        LEFT JOIN employers_profile e ON j.employer_id = e.employer_id
        WHERE a.student_id = ?
        ORDER BY a.date_applied DESC
        LIMIT 3
    ");
    $recentStmt->execute([$student_id]);
    $recentApplications = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch recommended jobs (latest active jobs)
    $recommendedStmt = $pdo->prepare("
        SELECT j.*, e.company_name
        FROM jobs j 
        LEFT JOIN employers_profile e ON j.employer_id = e.employer_id 
        WHERE j.status = 'Active' 
        ORDER BY j.date_posted DESC 
        LIMIT 3
    ");
    $recommendedStmt->execute();
    $recommendedJobs = $recommendedStmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Handle errors gracefully
    $totalApplications = 0;
    $activeApplications = 0;
    $jobOffers = 0;
    $savedJobs = 0;
    $recentApplications = [];
    $recommendedJobs = [];
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Portal Dashboard | PRMSUmikap</title>

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
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 6px;
}
.salary-range {
    font-weight: 600;
    color: #198754;
    font-size: 0.9rem;
}
.status-badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}
</style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="welcome-card mb-4">
            <small class="opacity-75">✦ Welcome Back ✦</small>
            <h1 class="display-5 fw-bold mt-2">Hello, <?php echo htmlspecialchars($studentName); ?>!</h1>
            <p class="fs-5">Ready to find your next opportunity?</p>
            <div class="d-flex flex-wrap gap-3 mt-3">
                <a href="browse_job.php" class="btn btn-light text-primary rounded-pill px-4 py-2 fw-bold">
                    <i class="bi bi-search me-2"></i> Browse Jobs
                </a>
                <a href="student_profile.php" class="btn btn-light rounded-pill px-4 py-2 opacity-50">
                    Complete Profile
                </a>
            </div>
        </div>

        <!-- STAT CARDS -->
        <div class="row mb-4 g-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <i class="bi bi-file-earmark-text-fill fs-2 text-primary"></i>
                    <div>
                        <h5 class="text-muted mb-1">Total Applications</h5>
                        <h2 class="fw-bold mb-0"><?php echo $totalApplications; ?></h2>
                        <p class="text-muted mb-0"><?php echo $activeApplications; ?> active</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <i class="bi bi-graph-up fs-2 text-info"></i>
                    <div>
                        <h5 class="text-muted mb-1">Job Offers</h5>
                        <h2 class="fw-bold mb-0"><?php echo $jobOffers; ?></h2>
                        <p class="<?php echo $jobOffers > 0 ? 'text-success' : 'text-muted'; ?> mb-0">
                            <?php echo $jobOffers > 0 ? 'Congratulations! 🎉' : 'Keep applying!'; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <i class="bi bi-bookmark-fill fs-2 text-primary"></i>
                    <div>
                        <h5 class="text-muted mb-1">Saved Jobs</h5>
                        <h2 class="fw-bold mb-0"><?php echo $savedJobs; ?></h2>
                        <p class="text-muted mb-0">Ready to apply</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT APPLICATIONS & FOR YOU -->
        <div class="row g-4">
            <!-- Recent Applications Column -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold"><i class="bi bi-clock-history me-2"></i> Recent Applications</h4>
                    <a href="job_applications.php" class="fw-bold text-decoration-none">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                
                <?php if (!empty($recentApplications)): ?>
                    <div class="bg-white rounded-3 shadow-sm">
                        <?php foreach($recentApplications as $application): ?>
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($application['job_title']); ?></h6>
                                        <p class="text-muted mb-1 small">
                                            <i class="bi bi-building me-1"></i><?php echo htmlspecialchars($application['company_name']); ?>
                                            • <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($application['job_location']); ?>
                                        </p>
                                        <small class="text-muted">
                                            Applied on: <?php echo date('M j, Y', strtotime($application['date_applied'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge status-badge 
                                        <?php 
                                        switch($application['status']) {
                                            case 'Pending': echo 'bg-warning'; break;
                                            case 'Shortlisted': echo 'bg-info'; break;
                                            case 'Accepted': echo 'bg-success'; break;
                                            case 'Rejected': echo 'bg-danger'; break;
                                            default: echo 'bg-secondary';
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($application['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5 bg-white rounded-3 shadow-sm">
                        <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No applications yet.</p>
                        <a href="browse_job.php" class="btn btn-primary">Browse Jobs</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recommended Jobs Column -->
            <div class="col-lg-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-star me-2"></i> Recommended For You</h4>
                
                <?php if (!empty($recommendedJobs)): ?>
                    <?php foreach($recommendedJobs as $job): ?>
                        <div class="job-card card mb-3 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <div class="company-logo bg-light d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="bi bi-building text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 small"><?php echo htmlspecialchars($job['job_title']); ?></h6>
                                        <p class="text-muted mb-1 small"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark small">
                                        <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($job['job_location']); ?>
                                    </span>
                                    <span class="badge bg-light text-dark small">
                                        <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($job['job_type']); ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="salary-range">
                                        ₱<?php echo number_format($job['min_salary']); ?>+
                                    </span>
                                    <a href="view_details.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-3">
                        <a href="browse_job.php" class="btn w-100 py-2 fw-bold text-white" 
                           style="background: linear-gradient(90deg, #11bfcb, #2575fc); border: none;">
                            View All Jobs <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center p-4 bg-white rounded-3 shadow-sm">
                        <i class="bi bi-briefcase display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No recommended jobs at the moment.</p>
                        <a href="browse_job.php" class="btn w-100 py-2 fw-bold text-white" 
                           style="background: linear-gradient(90deg, #11bfcb, #2575fc); border: none;">
                            Browse All Jobs
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>