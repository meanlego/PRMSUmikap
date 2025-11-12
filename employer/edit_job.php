<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
  header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
  exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
$employer_id = $employer['employer_id'] ?? null;

if (!$employer_id) {
  die("Employer record not found.");
}

if (!isset($_GET['id'])) {
  header("Location: manage_jobs.php");
  exit;
}

$job_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE job_id = ? AND employer_id = ?");
$stmt->execute([$job_id, $employer_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
  die("Job not found or unauthorized access.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Job | PRMSUmikap</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap-rebase/assets/images/favicon.png">

<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div id="main-content">

      <div class="welcome-card mb-4">
          <div class="mb-4">
            <h1 class="display-5 fw-bold">Edit Job Posting</h1>
            <p class="text-muted">Update your job details and publish changes</p>
          </div>
      </div>
  

  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <form id="jobForm" action="../config/edit_job_process.php" method="POST">
        <input type="hidden" name="job_id" value="<?= htmlspecialchars($job['job_id']) ?>">
        <input type="hidden" name="status" id="jobStatus" value="Active">

       
        <div class="mb-4">
          <label class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="job_title" 
                 value="<?= htmlspecialchars($job['job_title']) ?>" 
                 placeholder="e.g., Senior Software Engineer" required>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Job Description <span class="text-danger">*</span></label>
          <textarea class="form-control" name="job_description" rows="5" 
                    placeholder="Describe the role and what the candidate will do..." 
                    required><?= htmlspecialchars($job['job_description']) ?></textarea>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Job Type <span class="text-danger">*</span></label>
            <select class="form-select" name="job_type" required>
              <option value="">Select Type</option>
              <option value="Full-time" <?= $job['job_type'] === 'Full-time' ? 'selected' : '' ?>>Full-time</option>
              <option value="Part-time" <?= $job['job_type'] === 'Part-time' ? 'selected' : '' ?>>Part-time</option>
              <option value="Contract" <?= $job['job_type'] === 'Contract' ? 'selected' : '' ?>>Contract</option>
              <option value="Internship" <?= $job['job_type'] === 'Internship' ? 'selected' : '' ?>>Internship</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Work Arrangement <span class="text-danger">*</span></label>
            <select class="form-select" name="work_arrangement" required>
              <option value="">Select Arrangement</option>
              <option value="On-site" <?= $job['work_arrangement'] === 'On-site' ? 'selected' : '' ?>>On-site</option>
              <option value="Remote" <?= $job['work_arrangement'] === 'Remote' ? 'selected' : '' ?>>Remote</option>
              <option value="Hybrid" <?= $job['work_arrangement'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
            </select>
          </div>
        </div>


        <div class="mb-4">
          <label class="form-label fw-semibold">Job Location <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="job_location" 
                 value="<?= htmlspecialchars($job['job_location']) ?>" 
                 placeholder="e.g., Manila, Philippines" required>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Minimum Salary (₱)</label>
            <input type="number" class="form-control" name="min_salary" 
                   value="<?= htmlspecialchars($job['min_salary']) ?>" 
                   placeholder="e.g., 25000" min="0">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Maximum Salary (₱)</label>
            <input type="number" class="form-control" name="max_salary" 
                   value="<?= htmlspecialchars($job['max_salary']) ?>" 
                   placeholder="e.g., 35000" min="0">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Qualifications</label>
          <textarea class="form-control" name="job_qualifications" rows="4" 
                    placeholder="List required qualifications and skills..."><?= htmlspecialchars($job['job_qualifications']) ?></textarea>
          <small class="text-muted">Optional: List education, experience, and skills required</small>
        </div>


        <div class="mb-4">
          <label class="form-label fw-semibold">Responsibilities</label>
          <textarea class="form-control" name="job_responsibilities" rows="4" 
                    placeholder="List key responsibilities and duties..."><?= htmlspecialchars($job['job_responsibilities']) ?></textarea>
          <small class="text-muted">Optional: Describe what the candidate will be doing</small>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <a href="manage_jobs.php" class="btn btn-outline-secondary px-4">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </a>
          <button type="button" class="btn btn-outline-primary px-4" id="saveDraftBtn">
            <i class="bi bi-file-earmark me-2"></i>Save as Draft
          </button>
          <button type="button" class="btn btn-primary px-4" id="publishBtn">
            <i class="bi bi-check-circle me-2"></i>Update & Publish
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-body text-center p-5">
        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
          <i class="bi bi-check-circle-fill fs-1 text-success"></i>
        </div>
        <h3 class="fw-bold mb-2" id="successTitle">Success!</h3>
        <p class="text-muted mb-4" id="successMessage"></p>
        <button type="button" class="btn btn-success px-4" onclick="window.location.href='manage_jobs.php'">
          <i class="bi bi-arrow-left me-2"></i>Back to Jobs
        </button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-body text-center p-5">
        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
          <i class="bi bi-x-circle-fill fs-1 text-danger"></i>
        </div>
        <h3 class="fw-bold mb-2">Error</h3>
        <p class="text-muted mb-4" id="errorMessage"></p>
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const jobForm = document.getElementById('jobForm');
  const jobStatus = document.getElementById('jobStatus');
  const saveDraftBtn = document.getElementById('saveDraftBtn');
  const publishBtn = document.getElementById('publishBtn');

  saveDraftBtn.addEventListener('click', function() {
    if (!jobForm.checkValidity()) {
      jobForm.reportValidity();
      return;
    }
    
    jobStatus.value = 'Draft';
    submitJob('draft');
  });


  publishBtn.addEventListener('click', function() {
    if (!jobForm.checkValidity()) {
      jobForm.reportValidity();
      return;
    }
    
    jobStatus.value = 'Active';
    submitJob('publish');
  });

  function submitJob(action) {

    saveDraftBtn.disabled = true;
    publishBtn.disabled = true;

    if (action === 'draft') {
      saveDraftBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    } else {
      publishBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    }

    const formData = new FormData(jobForm);

    fetch(jobForm.action, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        
        if (action === 'draft') {
          document.getElementById('successTitle').textContent = 'Saved as Draft!';
          document.getElementById('successMessage').textContent = 'Your job has been saved as a draft. You can publish it later.';
        } else {
          document.getElementById('successTitle').textContent = 'Job Updated!';
          document.getElementById('successMessage').textContent = 'Your job posting has been updated and is now live.';
        }
        
        successModal.show();
      } else {
        showError(data.message || 'Failed to update job. Please try again.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showError('An unexpected error occurred. Please try again.');
    })
    .finally(() => {
      publishBtn.disabled = false;
      saveDraftBtn.innerHTML = '<i class="bi bi-file-earmark me-2"></i>Save as Draft';
      publishBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update & Publish';
    });
  }

  function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
  }
});
</script>

</body>
</html>