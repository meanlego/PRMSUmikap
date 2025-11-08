<?php
session_start();

// Redirect if not logged in or not an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

include __DIR__ . '/../database/prmsumikap_db.php';

// Get employer_id linked to this user
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$employerProfile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employerProfile) {
    die("Employer profile not found. Please complete your employer profile.");
}

$employer_id = $employerProfile['employer_id'];

// Fetch all applications for this employer's jobs
$stmt = $pdo->prepare("
    SELECT 
        applications.application_id,
        users.name,
        users.email,
        jobs.job_title,
        applications.status,
        applications.date_applied
    FROM applications
    JOIN users ON applications.student_id = users.user_id
    JOIN jobs ON applications.job_id = jobs.job_id
    WHERE jobs.employer_id = ?
    ORDER BY applications.date_applied DESC
");
$stmt->execute([$employer_id]);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalApplications = count($applicants);

// Get unique job titles for filter dropdown
$jobsStmt = $pdo->prepare("SELECT DISTINCT job_title FROM jobs WHERE employer_id = ? ORDER BY job_title");
$jobsStmt->execute([$employer_id]);
$jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applicants | PRMSUmikap</title>

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

<!-- Favicon -->
<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap/assets/images/favicon.png">

<!-- Custom CSS -->
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div id="main-content" class="container my-5">

  <!-- Header -->
  <div class="welcome-card mb-4">
      <h1 class="display-5 fw-bold mt-2">All Applicants</h1>
      <p class="fs-5">View and manage all applications across your job postings</p>

      <div class="mt-4">
          <div class="bg-white bg-opacity-25 rounded-4 px-4 py-3 d-inline-block text-center">
              <h6 class="fw-semibold text-white mb-1">Total Applications</h6>
              <h3 class="text-white fw-bold mb-0"><?= $totalApplications ?></h3>
          </div>
      </div>
  </div>

  <!-- Filter Section -->
  <div class="card mb-4 border-0 shadow-sm">
      <div class="card-body">
          <div class="row g-3 align-items-center">
              <div class="col-md-4">
                  <label class="form-label fw-semibold">Search Applicants</label>
                  <input type="text" id="searchInput" class="form-control" placeholder="Name or email...">
              </div>
              <div class="col-md-4">
                  <label class="form-label fw-semibold">Filter by Status</label>
                  <select class="form-select" id="statusFilter">
                      <option value="">All Statuses</option>
                      <option value="Pending">Pending</option>
                      <option value="Accepted">Accepted</option>
                      <option value="Rejected">Rejected</option>
                  </select>
              </div>
              <div class="col-md-4">
                  <label class="form-label fw-semibold">Filter by Job</label>
                  <select class="form-select" id="jobFilter">
                      <option value="">All Jobs</option>
                      <?php foreach ($jobs as $job): ?>
                          <option value="<?= htmlspecialchars($job['job_title']) ?>">
                              <?= htmlspecialchars($job['job_title']) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>
          </div>
      </div>
  </div>

  <!-- Applications Table -->
  <?php if ($totalApplications > 0): ?>
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0" id="applicantsTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Applicant Name</th>
            <th>Email</th>
            <th>Job Title</th>
            <th>Status</th>
            <th>Date Applied</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applicants as $index => $row): ?>
            <tr class="applicant-row" 
                data-name="<?= htmlspecialchars(strtolower($row['name'])) ?>"
                data-email="<?= htmlspecialchars(strtolower($row['email'])) ?>"
                data-job="<?= htmlspecialchars($row['job_title']) ?>"
                data-status="<?= htmlspecialchars($row['status']) ?>">
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['job_title']) ?></td>
              <td>
                <span class="badge bg-<?= 
                  $row['status'] === 'Accepted' ? 'success' : 
                  ($row['status'] === 'Rejected' ? 'danger' : 'warning') ?>">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
              <td><?= date('M d, Y', strtotime($row['date_applied'])) ?></td>
              <td>
                <a href="./view_application.php?id=<?= $row['application_id'] ?>" 
                   class="btn btn-sm btn-outline-primary">
                   <i class="bi bi-eye"></i> View
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
  <div class="card text-center p-5">
    <div class="card-body">
      <i class="bi bi-person fs-1 text-secondary mb-3"></i>
      <h4 class="fw-semibold">No applications found</h4>
      <p class="text-muted mb-0">Applications will appear here as candidates apply for your positions.</p>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const jobFilter = document.getElementById('jobFilter');
    const rows = document.querySelectorAll('.applicant-row');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const jobValue = jobFilter.value;

        let visibleIndex = 1;

        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const job = row.dataset.job;
            const status = row.dataset.status;

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = !statusValue || status === statusValue;
            const matchesJob = !jobValue || job === jobValue;

            if (matchesSearch && matchesStatus && matchesJob) {
                row.style.display = '';
                row.querySelector('td:first-child').textContent = visibleIndex++;
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    jobFilter.addEventListener('change', filterTable);
});
</script>

</body>
</html>
