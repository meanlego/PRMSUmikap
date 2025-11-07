<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

// Check if logged in as employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
  header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
  exit;
}

$user_id = $_SESSION['user_id'];

// Fetch employer ID
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);
$employer_id = $employer['employer_id'] ?? null;

if (!$employer_id) {
  die("Employer record not found.");
}

// Get the job to edit
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
<title>Edit Job | PRMSUmikap</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container bg-white p-5 rounded shadow">
  <h2 class="fw-bold mb-4">Edit Job</h2>

  <form action="../config/edit_job_process.php" method="POST">
    <input type="hidden" name="job_id" value="<?= htmlspecialchars($job['job_id']) ?>">

    <div class="mb-3">
      <label class="form-label">Job Title</label>
      <input type="text" class="form-control" name="job_title" value="<?= htmlspecialchars($job['job_title']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Job Description</label>
      <textarea class="form-control" name="job_description" rows="4" required><?= htmlspecialchars($job['job_description']) ?></textarea>
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Job Type</label>
        <input type="text" class="form-control" name="job_type" value="<?= htmlspecialchars($job['job_type']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Work Arrangement</label>
        <input type="text" class="form-control" name="work_arrangement" value="<?= htmlspecialchars($job['work_arrangement']) ?>">
      </div>
    </div>

    <div class="mt-3 mb-3">
      <label class="form-label">Location</label>
      <input type="text" class="form-control" name="job_location" value="<?= htmlspecialchars($job['job_location']) ?>">
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Min Salary</label>
        <input type="number" class="form-control" name="min_salary" value="<?= htmlspecialchars($job['min_salary']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Max Salary</label>
        <input type="number" class="form-control" name="max_salary" value="<?= htmlspecialchars($job['max_salary']) ?>">
      </div>
    </div>

    <div class="mt-3 mb-3">
      <label class="form-label">Qualifications</label>
      <textarea class="form-control" name="job_qualifications" rows="3"><?= htmlspecialchars($job['job_qualifications']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Responsibilities</label>
      <textarea class="form-control" name="job_responsibilities" rows="3"><?= htmlspecialchars($job['job_responsibilities']) ?></textarea>
    </div>

    <div class="d-flex justify-content-end gap-2">
      <a href="manage_jobs.php" class="btn btn-outline-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Update Job</button>
    </div>
  </form>
</div>
</body>
</html>
