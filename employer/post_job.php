<?php
session_start();
  include __DIR__. '/../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Job | PRMSUmikap</title>

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
    <div id="main-content">
    <div class="welcome-card mb-4">
        <h1 class="display-5 fw-bold mt-2">Post a New Job</h1>
        <p class="fs-5">Find the perfect candidate for your team</p>
    </div>  

   <div class="card p-5">
      <h3 class="fw-bold mb-4 text-center">Job Details</h3>

      <form id="jobForm" action="../config/job_post_process.php" method="POST">

  <!-- Hidden status input -->
  <input type="hidden" name="status" id="jobStatus" value="draft">

  <!-- Job Title -->
  <div class="mb-3">
    <label class="form-label">Job Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" name="job_title" placeholder="e.g., Software Engineer" required>
  </div>

        <!-- Job Type & Work Arrangement -->
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Job Type <span class="text-danger">*</span></label>
            <select class="form-select" name="job_type" required>
              <option value="">Select Job Type</option>
              <option>Full-time</option>
              <option>Part-time</option>
              <option>Contract</option>
              <option>Internship</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Work Arrangement <span class="text-danger">*</span></label>
            <select class="form-select" name="work_arrangement" required>
              <option value="">Select Arrangement</option>
              <option>On-site</option>
              <option>Hybrid</option>
              <option>Remote</option>
            </select>
          </div>
        </div>

        <!-- Location -->
        <div class="mt-3 mb-3">
          <label class="form-label">Location <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="job_location" placeholder="e.g., Purok 3, Palanginan, Iba, Zambales" required>
        </div>

        <!-- Salary -->
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Minimum Salary <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="min_salary" placeholder="500" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Maximum Salary <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="max_salary" placeholder="1000" required>
          </div>
        </div>

        <!-- Description -->
        <div class="mt-3 mb-3">
          <label class="form-label">Job Description <span class="text-danger">*</span></label>
          <textarea type="text" class="form-control" name="job_description" rows="4" placeholder="Describe the role..." required></textarea>
        </div>

        <!-- Responsibilities -->
        <div class="mb-3">
          <label class="form-label">Responsibilities <span class="text-danger">*</span></label>
          <textarea class="form-control" name="job_responsibilities" rows="3" placeholder="List the key responsibilities..." required></textarea>
        </div>

        <!-- Qualifications -->
        <div class="mb-4">
          <label class="form-label">Qualifications <span class="text-danger">*</span></label>
          <textarea class="form-control" name="job_qualifications" rows="3" placeholder="List required skills, experience, education..." required></textarea>
        </div>

        <!-- Buttons -->
       <div class="d-flex justify-content-end gap-2">
      <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn">Save as Draft</button>
        <button type="submit" class="btn btn-primary" id="publishBtn">Publish Job</button>
        </div>
      </form>
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
          <h5 class="modal-title" id="resultModalLabel"></h5>
          <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- JS Logic -->
  <script>
  const jobForm = document.getElementById('jobForm');
  const jobStatus = document.getElementById('jobStatus');

  // Save as Draft button
  document.getElementById('saveDraftBtn').addEventListener('click', () => {
    jobStatus.value = 'draft';
    submitJob();
  });

  // Publish button
  document.getElementById('publishBtn').addEventListener('click', (e) => {
    e.preventDefault(); // prevent default submit behavior
    jobStatus.value = 'active';
    submitJob();
  });


  function submitJob() {
  // Check if the form is valid
  if (!jobForm.checkValidity()) {
    jobForm.reportValidity(); // show validation messages
    return; // stop submission
  }

  const formData = new FormData(jobForm);

  fetch(jobForm.action, {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    const modalLabel = document.getElementById('resultModalLabel');
    modalLabel.textContent = data.message;

    const modal = new bootstrap.Modal(document.getElementById('resultModal'));
    modal.show();

    if (data.status === 'success') {
      jobForm.reset();
      jobStatus.value = 'draft'; // reset to default
    }
  })
  .catch(err => {
    console.error(err);
    alert('Something went wrong. Please try again.');
  });
}
  </script>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
