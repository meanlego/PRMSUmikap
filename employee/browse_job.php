<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

require_once '../database/prmsumikap_db.php';

$studentName = $_SESSION['name'];  
$accountType = ucfirst($_SESSION['role']);

try {
    $stmt = $pdo->prepare("SELECT student_id FROM students_profile WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student_profile) {
        $student_id = 0;
    } else {
        $student_id = $student_profile['student_id'];
    }
} catch(PDOException $e) {
    $student_id = 0;
    error_log("Browse jobs page student profile error: " . $e->getMessage());
}


$searchQuery = "";
$params = [];
$whereConditions = ["j.status = 'Active'"];

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $searchTerm = trim($_GET['query']);
    $whereConditions[] = "(j.job_title LIKE ? OR j.job_description LIKE ? OR j.job_location LIKE ? OR e.company_name LIKE ?)";
    $searchParam = "%" . $searchTerm . "%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (isset($_GET['job_type']) && !empty($_GET['job_type'])) {
    $whereConditions[] = "j.job_type = ?";
    $params[] = $_GET['job_type'];
}


if (isset($_GET['work_arrangement']) && !empty($_GET['work_arrangement'])) {
    $whereConditions[] = "j.work_arrangement = ?";
    $params[] = $_GET['work_arrangement'];
}

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $whereConditions[] = "j.job_location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
}


$whereClause = implode(" AND ", $whereConditions);
$sql = "
    SELECT 
        j.*,
        e.company_name,
        e.contact_person,
        (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.job_id AND a.student_id = ?) as has_applied,
        (SELECT COUNT(*) FROM saved_jobs sj WHERE sj.job_id = j.job_id AND sj.student_id = ?) as is_saved
    FROM jobs j 
    LEFT JOIN employers_profile e ON j.employer_id = e.employer_id 
    WHERE $whereClause
    ORDER BY j.date_posted DESC
";

array_unshift($params, $student_id, $student_id);

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $jobs = [];
    error_log("Database error: " . $e->getMessage());
}


try {
    $locationsStmt = $pdo->query("SELECT DISTINCT job_location FROM jobs WHERE status = 'Active' ORDER BY job_location");
    $locations = $locationsStmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $locations = [];
}


$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Jobs | PRMSUmikap</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

<link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap-rebase/assets/images/favicon.png">

<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
<style>
.job-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    border-left: 4px solid #2575fc;
}
.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.company-logo {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
.salary-range {
    font-weight: 600;
    color: #198754;
}
.filter-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
}
.job-type-badge {
    font-size: 0.75rem;
}
.active-filter {
    background-color: #e7f1ff;
    border-left: 4px solid #0a4da2;
}
.loading-spinner {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
}
</style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

<div class="loading-spinner" id="loadingSpinner">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div id="main-content">
    <div class="welcome-card mb-4">
        <h1 class="display-5 fw-bold mb-3">Find your part-time job</h1>
        <p class="fs-5 mb-4">Discover opportunities from local businesses in your area</p>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="GET" id="searchForm" class="d-flex flex-column flex-md-row gap-3">
            <div class="input-group shadow-sm rounded-pill overflow-hidden flex-grow-1">
                <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                <input type="text" name="query" class="form-control border-0" 
                       placeholder="Search jobs by title, company, or location" 
                       value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                       onkeyup="debounceSearch()" id="searchInput">
            </div>
            <button type="submit" class="btn text-white rounded-pill px-4 py-2 fw-bold mt-2 mt-md-0" style="background: #0a4da2;">Search</button>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-lg-3">
            <div class="filter-section shadow-sm <?php echo (isset($_GET['job_type']) || isset($_GET['work_arrangement']) || isset($_GET['location'])) ? 'active-filter' : ''; ?>">
                <h5 class="fw-bold mb-3"><i class="bi bi-funnel me-2"></i>Filters</h5>
                
                <form method="GET" id="filterForm">

                    <input type="hidden" name="query" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" id="filterQuery">

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Job Type</label>
                        <select name="job_type" class="form-select" onchange="showLoading(); document.getElementById('filterForm').submit()">
                            <option value="">All Types</option>
                            <option value="Full-time" <?php echo (isset($_GET['job_type']) && $_GET['job_type'] == 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo (isset($_GET['job_type']) && $_GET['job_type'] == 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Contract" <?php echo (isset($_GET['job_type']) && $_GET['job_type'] == 'Contract') ? 'selected' : ''; ?>>Contract</option>
                            <option value="Internship" <?php echo (isset($_GET['job_type']) && $_GET['job_type'] == 'Internship') ? 'selected' : ''; ?>>Internship</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Work Arrangement</label>
                        <select name="work_arrangement" class="form-select" onchange="showLoading(); document.getElementById('filterForm').submit()">
                            <option value="">All Arrangements</option>
                            <option value="On-site" <?php echo (isset($_GET['work_arrangement']) && $_GET['work_arrangement'] == 'On-site') ? 'selected' : ''; ?>>On-site</option>
                            <option value="Hybrid" <?php echo (isset($_GET['work_arrangement']) && $_GET['work_arrangement'] == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                            <option value="Remote" <?php echo (isset($_GET['work_arrangement']) && $_GET['work_arrangement'] == 'Remote') ? 'selected' : ''; ?>>Remote</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Location</label>
                        <select name="location" class="form-select" onchange="showLoading(); document.getElementById('filterForm').submit()">
                            <option value="">All Locations</option>
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location); ?>" 
                                    <?php echo (isset($_GET['location']) && $_GET['location'] == $location) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if(isset($_GET['job_type']) || isset($_GET['work_arrangement']) || isset($_GET['location'])): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-2">Active Filters:</h6>
                            <div class="d-flex flex-column gap-1">
                                <?php if(isset($_GET['job_type'])): ?>
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-primary me-2 flex-shrink-0">Job</span>
                                        <small class="text-muted text-break"><?php echo htmlspecialchars($_GET['job_type']); ?></small>
                                    </div>
                                <?php endif; ?>
                                <?php if(isset($_GET['work_arrangement'])): ?>
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-info me-2 flex-shrink-0">Work</span>
                                        <small class="text-muted text-break"><?php echo htmlspecialchars($_GET['work_arrangement']); ?></small>
                                    </div>
                                <?php endif; ?>
                                <?php if(isset($_GET['location'])): ?>
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-success me-2 flex-shrink-0">Location</span>
                                        <small class="text-muted text-break"><?php echo htmlspecialchars($_GET['location']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['query']) || isset($_GET['job_type']) || isset($_GET['work_arrangement']) || isset($_GET['location'])): ?>
                        <a href="browse_job.php" class="btn btn-outline-secondary w-100" onclick="showLoading()">Clear All Filters</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">
                    <?php 
                    $totalJobs = count($jobs);
                    echo $totalJobs . ' job' . ($totalJobs != 1 ? 's' : '') . ' found';
                    if (isset($_GET['query']) && !empty($_GET['query'])) {
                        echo ' for "' . htmlspecialchars($_GET['query']) . '"';
                    }
                    ?>
                </h5>
                <div class="text-muted small">
                    Sorted by: Newest first
                </div>
            </div>

            <?php if (!empty($jobs)): ?>
                <?php foreach($jobs as $job): ?>
                <div class="job-card card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="company-logo bg-light d-flex align-items-center justify-content-center">
                                        <i class="bi bi-building text-muted fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h4>
                                        <p class="text-muted mb-1 fs-5"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($job['job_location']); ?>
                                            </span>
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($job['job_type']); ?>
                                            </span>
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-laptop me-1"></i><?php echo htmlspecialchars($job['work_arrangement']); ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-2"><?php echo substr(strip_tags($job['job_description']), 0, 150); ?>...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column h-100">
                                    <div class="mb-3">
                                        <span class="salary-range fs-5">
                                            ₱<?php echo number_format($job['min_salary']); ?> - ₱<?php echo number_format($job['max_salary']); ?>
                                        </span>
                                        <small class="text-muted d-block">per month</small>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            <?php if($job['has_applied']): ?>
                                                <button class="btn btn-success" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>Applied
                                                </button>
                                            <?php else: ?>
                                                <a href="apply_job.php?id=<?php echo $job['job_id']; ?>" 
                                                   class="btn btn-primary">
                                                    <i class="bi bi-send me-1"></i>Apply Now
                                                </a>
                                            <?php endif; ?>
                                            
<form method="POST" action="../employee/save_job_process.php" class="d-inline w-100">
    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
    <input type="hidden" name="redirect_url" value="<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
    <?php if($job['is_saved']): ?>
        <button type="submit" name="action" value="unsave" class="btn btn-outline-danger w-100">
            <i class="bi bi-bookmark-check-fill me-1"></i>Saved
        </button>
    <?php else: ?>
        <button type="submit" name="action" value="save" class="btn btn-outline-primary w-100">
            <i class="bi bi-bookmark me-1"></i>Save Job
        </button>
    <?php endif; ?>
</form>
                                            
                                <a href="view_details.php?id=<?php echo $job['job_id']; ?>" 
                                               class="btn btn-outline-secondary">
                                                <i class="bi bi-eye me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-top pt-3 mt-3">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>Posted on <?php echo date('M j, Y', strtotime($job['date_posted'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center p-5 bg-white rounded-3 shadow-sm">
                    <i class="bi bi-briefcase display-4 text-muted"></i>
                    <h5 class="mt-3 text-muted">No jobs found</h5>
                    <p class="text-muted mb-4">
                        <?php if(isset($_GET['query']) || isset($_GET['job_type']) || isset($_GET['work_arrangement']) || isset($_GET['location'])): ?>
                            Try adjusting your filters or search terms.
                        <?php else: ?>
                            No active job postings available yet. Check back later!
                        <?php endif; ?>
                    </p>
                    <?php if(isset($_GET['query']) || isset($_GET['job_type']) || isset($_GET['work_arrangement']) || isset($_GET['location'])): ?>
                        <a href="browse_job.php" class="btn btn-primary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Debounce function to prevent too many requests
let searchTimeout;
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        showLoading();
        document.getElementById('searchForm').submit();
    }, 800); // Wait 800ms after user stops typing
}

// Show loading spinner
function showLoading() {
    document.getElementById('loadingSpinner').style.display = 'block';
}

// Hide loading spinner when page loads
window.addEventListener('load', function() {
    document.getElementById('loadingSpinner').style.display = 'none';
});

// Also submit when search input loses focus
document.getElementById('searchInput').addEventListener('blur', function() {
    if (this.value.trim() !== '') {
        showLoading();
        document.getElementById('searchForm').submit();
    }
});

// Sync search query between forms
document.getElementById('searchInput').addEventListener('input', function() {
    document.getElementById('filterQuery').value = this.value;
});
</script>
</body>
</html>