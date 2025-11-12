<?php
session_start();

// Redirect if not logged in or not employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

include __DIR__ . '/../database/prmsumikap_db.php';
$user_id = $_SESSION['user_id'];

// Get the employer_id linked to this user
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employer) {
    die("Employer profile not found. Please complete your employer profile first.");
}

$employer_id = $employer['employer_id'];

// Fetch all jobs for this employer
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY date_posted DESC");
$stmt->execute([$employer_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter by status
$active_jobs = array_filter($jobs, fn($job) => $job['status'] === 'Active');
$draft_jobs  = array_filter($jobs, fn($job) => $job['status'] === 'Draft');
$closed_jobs = array_filter($jobs, fn($job) => $job['status'] === 'Closed');

// Function to render job card
function renderJobCard($job) {
    ob_start();
    ?>
    <div class="col">
        <div class="card h-100 p-3 shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold text-secondary mb-1"><?= htmlspecialchars($job['job_title']) ?></h5>
                <p class="text-muted mb-2">
                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['job_location']) ?>
                </p>
                <span class="badge mb-3 <?= 
                    $job['status'] === 'Active' ? 'bg-success' : 
                    ($job['status'] === 'Draft' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                    <?= htmlspecialchars($job['status']) ?>
                </span>

                <p class="mb-1"><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?></p>
                <p class="mb-1"><strong>Work Arrangement:</strong> <?= htmlspecialchars($job['work_arrangement']) ?></p>

                <p class="mb-2">
                    <strong>Salary:</strong>
                    <?php if (!empty($job['min_salary']) && !empty($job['max_salary'])): ?>
                        ₱<?= number_format($job['min_salary']) ?> - ₱<?= number_format($job['max_salary']) ?>
                    <?php elseif (!empty($job['min_salary'])): ?>
                        From ₱<?= number_format($job['min_salary']) ?>
                    <?php elseif (!empty($job['max_salary'])): ?>
                        Up to ₱<?= number_format($job['max_salary']) ?>
                    <?php else: ?>
                        Not specified
                    <?php endif; ?>
                </p>

                <div class="mb-2">
                    <h6 class="fw-bold mb-1">Description</h6>
                    <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_description'])) ?></p>
                </div>

                <?php if (!empty($job['job_responsibilities'])): ?>
                <div class="mb-2">
                    <h6 class="fw-bold mb-1">Responsibilities</h6>
                    <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_responsibilities'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($job['job_qualifications'])): ?>
                <div>
                    <h6 class="fw-bold mb-1">Qualifications</h6>
                    <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_qualifications'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-transparent border-0 pt-2 d-flex justify-content-end gap-2">
                <?php if (strtolower($job['status']) !== 'closed'): ?>
                    <a href="edit_job.php?id=<?= $job['job_id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil-square me-1"></i>Edit
                    </a>
                <?php endif; ?>

                <button type="button" 
                        class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteJobModal"
                        data-job-id="<?= $job['job_id'] ?>"
                        data-job-title="<?= htmlspecialchars($job['job_title']) ?>">
                    <i class="bi bi-trash-fill me-1"></i>Delete
                </button>

                <?php if (strtolower($job['status']) !== 'closed'): ?>
                    <button type="button" 
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" 
                            data-bs-target="#closeJobModal"
                            data-job-id="<?= $job['job_id'] ?>"
                            data-job-title="<?= htmlspecialchars($job['job_title']) ?>">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Function to render empty state
function renderEmptyState($type) {
    $messages = [
        'all' => ['title' => 'No jobs found', 'message' => "You haven't posted any jobs yet", 'showButton' => true],
        'active' => ['title' => 'No active jobs', 'message' => 'You currently have no published jobs.', 'showButton' => true],
        'drafts' => ['title' => 'No drafts', 'message' => 'You have no jobs saved as draft.', 'showButton' => true],
        'closed' => ['title' => 'No closed jobs', 'message' => 'No jobs have been closed yet.', 'showButton' => false]
    ];
    
    $config = $messages[$type];
    
    ob_start();
    ?>
    <div class="card text-center p-5">
        <div class="card-body">
            <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
            <h4 class="fw-semibold"><?= $config['title'] ?></h4>
            <p class="text-muted mb-4"><?= $config['message'] ?></p>
            <?php if ($config['showButton']): ?>
                <a href="post_job.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Post a Job
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs | PRMSUmikap</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <!-- Tab Icon -->
    <link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap/assets/images/favicon.png">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div id="main-content">
    <!-- Header -->
    <div class="welcome-card mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="display-5 fw-bold mt-2">Manage Jobs</h1>
            <p class="fs-5">View and manage all your job postings</p>
        </div>
        <a href="post_job.php" class="btn btn-light border">
            <i class="bi bi-plus-circle me-2"></i>Post New Job
        </a>
    </div>

    <!-- Tabs -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-2">
            <ul class="nav nav-pills justify-content-center gap-2" id="jobTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-2" id="all-tab" data-bs-toggle="pill" 
                            data-bs-target="#all" type="button" role="tab">All Jobs</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2" id="active-tab" data-bs-toggle="pill" 
                            data-bs-target="#active" type="button" role="tab">Active</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2" id="drafts-tab" data-bs-toggle="pill" 
                            data-bs-target="#drafts" type="button" role="tab">Drafts</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2" id="closed-tab" data-bs-toggle="pill" 
                            data-bs-target="#closed" type="button" role="tab">Closed</button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="jobTabsContent">
        <!-- All Jobs -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <?php if (empty($jobs)): ?>
                <?= renderEmptyState('all') ?>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php foreach ($jobs as $job): ?>
                        <?= renderJobCard($job) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Active Jobs -->
        <div class="tab-pane fade" id="active" role="tabpanel">
            <?php if (empty($active_jobs)): ?>
                <?= renderEmptyState('active') ?>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php foreach ($active_jobs as $job): ?>
                        <?= renderJobCard($job) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Draft Jobs -->
        <div class="tab-pane fade" id="drafts" role="tabpanel">
            <?php if (empty($draft_jobs)): ?>
                <?= renderEmptyState('drafts') ?>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php foreach ($draft_jobs as $job): ?>
                        <?= renderJobCard($job) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Closed Jobs -->
        <div class="tab-pane fade" id="closed" role="tabpanel">
            <?php if (empty($closed_jobs)): ?>
                <?= renderEmptyState('closed') ?>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php foreach ($closed_jobs as $job): ?>
                        <?= renderJobCard($job) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Close Job Confirmation Modal -->
<div class="modal fade" id="closeJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow-lg border-0">
            <div class="modal-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-x-circle fs-1 text-warning"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-2">Close Job Posting?</h2>
                    <p class="text-secondary mb-0" id="closeJobTitle"></p>
                </div>
                
                <div class="alert alert-warning border-0 mb-4">
                    <div class="d-flex">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <div>
                            <strong>What happens when you close a job?</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li>The job will no longer be visible to applicants</li>
                                <li>You can still view applications received</li>
                                <li>This action can be reversed later</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form id="closeJobForm" action="../config/close_job_process.php" method="POST">
                    <input type="hidden" name="job_id" id="closeJobId">
                    
                    <div class="d-flex justify-content-end gap-3">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-x-circle me-2"></i>Close Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Job Confirmation Modal -->
<div class="modal fade" id="deleteJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow-lg border-0">
            <div class="modal-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-trash-fill fs-1 text-danger"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-2">Delete Job Posting?</h2>
                    <p class="text-secondary mb-0" id="deleteJobTitle"></p>
                </div>
                
                <div class="alert alert-danger border-0 mb-4">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div>
                            <strong>Warning: This action cannot be undone!</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li>The job posting will be permanently deleted</li>
                                <li>All associated applications will be removed</li>
                                <li>This data cannot be recovered</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                        <i class="bi bi-trash-fill me-2"></i>Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteJobModal');
    const closeModal = document.getElementById('closeJobModal');
    let deleteJobId = null;

    // ===== DELETE JOB FUNCTIONALITY =====
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        deleteJobId = button.getAttribute('data-job-id');
        const jobTitle = button.getAttribute('data-job-title');
        
        if (jobTitle) {
            document.getElementById('deleteJobTitle').textContent = `"${jobTitle}"`;
        }
    });

    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    confirmDeleteBtn.addEventListener('click', function() {
        if (!deleteJobId) return;

        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        const formData = new FormData();
        formData.append('job_id', deleteJobId);

        fetch('../config/delete_job_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(deleteModal);

            if (data.status === 'success') {
                modal.hide();

                const jobCards = document.querySelectorAll(`button[data-job-id="${deleteJobId}"]`);
                jobCards.forEach(button => {
                    const jobCard = button.closest('.col');
                    if (jobCard) {
                        jobCard.style.transition = 'opacity 0.3s ease';
                        jobCard.style.opacity = '0';
                        
                        setTimeout(() => {
                            jobCard.remove();
                            checkEmptyStates();
                        }, 300);
                    }
                });

                showAlert(data.message, 'success');
            } else {
                showAlert(data.message, 'danger');
            }

            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = '<i class="bi bi-trash-fill me-2"></i>Delete Permanently';
            deleteJobId = null;
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = '<i class="bi bi-trash-fill me-2"></i>Delete Permanently';
        });
    });

    // ===== CLOSE JOB FUNCTIONALITY =====
    closeModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const jobId = button.getAttribute('data-job-id');
        const jobTitle = button.getAttribute('data-job-title');
        
        document.getElementById('closeJobId').value = jobId;
        
        if (jobTitle) {
            document.getElementById('closeJobTitle').textContent = `"${jobTitle}"`;
        }
    });

    const closeJobForm = document.getElementById('closeJobForm');
    closeJobForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = closeJobForm.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Closing...';

        const formData = new FormData(closeJobForm);

        fetch(closeJobForm.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(closeModal);
            modal.hide();

            if (data.status === 'success') {
                showAlert(data.message || 'Job closed successfully!', 'success');
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert(data.message || 'Failed to close job.', 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        });
    });

    // ===== HELPER FUNCTIONS =====
    function checkEmptyStates() {
        const tabs = [
            { id: 'all', title: 'jobs', message: "You haven't posted any jobs yet", button: true },
            { id: 'active', title: 'active jobs', message: 'You currently have no published jobs.', button: true },
            { id: 'drafts', title: 'drafts', message: 'You have no jobs saved as draft.', button: true },
            { id: 'closed', title: 'closed jobs', message: 'No jobs have been closed yet.', button: false }
        ];

        tabs.forEach(tab => {
            const tabPane = document.getElementById(tab.id);
            const jobsContainer = tabPane.querySelector('.row');
            
            if (jobsContainer && jobsContainer.querySelectorAll('.col').length === 0) {
                const emptyState = `
                    <div class="card text-center p-5">
                        <div class="card-body">
                            <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                            <h4 class="fw-semibold">No ${tab.title}</h4>
                            <p class="text-muted mb-4">${tab.message}</p>
                            ${tab.button ? '<a href="post_job.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Post a Job</a>' : ''}
                        </div>
                    </div>
                `;
                jobsContainer.outerHTML = emptyState;
            }
        });
    }

    function showAlert(message, type) {
        const existingAlert = document.querySelector('.custom-alert');
        if (existingAlert) existingAlert.remove();

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show custom-alert position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg`;
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 3000);
    }
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>