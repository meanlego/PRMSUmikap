<?php 
include __DIR__ . '/../includes/header.php'; 
include __DIR__ . '/../database/prmsumikap_db.php'; 

if (!isset($_GET['id'])) {
  echo '<div class="container my-5"><div class="alert alert-danger">Invalid job ID.</div></div>';
  include __DIR__ . '/../includes/footer.php';
  exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM tbl_jobs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo '<div class="container my-5"><div class="alert alert-warning">Job not found.</div></div>';
  include __DIR__ . '/../includes/footer.php';
  exit;
}

$job = $result->fetch_assoc();
?>

<div class="container my-5">
  <div class="bg-white p-5 rounded-4 shadow-sm">
    <h1 class="fw-bold text-primary mb-3"><?= htmlspecialchars($job['job_title']) ?></h1>
    <p class="text-muted"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($job['job_location']) ?></p>
    <p class="fw-semibold mb-1">Posted by: <?= htmlspecialchars($job['employer_name']) ?></p>
    <hr>
    <p><?= nl2br(htmlspecialchars($job['job_description'])) ?></p>
    <hr>
    <p class="text-muted small">Date Posted: <?= htmlspecialchars($job['date_posted']) ?></p>
    <a href="job_search.php" class="btn btn-outline-secondary mt-3">← Back to Jobs</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
