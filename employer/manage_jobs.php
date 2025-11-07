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
            <button class="nav-link active px-4 py-2" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" role="tab">All Jobs</button>
            </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2" id="active-tab" data-bs-toggle="pill" data-bs-target="#active" type="button" role="tab">Active</button>
            </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2" id="drafts-tab" data-bs-toggle="pill" data-bs-target="#drafts" type="button" role="tab">Drafts</button>
            </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2" id="closed-tab" data-bs-toggle="pill" data-bs-target="#closed" type="button" role="tab">Closed</button>
            </li>
        </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="jobTabsContent">

        <!-- All Jobs -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
        <?php if (empty($jobs)) { ?>
            <div class="card text-center p-5">
            <div class="card-body">
                <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                <h4 class="fw-semibold">No jobs found</h4>
                <p class="text-muted mb-4">You haven't posted any jobs yet</p>
                <a href="post_job.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Post a Job
                </a>
            </div>
            </div>
        <?php } else { ?>
            <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php foreach ($jobs as $job): ?>
              <div class="col">
                <div class="card h-100 p-3 shadow-sm border-0 hover-shadow-sm transition">
                 <div class="card-body">
                 <h5 class="fw-bold text-secondary mb-1"><?= htmlspecialchars($job['job_title']) ?></h5>
                <p class="text-muted mb-2">
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['job_location']) ?>
                </p>
                <small>
                    <span class="badge 
                     <?= $job['status'] == 'Active' ? 'bg-success' : 
                            ($job['status'] == 'Draft' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                     <?= ucfirst(htmlspecialchars($job['status'])) ?>
                    </span>
                </small>
                <!-- Job Type & Work Arrangement -->
                <p class="mb-1"><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?></p>
                <p class="mb-1"><strong>Work Arrangement:</strong> <?= htmlspecialchars($job['work_arrangement']) ?></p>

                <!-- Salary -->
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

                <!-- Job Description -->
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Description</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_description'])) ?></p>
                </div>

                <!-- Responsibilities -->
                <?php if (!empty($job['job_responsibilities'])): ?>
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Responsibilities</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_responsibilities'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Qualifications -->
                <?php if (!empty($job['job_qualifications'])): ?>
                <div>
                <h6 class="fw-bold mb-1">Qualifications</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_qualifications'])) ?></p>
                </div>
                <?php endif; ?>
                    <div class="card-footer bg-transparent border-0 pt-2 d-flex justify-content-end gap-2">
                    <?php if (strtolower($job['status']) === 'active' || strtolower($job['status']) === 'draft'): ?>
                      <a href="edit_job.php?id=<?= $job['job_id'] ?>" class="btn btn-sm btn-outline-primary">
                       <i class="bi bi-pencil-square me-1"></i>Edit
                      </a>                
                    <?php endif; ?>
                    <!-- Delete Button -->
                    <button type="button" 
                        class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteJobModal"
                        data-job-id="<?= $job['job_id'] ?>">
                    <i class="bi bi-trash-fill me-1"></i>Delete
                    </button>

                    <?php if (strtolower($job['status']) !== 'closed'): ?>
                     <form action="../config/close_job_process.php" method="POST" onsubmit="return confirm('Mark this job as closed?');">
                        <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Close
                        </button>
                    </form>
                    <?php endif; ?>
            </div>
                 </div>
                </div>
              </div>
            <?php endforeach; ?>
            </div>
        <?php } ?>
        </div>

        <!-- Active Jobs -->
        <div class="tab-pane fade" id="active" role="tabpanel">
        <?php if (empty($active_jobs)) { ?>
            <div class="card text-center p-5">
            <div class="card-body">
                <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                <h4 class="fw-semibold">No active jobs</h4>
                <p class="text-muted mb-4">You currently have no published jobs.</p>
                <a href="post_job.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Post a Job
                </a>
            </div>
            </div>
        <?php } else { ?>
            <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php foreach ($active_jobs as $job): ?>
                <div class="col">
                <div class="card h-100 p-3 shadow-sm border-0">
                <div class="card-body">
                <!-- Job Title & Location -->
                <h5 class="fw-bold text-secondary mb-1"><?= htmlspecialchars($job['job_title']) ?></h5>
                <p class="text-muted mb-2">
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['job_location']) ?>
                </p>
                <span class="badge bg-success mb-3">Active</span>
                <!-- Job Type & Work Arrangement -->
                <p class="mb-1"><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?></p>
                <p class="mb-1"><strong>Work Arrangement:</strong> <?= htmlspecialchars($job['work_arrangement']) ?></p>

                <!-- Salary -->
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

                <!-- Job Description -->
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Description</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_description'])) ?></p>
                </div>

                <!-- Responsibilities -->
                <?php if (!empty($job['job_responsibilities'])): ?>
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Responsibilities</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_responsibilities'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Qualifications -->
                <?php if (!empty($job['job_qualifications'])): ?>
                <div>
                <h6 class="fw-bold mb-1">Qualifications</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_qualifications'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

                <div class="card-footer bg-transparent border-0 pt-2 d-flex justify-content-end gap-2">
                  <a href="edit_job.php?id=<?= $job['job_id'] ?>" class="btn btn-sm btn-outline-primary">
                   <i class="bi bi-pencil-square me-1"></i>Edit
                  </a>

                <button type="button" 
                        class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteJobModal"
                        data-job-id="<?= $job['job_id'] ?>">
                    <i class="bi bi-trash-fill me-1"></i>Delete
                </button>

                <?php if (strtolower($job['status']) !== 'closed'): ?>
                <form action="../config/close_job_process.php" method="POST" onsubmit="return confirm('Mark this job as closed?');">
                    <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                     <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Close
                     </button>
                </form>
                <?php endif; ?>
            </div>
                </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php } ?>
        </div>

        <!-- Draft Jobs -->
        <div class="tab-pane fade" id="drafts" role="tabpanel">
        <?php if (empty($draft_jobs)) { ?>
            <div class="card text-center p-5">
            <div class="card-body">
                <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                <h4 class="fw-semibold">No drafts</h4>
                <p class="text-muted mb-4">You have no jobs saved as draft.</p>
                <a href="post_job.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Post a Job
                </a>
            </div>
            </div>
        <?php } else { ?>
            <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php foreach ($draft_jobs as $job): ?>
                <div class="col">
                <div class="card h-100 p-3 shadow-sm border-0">
                <div class="card-body">

                <!-- Job Title & Location -->
                <h5 class="fw-bold text-secondary mb-1"><?= htmlspecialchars($job['job_title']) ?></h5>
                <p class="text-muted mb-2">
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['job_location']) ?>
                </p>
                <span class="badge bg-warning mb-3">Draft</span>

                <!-- Job Type & Work Arrangement -->
                <p class="mb-1"><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?></p>
                <p class="mb-1"><strong>Work Arrangement:</strong> <?= htmlspecialchars($job['work_arrangement']) ?></p>

                <!-- Salary -->
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

                <!-- Job Description -->
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Description</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_description'])) ?></p>
                </div>

                <!-- Responsibilities -->
                <?php if (!empty($job['job_responsibilities'])): ?>
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Responsibilities</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_responsibilities'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Qualifications -->
                <?php if (!empty($job['job_qualifications'])): ?>
                <div>
                <h6 class="fw-bold mb-1">Qualifications</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_qualifications'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

                <div class="card-footer bg-transparent border-0 pt-2 d-flex justify-content-end gap-2">
                <a href="edit_job.php?id=<?= $job['job_id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i>Edit
                </a>

                <button type="button" 
                        class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteJobModal"
                        data-job-id="<?= $job['job_id'] ?>">
                    <i class="bi bi-trash-fill me-1"></i>Delete
                </button>

                <?php if (strtolower($job['status']) !== 'closed'): ?>
                <form action="../config/close_job_process.php" method="POST" onsubmit="return confirm('Mark this job as closed?');">
                    <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                     <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Close
                     </button>
                </form>
                <?php endif; ?>
            </div>
                </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php } ?>
        </div>

        <!-- Closed Jobs -->
        <div class="tab-pane fade" id="closed" role="tabpanel">
        <?php if (empty($closed_jobs)) { ?>
            <div class="card text-center p-5">
            <div class="card-body">
                <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
                <h4 class="fw-semibold">No closed jobs</h4>
                <p class="text-muted mb-4">No jobs have been closed yet.</p>
            </div>
            </div>
        <?php } else { ?>
        <div class="row row-cols-1 row-cols-md-2 g-3">
        <?php foreach ($closed_jobs as $job): ?>
            <div class="col">
                <div class="card h-100 p-3 shadow-sm border-0">

                <div class="card-body">
                <!-- Job Title & Location -->
                <h5 class="fw-bold text-secondary mb-1"><?= htmlspecialchars($job['job_title']) ?></h5>
                <p class="text-muted mb-2">
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['job_location']) ?>
                </p>
                <span class="badge bg-secondary mb-3">Closed</span>

                <!-- Job Type & Work Arrangement -->
                <p class="mb-1"><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?></p>
                <p class="mb-1"><strong>Work Arrangement:</strong> <?= htmlspecialchars($job['work_arrangement']) ?></p>

                <!-- Salary -->
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

                <!-- Job Description -->
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Description</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_description'])) ?></p>
                </div>

                <!-- Responsibilities -->
                <?php if (!empty($job['job_responsibilities'])): ?>
                <div class="mb-2">
                <h6 class="fw-bold mb-1">Responsibilities</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_responsibilities'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Qualifications -->
                <?php if (!empty($job['job_qualifications'])): ?>
                <div>
                <h6 class="fw-bold mb-1">Qualifications</h6>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($job['job_qualifications'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Card Footer -->
            <div class="card-footer bg-transparent border-0 pt-2 d-flex justify-content-end gap-2">
                <button type="button"
                class="btn btn-sm btn-outline-danger"
                data-bs-toggle="modal"
                data-bs-target="#deleteJobModal"
                data-job-id="<?= $job['job_id'] ?>">
                <i class="bi bi-trash-fill me-1"></i>Delete
                </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php } ?>
    </div>
    </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteJobModal" tabindex="-1" aria-labelledby="deleteJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow-lg">
        <div class="modal-body p-4 p-md-5">
            <h2 class="h3 fw-bold mb-3">Are you sure?</h2>
            <p class="text-secondary mb-4">
            This will permanently delete this job posting and all associated applications. This action cannot be undone.
            </p>

            <div class="d-flex justify-content-end gap-3">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                Cancel
            </button>
            <form id="deleteJobForm" action="../config/delete_job_process.php" method="POST" class="d-inline">
                <input type="hidden" name="job_id" id="deleteJobId">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
            </div>
        </div>
        </div>
    </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteJobModal');
    const deleteForm = document.getElementById('deleteJobForm');

    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const jobId = button.getAttribute('data-job-id');
        document.getElementById('deleteJobId').value = jobId;
    });

    deleteForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(deleteForm);

        fetch(deleteForm.action, {
        method: 'POST',
        body: formData
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(deleteModal);

            if (data.status === 'success') {
            modal.hide();

            // Remove deleted job card smoothly
            const jobId = formData.get('job_id');
            const jobCard = document.querySelector(`[data-job-id="${jobId}"]`)?.closest('.col');
            if (jobCard) {
                jobCard.classList.add('fade-out');
                setTimeout(() => jobCard.remove(), 500);
            }

            // Show success alert
            showAlert(data.message, 'success');
            } else {
            showAlert(data.message, 'danger');
            }
        })
        .catch(() => {
            showAlert('An error occurred. Please try again.', 'danger');
        });
    });

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} text-center position-fixed top-0 start-50 translate-middle-x mt-3 shadow`;
        alertDiv.style.zIndex = '9999';
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 2500);
    }
    });
    </script>

    <!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
