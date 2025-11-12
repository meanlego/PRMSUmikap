<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

require_once '../database/prmsumikap_db.php';

$studentName = $_SESSION['name'];  
$accountType = ucfirst($_SESSION['role']);

try {
    $stmt = $pdo->prepare("SELECT student_id FROM students_profile WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student_profile) {
        $student_id = 0;
    } else {
        $student_id = $student_profile['student_id'];
    }
} catch(PDOException $e) {
    $student_id = 0;
    error_log("Applications page student profile error: " . $e->getMessage());
}

try {
    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE student_id = ?");
    $totalStmt->execute([$student_id]);
    $totalApplications = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

    $activeStmt = $pdo->prepare("SELECT COUNT(*) as active FROM applications WHERE student_id = ? AND status IN ('Pending', 'Shortlisted')");
    $activeStmt->execute([$student_id]);
    $activeApplications = $activeStmt->fetch(PDO::FETCH_ASSOC)['active'];

    $offersStmt = $pdo->prepare("SELECT COUNT(*) as offers FROM applications WHERE student_id = ? AND status = 'Accepted'");
    $offersStmt->execute([$student_id]);
    $jobOffers = $offersStmt->fetch(PDO::FETCH_ASSOC)['offers'];

    $applicationsStmt = $pdo->prepare("
        SELECT a.*, j.job_title, j.job_location, j.job_type, j.min_salary, j.max_salary, 
               e.company_name, e.contact_person,
               DATEDIFF(NOW(), a.date_applied) as days_ago
        FROM applications a
        JOIN jobs j ON a.job_id = j.job_id
        LEFT JOIN employers_profile e ON j.employer_id = e.employer_id
        WHERE a.student_id = ?
        ORDER BY a.date_applied DESC
    ");
    $applicationsStmt->execute([$student_id]);
    $allApplications = $applicationsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $totalApplications = 0;
    $activeApplications = 0;
    $jobOffers = 0;
    $allApplications = [];
    error_log("Applications error: " . $e->getMessage());
}

$activeApps = array_filter($allApplications, function($app) {
    return in_array($app['status'], ['Pending', 'Shortlisted']);
});

$archivedApps = array_filter($allApplications, function($app) {
    return $app['status'] === 'Rejected';
});

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Applications | PRMSUmikap</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap-rebase/assets/images/favicon.png">

<!-- Custom CSS -->
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
<style>
.application-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 4px solid #2575fc;
}
.application-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.status-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
}
.company-logo {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
}
</style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

<div id="main-content">

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

    <div class="welcome-card mb-4">
        <h1 class="display-5 fw-bold mb-2">My Applications</h1>
        <p class="fs-5 mb-4">Track all your job applications in one place</p>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3 p-3 shadow-sm bg-white bg-opacity-75">
                    <i class="bi bi-file-earmark-text-fill fs-2 text-black"></i>
                    <div>
                        <h6 class="text-black opacity-75 mb-1">Total Applications</h6>
                        <h3 class="fw-bold mb-0 text-black"><?php echo $totalApplications; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3 p-3 shadow-sm bg-white bg-opacity-75">
                    <i class="bi bi-graph-up fs-2 text-black"></i>
                    <div>
                        <h6 class="text-black opacity-75 mb-1">Active</h6>
                        <h3 class="fw-bold mb-0 text-black"><?php echo $activeApplications; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3 p-3 shadow-sm bg-white bg-opacity-75">
                    <i class="bi bi-briefcase-fill fs-2 text-black"></i>
                    <div>
                        <h6 class="text-black opacity-75 mb-1">Offers</h6>
                        <h3 class="fw-bold mb-0 text-black"><?php echo $jobOffers; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-2">
            <ul class="nav nav-pills justify-content-center gap-2" id="jobTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-2" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" role="tab">
                        All Applications (<?php echo $totalApplications; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2" id="active-tab" data-bs-toggle="pill" data-bs-target="#active" type="button" role="tab">
                        Active (<?php echo count($activeApps); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2" id="offers-tab" data-bs-toggle="pill" data-bs-target="#offers" type="button" role="tab">
                        Offers (<?php echo $jobOffers; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2" id="archived-tab" data-bs-toggle="pill" data-bs-target="#archived" type="button" role="tab">
                        Archived (<?php echo count($archivedApps); ?>)
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="jobTabsContent">
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
            <?php if (!empty($allApplications)): ?>
                <?php foreach($allApplications as $application): ?>
                    <div class="application-card card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="company-logo bg-light d-flex align-items-center justify-content-center flex-shrink-0">
                                            <i class="bi bi-building text-muted"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($application['job_title']); ?></h5>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($application['company_name']); ?></p>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($application['job_location']); ?>
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($application['job_type']); ?>
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    ₱<?php echo number_format($application['min_salary']); ?> - ₱<?php echo number_format($application['max_salary']); ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                Applied <?php 
                                                if ($application['days_ago'] == 0) echo 'today';
                                                elseif ($application['days_ago'] == 1) echo 'yesterday';
                                                else echo $application['days_ago'] . ' days ago';
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="status-badge badge 
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
                                    <div class="mt-2">
                                        <a href="view_details.php?id=<?php echo $application['job_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View Job
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                        <h4 class="fw-semibold">No applications yet</h4>
                        <p class="text-muted mb-4">Start applying to jobs that match your skills and interests</p>
                        <a href="browse_job.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Browse Jobs
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="active" role="tabpanel" aria-labelledby="active-tab">
            <?php if (!empty($activeApps)): ?>
                <?php foreach($activeApps as $application): ?>
                    <div class="application-card card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="company-logo bg-light d-flex align-items-center justify-content-center flex-shrink-0">
                                            <i class="bi bi-building text-muted"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($application['job_title']); ?></h5>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($application['company_name']); ?></p>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($application['job_location']); ?>
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($application['job_type']); ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                Applied <?php echo $application['days_ago'] == 0 ? 'today' : $application['days_ago'] . ' days ago'; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="status-badge badge 
                                        <?php echo $application['status'] == 'Pending' ? 'bg-warning' : 'bg-info'; ?>">
                                        <?php echo htmlspecialchars($application['status']); ?>
                                    </span>
                                    <div class="mt-2">
                                        <a href="job_details.php?id=<?php echo $application['job_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View Job
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                        <h4 class="fw-semibold">No active applications</h4>
                        <p class="text-muted mb-4">No active applications at the moment</p>
                        <a href="browse_job.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Browse Jobs
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="offers" role="tabpanel" aria-labelledby="offers-tab">
            <?php if ($jobOffers > 0): ?>
                <div class="card text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-trophy fs-1 text-warning mb-3"></i>
                        <h4 class="fw-semibold">Congratulations!</h4>
                        <p class="text-muted mb-4">You have <?php echo $jobOffers; ?> job offer(s)</p>
                        <p class="text-muted small">Employers will contact you directly with offer details.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                        <h4 class="fw-semibold">No offers yet</h4>
                        <p class="text-muted mb-4">Keep applying! Your next opportunity is waiting.</p>
                        <a href="browse_job.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Browse Jobs
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="archived" role="tabpanel" aria-labelledby="archived-tab">
            <?php if (!empty($archivedApps)): ?>
                <?php foreach($archivedApps as $application): ?>
                    <div class="application-card card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="company-logo bg-light d-flex align-items-center justify-content-center flex-shrink-0">
                                            <i class="bi bi-building text-muted"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($application['job_title']); ?></h5>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($application['company_name']); ?></p>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($application['job_location']); ?>
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($application['job_type']); ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                Applied <?php echo $application['days_ago'] == 0 ? 'today' : $application['days_ago'] . ' days ago'; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="status-badge badge bg-danger">
                                        <?php echo htmlspecialchars($application['status']); ?>
                                    </span>
                                    <div class="mt-2">
                                        <a href="job_details.php?id=<?php echo $application['job_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye me-1"></i>View Job
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-archive fs-1 text-secondary mb-3"></i>
                        <h4 class="fw-semibold">No archived applications</h4>
                        <p class="text-muted mb-4">No archived applications at the moment</p>
                        <a href="browse_job.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Browse Jobs
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>