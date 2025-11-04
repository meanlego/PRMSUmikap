<?php
session_start();

// // Check if the user is logged in
// if (!isset($_SESSION['employer_name'])) {
//     header("Location: login.php");
//     exit();
// }

// // You can fetch more details from the database if needed
// $employerName = $_SESSION['employer_name']; 
// $accountType = $_SESSION['account_type'] ?? 'Employer Account'; 
// ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Portal Dashboard | PRMSUmikap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f7f9fc;
        }
        #sidebar {
            width: 280px;
            min-height: 100vh;
            background-color: white;
            padding: 0;
        }
        #main-content {
            padding: 30px;
        }
        .welcome-card {
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
        }
        .stat-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            min-height: 150px;
        }
        .menu-item.active {
            color: #6a11cb;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <div id="sidebar" class="d-flex flex-column shadow-sm">
        <div class="p-3 border-bottom d-flex align-items-center">
            <i class="bi bi-briefcase-fill fs-4 me-2" style="color: #6a11cb;"></i>
            <span class="fs-5 fw-bold">PRMSUmikap</span>
        </div>

        <div class="p-3">
            <small class="text-uppercase text-muted fw-bold">Main Menu</small>
            <ul class="nav flex-column mt-2">
                <li class="nav-item">
                    <a class="nav-link text-dark menu-item active" href="#">
                        <i class="bi bi-grid-fill me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark menu-item" href="post_job.php">
                        <i class="bi bi-plus-square-fill me-2"></i> Post a Job
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark menu-item" href="manage_job.php">
                        <i class="bi bi-clipboard-check-fill me-2"></i> My Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark menu-item" href="view_applicant.php">
                        <i class="bi bi-people-fill me-2"></i> Applicants
                    </a>
                </li>
            </ul>
        </div>

        <div class="mt-auto p-3 border-top">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white text-center d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(substr($employerName, 0, 1)); ?>
                </div>
                <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($employerName); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($accountType); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div id="main-content" class="flex-grow-1">
        <div class="welcome-card mb-4">
            <div class="p-3">
                <small class="text-white opacity-75">✨ Welcome Back</small>
                <h1 class="display-5 fw-bold mt-2">Hello, <?php echo htmlspecialchars($employerName); ?>! 👋</h1>
                <p class="fs-5">Ready to find your next opportunity?</p>
                <div class="d-flex mt-4">
                    <button class="btn btn-light rounded-pill me-3 px-4 py-2 fw-bold text-primary">
                        <i class="bi bi-search me-2"></i> Browse Jobs
                    </button>
                    <button class="btn btn-light rounded-pill px-4 py-2 opacity-50"></button>
                </div>
            </div>
        </div>

        <!-- STAT CARDS -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card p-3 stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title text-muted">Total Applications</h5>
                            <i class="bi bi-file-earmark-text-fill fs-3 text-info"></i>
                        </div>
                        <h2 class="display-4 fw-bold mt-2">0</h2>
                        <p class="card-text text-muted">0 active</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title text-muted">Job Offers</h5>
                            <i class="bi bi-graph-up fs-3 text-success"></i>
                        </div>
                        <h2 class="display-4 fw-bold mt-2">0</h2>
                        <p class="card-text text-success">Congratulations! 🎉</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title text-muted">Saved Jobs</h5>
                            <i class="bi bi-bookmark-fill fs-3 text-danger"></i>
                        </div>
                        <h2 class="display-4 fw-bold mt-2">0</h2>
                        <p class="card-text text-muted">Ready to apply</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT APPLICATIONS -->
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold"><i class="bi bi-clock-history me-2"></i> Recent Applications</h4>
                    <a href="#" class="text-decoration-none fw-bold">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="text-center p-5 bg-white rounded-3">
                    <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                    <p class="mt-3 text-muted">No new applications yet.</p>
                </div>
            </div>

            <div class="col-md-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-star-fill me-2"></i> For You</h4>
                <div class="text-center p-4 bg-white rounded-3">
                    <button class="btn btn-primary w-100 py-3 fw-bold" style="background-color: #6a11cb; border-color: #6a11cb;">
                        View All Jobs <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
