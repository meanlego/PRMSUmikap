<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

include __DIR__ . '/../database/prmsumikap_db.php';
include __DIR__ . '/../config/fetch_employer_dashboard_data.php';

$employerName = $_SESSION['name'];  
$accountType = ucfirst($_SESSION['role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employer Dashboard | PRMSUmikap</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap-rebase/assets/images/favicon.png">

<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

    <?php include __DIR__. '/../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="welcome-card mb-4">
            <small class="opacity-75">✦ Welcome Back ✦</small>
            <h1 class="display-5 fw-bold mt-2">Hello, <?php echo htmlspecialchars($employerName); ?>!</h1>
            <p class="fs-5">Ready to find your next great hire?</p>
            <div class="d-flex flex-wrap gap-3 mt-3">
                 <a href="post_job.php" class="btn btn-light text-primary rounded-pill px-4 py-2 fw-bold">
                    <i class="bi bi-plus-circle me-2"></i> Post a New Job
                </a>
                <a href="manage_jobs.php" class="btn btn-outline-light rounded-pill fw-semibold px-4 py-2">
                    <i class="bi bi-briefcase me-2"></i> Manage Posted Jobs
                </a>
                <a href="profile.php" class="btn btn-light rounded-pill px-4 py-2 opacity-50">
                    Complete Profile
                </a>
            </div>
        </div>

        <div class="row mb-4 g-4">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <i class="bi bi-file-earmark-text-fill fs-2 text-primary"></i>
                    <div>
                        <h5 class="text-muted mb-1">Active Jobs</h5>
                        <h2 class="fw-bold mb-0"><?= $activeJobs ?></h2>
                        <p class="text-muted mb-0"><?= $activeJobs ?> active</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <i class="bi bi-graph-up fs-2 text-info"></i>
                    <div>
                        <h5 class="text-muted mb-1">New Applications</h5>
                        <h2 class="fw-bold mb-0"><?= $newApplications ?></h2>
                        <p class="text-success mb-0">This Week</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <i class="bi bi-bookmark-fill fs-2 text-primary"></i>
                    <div>
                        <h5 class="text-muted mb-1">Short Listed</h5>
                        <h2 class="fw-bold mb-0"><?= $shortListed ?></h2>
                        <p class="text-muted mb-0">Ready for Interviews</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold"><i class="bi bi-clock-history me-2"></i> Recent Posted Jobs</h4>
                    <a href="manage_jobs.php" class="text-decoration-none fw-bold">View All <i class="bi bi-arrow-right"></i></a>
                </div>

                <?php if (!empty($recentJobs)): ?>
                    <div class="bg-white rounded-3 shadow-sm p-3">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Date Posted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentJobs as $job): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($job['job_title']) ?></td>
                                        <td><?= htmlspecialchars($job['date_posted']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $job['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                                <?= htmlspecialchars($job['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5 bg-white rounded-3 shadow-sm">
                        <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No jobs posted yet.</p>
                        <a href="post_job.php" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle me-2"></i>Post Your First Job
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold"><i class="bi bi-person-lines-fill me-2"></i> Recent Applicants</h4>
                    <a href="applicants.php" class="text-decoration-none fw-bold">View All <i class="bi bi-arrow-right"></i></a>
                </div>

                <?php if (!empty($recentApplicants)): ?>
                    <div class="bg-white rounded-3 shadow-sm p-3">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th>Job Applied</th>
                                    <th>Status</th>
                                    <th>Date Applied</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentApplicants as $app): ?>
                                    <tr>
                                        <td class="fw-semibold">
                                            <i class="bi bi-person-circle me-2 text-primary"></i>
                                            <?= htmlspecialchars($app['name']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($app['job_title']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $app['status'] === 'Accepted' ? 'success' : 
                                                ($app['status'] === 'Rejected' ? 'danger' : 
                                                ($app['status'] === 'Shortlisted' ? 'info' : 'warning')) 
                                            ?>">
                                                <?= htmlspecialchars($app['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($app['date_applied']) ?></td>
                                        <td>
                                            <a href="view_application.php?id=<?= $app['application_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5 bg-white rounded-3 shadow-sm">
                        <i class="bi bi-person-x display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No applications yet.</p>
                        <p class="text-muted small">Applications will appear here as candidates apply for your positions.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>