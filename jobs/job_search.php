<?php 
include __DIR__ . '/../includes/header.php'; 
include __DIR__ . '/../database/prmsumikap_db.php';


$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$job_type = isset($_GET['job_type']) ? $_GET['job_type'] : '';
$work_arrangement = isset($_GET['work_arrangement']) ? $_GET['work_arrangement'] : '';
?>

<div class="container my-5">
  <div class="bg-white p-5 rounded-4 shadow-sm">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="fw-bold text-primary mb-2">Find Your Dream Job</h1>
        <p class="text-muted mb-0">Browse through our latest job opportunities</p>
      </div>
    </div>


    <div class="card border-0 bg-light mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
 
      <div class="col-md-3">
        <label class="form-label fw-semibold small">Job Type</label>
        <select name="job_type" class="form-select">
          <option value="">All Types</option>
          <option value="Full-time" <?= $job_type === 'Full-time' ? 'selected' : '' ?>>Full-time</option>
          <option value="Part-time" <?= $job_type === 'Part-time' ? 'selected' : '' ?>>Part-time</option>
          <option value="Contract" <?= $job_type === 'Contract' ? 'selected' : '' ?>>Contract</option>
          <option value="Internship" <?= $job_type === 'Internship' ? 'selected' : '' ?>>Internship</option>
        </select>
      </div>

 
      <div class="col-md-3">
        <label class="form-label fw-semibold small">Work Setup</label>
        <select name="work_arrangement" class="form-select">
          <option value="">All Arrangements</option>
          <option value="On-site" <?= $work_arrangement === 'On-site' ? 'selected' : '' ?>>On-site</option>
          <option value="Remote" <?= $work_arrangement === 'Remote' ? 'selected' : '' ?>>Remote</option>
          <option value="Hybrid" <?= $work_arrangement === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
        </select>
      </div>


      <div class="col-md-4">
        <label class="form-label fw-semibold small">Search Jobs</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" name="query" class="form-control border-start-0"
                 placeholder="Job title, keywords, or company..."
                 value="<?= htmlspecialchars($query) ?>">
        </div>
      </div>

 
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search me-2"></i>Search
        </button>
      </div>

    </form>
  </div>
</div>

    <?php

    $sql = "SELECT * FROM jobs WHERE status='Active'";
    $params = [];

    if ($query) {
        $sql .= " AND (job_title LIKE ? OR job_description LIKE ? OR job_location LIKE ?)";
        $search = "%$query%";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    if ($job_type) {
        $sql .= " AND job_type = ?";
        $params[] = $job_type;
    }

    if ($work_arrangement) {
        $sql .= " AND work_arrangement = ?";
        $params[] = $work_arrangement;
    }

    $sql .= " ORDER BY date_posted DESC";

   
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalJobs = count($jobs);
    ?>

  
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">
        <?php if ($totalJobs > 0): ?>
          <span class="text-primary fw-bold"><?= $totalJobs ?></span> 
          Job<?= $totalJobs !== 1 ? 's' : '' ?> Found
        <?php endif; ?>
      </h5>
    </div>

    <?php if ($totalJobs > 0): ?>
      <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($jobs as $job): ?>
          <div class="col">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition p-4">
              <h5 class="fw-bold text-primary"><?= htmlspecialchars($job['job_title']) ?></h5>
              <p class="text-muted mb-1"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['job_location']) ?></p>
              <span class="badge bg-success bg-opacity-10 text-success mb-2">
                <i class="bi bi-briefcase me-1"></i><?= htmlspecialchars($job['job_type']) ?>
              </span>
              <span class="badge bg-info bg-opacity-10 text-info mb-2">
                <i class="bi bi-laptop me-1"></i><?= htmlspecialchars($job['work_arrangement']) ?>
              </span>

              <?php if ($job['min_salary'] || $job['max_salary']): ?>
                <p class="text-success fw-semibold">
                  ₱<?= !empty($job['min_salary']) ? number_format($job['min_salary']) : '0' ?> 
                  <?= !empty($job['max_salary']) ? '- ₱' . number_format($job['max_salary']) : '' ?>
                </p>
              <?php endif; ?>

              <p class="text-muted"><?= nl2br(htmlspecialchars(substr($job['job_description'], 0, 150))) ?><?= strlen($job['job_description']) > 150 ? '...' : '' ?></p>

              <a href="../auth/login.php" class="btn btn-primary w-100">
                <i class="bi bi-send me-2"></i>Apply
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <h4 class="fw-semibold mb-2">No Jobs Found</h4>
        <p class="text-muted">No active job postings match your search.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.hover-shadow { transition: all 0.3s ease; }
.hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); }
.transition { transition: all 0.3s ease; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
