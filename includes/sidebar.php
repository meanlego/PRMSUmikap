<?php
if (!isset($_SESSION)) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? '';

$isStudent = $role === 'student';
$isEmployer = $role === 'employer';


$currentPage = basename($_SERVER['PHP_SELF']);


$studentMenu = [
    'dashboard.php' => ['icon' => 'bi-grid', 'label' => 'Dashboard'],
    'browse_job.php' => ['icon' => 'bi-search', 'label' => 'Find Jobs'],
    'job_applications.php' => ['icon' => 'bi-clipboard-check', 'label' => 'My Applications'],
    'saved_jobs.php' => ['icon' => 'bi-people', 'label' => 'Saved Jobs']
];

$employerMenu = [
    'dashboard.php' => ['icon' => 'bi-grid', 'label' => 'Dashboard'],
    'post_job.php' => ['icon' => 'bi-plus-circle', 'label' => 'Post a Job'],
    'manage_jobs.php' => ['icon' => 'bi-briefcase', 'label' => 'Manage Jobs'],
    'applicants.php' => ['icon' => 'bi-people', 'label' => 'Applicants']

];

$menuItems = $isStudent ? $studentMenu : ($isEmployer ? $employerMenu : []);
?>
<div id="sidebar" class="d-flex flex-column p-3">
    <div class="mb-4 pb-3 border-bottom d-flex flex-column">
        <div class="d-flex align-items-center mb-1">
            <img src="/prmsumikap-rebase/assets/images/PRMSUmikapLogo.png" 
                alt="PRMSUmikap Logo" 
                style="width:32px; height:32px; object-fit:contain;" 
                class="me-2">
            <span class="fs-5 fw-bold">PRMSUmikap</span>
        </div>
    </div>

    <small class="text-uppercase text-muted fw-bold">Main Menu</small>
    <ul class="nav flex-column mt-2">
        <?php foreach ($menuItems as $file => $item): ?>
            <li class="nav-item">
                <a class="nav-link text-dark menu-item <?= $currentPage === $file ? 'active' : '' ?>" href="<?= $file ?>">
                    <i class="bi <?= $item['icon'] ?> me-2"></i> <?= $item['label'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="dropdown mt-auto">
        <a href="#" 
           class="d-flex align-items-center text-decoration-none dropdown-toggle py-2 px-2 rounded" 
           id="accountDropdown" 
           role="button" 
           data-bs-toggle="dropdown" 
           aria-expanded="false">
          
          <div class="d-flex align-items-center gap-2 ms-1">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                 style="width: 42px; height: 42px;">
              <?php echo strtoupper(substr($name, 0, 1)); ?>
            </div>
            <div style="line-height: 1.2;">
              <div class="fw-semibold text-dark mb-0"><?php echo htmlspecialchars($name); ?></div>
              <small class="text-muted"><?php echo ucfirst($role); ?></small>
            </div>
          </div>
        </a>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 p-2" 
            aria-labelledby="accountDropdown" 
            style="border-radius: 0.75rem; min-width: 180px;">
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2 rounded px-3 py-2" href="<?php echo $isStudent ? 'student_profile.php' : 'profile.php'; ?>">
              <i class="bi bi-person-fill"></i> My Profile
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2 rounded px-3 py-2 text-danger" href="../auth/logout.php">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </li>
        </ul>
    </div>
</div>
