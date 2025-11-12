<?php
session_start();

// Check if the user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

include __DIR__ . '/../database/prmsumikap_db.php';

$employer_id = $_SESSION['user_id'];
$application_id = $_GET['id'] ?? null;

if (!$application_id) {
    header("Location: applicants.php");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $new_status = '';

    switch ($action) {
        case 'shortlist': $new_status = 'Shortlisted'; break;
        case 'accept': $new_status = 'Accepted'; break;
        case 'reject': $new_status = 'Rejected'; break;
    }

    if ($new_status) {
        $updateStmt = $pdo->prepare("
            UPDATE applications 
            SET status = ? 
            WHERE application_id = ? 
        ");
        $updateStmt->execute([$new_status, $application_id]);

        $_SESSION['success_message'] = "Application status updated to $new_status successfully!";
        header("Location: view_application.php?id=" . $application_id);
        exit;
    }
}

$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$employerProfile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employerProfile) {
    $_SESSION['error_message'] = "Employer profile not found.";
    header("Location: applicants.php");
    exit;
}

$employer_id = $employerProfile['employer_id'];

// Fetch application details along with student, job, and employer info
$stmt = $pdo->prepare("
    SELECT 
        applications.application_id,
        applications.student_id,
        applications.job_id,
        applications.status,
        applications.date_applied,
        users.name,
        users.email,
        students_profile.phone,
        students_profile.address,
        jobs.job_title,
        jobs.job_location,
        jobs.min_salary,
        jobs.max_salary,
        jobs.job_type,
        employers_profile.company_name
    FROM applications
    JOIN users ON applications.student_id = users.user_id
    LEFT JOIN students_profile ON applications.student_id = students_profile.student_id
    JOIN jobs ON applications.job_id = jobs.job_id
    JOIN employers_profile ON jobs.employer_id = employers_profile.employer_id
    WHERE applications.application_id = ?
    AND jobs.employer_id = ?
");
$stmt->execute([$application_id, $employer_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    $_SESSION['error_message'] = "Application not found or you don't have permission to view it.";
    header("Location: applicants.php");
    exit;
}

$studentId = $application['student_id'];

// Fetch additional student info
$skillsStmt = $pdo->prepare("SELECT skill_name FROM student_skills WHERE user_id = ?");
$skillsStmt->execute([$studentId]);
$skills = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);

$educationStmt = $pdo->prepare("SELECT * FROM student_education WHERE user_id = ? ORDER BY end_year DESC");
$educationStmt->execute([$studentId]);
$education = $educationStmt->fetchAll(PDO::FETCH_ASSOC);

$experienceStmt = $pdo->prepare("SELECT * FROM student_experience WHERE user_id = ? ORDER BY end_year DESC");
$experienceStmt->execute([$studentId]);
$experience = $experienceStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Application Details | PRMSUmikap</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap/assets/images/favicon.png">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
<style>
.status-badge-large { font-size: 1rem; padding: 0.5rem 1.5rem; }
.info-label { font-weight: 600; color: #6c757d; font-size: 0.875rem; margin-bottom: 0.25rem; }
.info-value { font-size: 1rem; color: #212529; }
.action-btn { min-width: 140px; }
.section-title { border-left: 4px solid #0d6efd; padding-left: 1rem; margin-bottom: 1.5rem; }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div id="main-content">
    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); endif; ?>

    <div class="mb-3">
        <a href="applicants.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to All Applicants
        </a>
    </div>

    <!-- Application Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><?= htmlspecialchars($application['name']) ?></h2>
                <p class="text-muted mb-0"><i class="bi bi-briefcase me-2"></i>Applied for: <strong><?= htmlspecialchars($application['job_title']) ?></strong></p>
                <p class="text-muted mb-0"><i class="bi bi-calendar me-2"></i>Applied on: <?= date('F d, Y', strtotime($application['date_applied'])) ?></p>
            </div>
            <span class="badge status-badge-large bg-<?= 
                $application['status'] === 'Accepted' ? 'success' : 
                ($application['status'] === 'Rejected' ? 'danger' : 
                ($application['status'] === 'Shortlisted' ? 'info' : 'warning')) ?>">
                <?= htmlspecialchars($application['status']) ?>
            </span>
        </div>
    </div>

    <!-- Action Buttons -->
     <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Update Application Status</h5>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach(['shortlist'=>'Shortlisted','accept'=>'Accepted','reject'=>'Rejected'] as $key=>$status): ?>
                <!-- Button triggers modal -->
                <button type="button" class="btn btn-<?= $key==='shortlist'?'info':($key==='accept'?'success':'danger') ?> action-btn" 
                    data-bs-toggle="modal" data-bs-target="#confirmModal<?= $key ?>"
                    <?= $application['status']==$status?'disabled':'' ?>>
                    <i class="bi bi-<?= $key==='shortlist'?'star':'check-circle' ?> me-1"></i> <?= $status ?>
                </button>

                <!-- Modal -->
                <div class="modal fade" id="confirmModal<?= $key ?>" tabindex="-1" aria-labelledby="confirmModalLabel<?= $key ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="confirmModalLabel<?= $key ?>">Confirm <?= $status ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        Are you sure you want to mark this application as <strong><?= $status ?></strong>?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="<?= $key ?>">
                            <button type="submit" class="btn btn-<?= $key==='shortlist'?'info':($key==='accept'?'success':'danger') ?>">Yes, <?= $status ?></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

    <div class="row">
        <!-- Left Column: Student Info -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="section-title fw-bold">Contact Information</h5>
                    <div class="mb-3"><div class="info-label">Email</div><div class="info-value"><a href="mailto:<?= htmlspecialchars($application['email']) ?>"><?= htmlspecialchars($application['email']) ?></a></div></div>
                    <?php if(!empty($application['phone'])): ?>
                    <div class="mb-3"><div class="info-label">Phone</div><div class="info-value"><a href="tel:<?= htmlspecialchars($application['phone']) ?>"><?= htmlspecialchars($application['phone']) ?></a></div></div>
                    <?php endif; ?>
                    <?php if(!empty($application['address'])): ?>
                    <div class="mb-0"><div class="info-label">Address</div><div class="info-value"><?= htmlspecialchars($application['address']) ?></div></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(!empty($skills)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="section-title fw-bold">Skills</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach($skills as $skill): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                            <?= htmlspecialchars($skill['skill_name']) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Job & Education/Experience -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="section-title fw-bold">Job Details</h5>
                    <div class="mb-3"><div class="info-label">Company</div><div class="info-value"><?= htmlspecialchars($application['company_name']) ?></div></div>
                    <div class="mb-3"><div class="info-label">Location</div><div class="info-value"><?= htmlspecialchars($application['job_location']) ?></div></div>
                    <div class="mb-3"><div class="info-label">Job Type</div><div class="info-value"><span class="badge bg-secondary"><?= htmlspecialchars($application['job_type']) ?></span></div></div>
                    <div class="mb-0"><div class="info-label">Salary Range</div><div class="info-value">₱<?= number_format($application['min_salary']) ?> - ₱<?= number_format($application['max_salary']) ?></div></div>
                </div>
            </div>

            <?php if(!empty($education)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="section-title fw-bold">Education</h5>
                    <?php foreach($education as $edu): ?>
                        <div class="mb-3 pb-3 <?= $edu !== end($education)?'border-bottom':'' ?>">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($edu['degree']) ?></h6>
                            <div class="text-primary mb-1"><?= htmlspecialchars($edu['school_name']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($edu['start_year']) ?> - <?= !empty($edu['end_year'])?htmlspecialchars($edu['end_year']):'Present' ?></div>
                            <?php if(!empty($edu['field_of_study'])): ?><div class="text-muted small">Field: <?= htmlspecialchars($edu['field_of_study']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($experience)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="section-title fw-bold">Work Experience</h5>
                    <?php foreach($experience as $exp): ?>
                        <div class="mb-3 pb-3 <?= $exp !== end($experience)?'border-bottom':'' ?>">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($exp['job_title']) ?></h6>
                            <div class="text-primary mb-1"><?= htmlspecialchars($exp['company_name']) ?></div>
                            <div class="text-muted small mb-2"><?= date('M Y', strtotime($exp['start_date'])) ?> - <?= $exp['end_date']?date('M Y', strtotime($exp['end_date'])):'Present' ?></div>
                            <?php if(!empty($exp['description'])): ?><p class="text-muted small mb-0"><?= htmlspecialchars($exp['description']) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php if(!empty($application['resume_path'])): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-4">
            <i class="bi bi-file-earmark-pdf fs-1 text-danger mb-3"></i>
            <h5 class="fw-bold mb-3">Resume/CV</h5>
            <a href="<?= htmlspecialchars($application['resume_path']) ?>" class="btn btn-primary" download target="_blank">
                <i class="bi bi-download me-2"></i>Download Resume
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>