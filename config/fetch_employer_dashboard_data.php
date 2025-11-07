<?php
// Make sure session and $pdo are ready
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized access');
}

// 🩵 NEW: map the logged-in user_id to the actual employer_id from employers_profile
$stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$employer_id = $stmt->fetchColumn();

if (!$employer_id) {
    exit('Employer profile not found for this user.');
}

try {
    // 1️⃣ Active Jobs
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM jobs 
        WHERE employer_id = ? AND status = 'Active'
    ");
    $stmt->execute([$employer_id]);
    $activeJobs = $stmt->fetchColumn() ?: 0;

    // 2️⃣ New Applications This Week
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM applications 
        INNER JOIN jobs ON applications.job_id = jobs.job_id
        WHERE jobs.employer_id = ? 
        AND WEEK(applications.date_applied) = WEEK(CURDATE())
        AND YEAR(applications.date_applied) = YEAR(CURDATE())
    ");
    $stmt->execute([$employer_id]);
    $newApplications = $stmt->fetchColumn() ?: 0;

    // 3️⃣ Shortlisted Applicants
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM applications 
        INNER JOIN jobs ON applications.job_id = jobs.job_id
        WHERE jobs.employer_id = ? 
        AND applications.status = 'Shortlisted'
    ");
    $stmt->execute([$employer_id]);
    $shortListed = $stmt->fetchColumn() ?: 0;

    // 4️⃣ Recent Posted Jobs
    $stmt = $pdo->prepare("
        SELECT job_title, date_posted, status
        FROM jobs
        WHERE employer_id = ?
        ORDER BY date_posted DESC
        LIMIT 3
    ");
    $stmt->execute([$employer_id]);
    $recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5️⃣ Recent Applicants
    $stmt = $pdo->prepare("
        SELECT users.name, jobs.job_title, applications.status, applications.date_applied
        FROM applications
        INNER JOIN jobs ON applications.job_id = jobs.job_id
        INNER JOIN users ON applications.user_id = users.user_id
        WHERE jobs.employer_id = ?
        ORDER BY applications.date_applied DESC
        LIMIT 3
    ");
    $stmt->execute([$employer_id]);
    $recentApplicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $activeJobs = $newApplications = $shortListed = 0;
    $recentJobs = $recentApplicants = [];
    error_log("Dashboard data fetch error: " . $e->getMessage());
}
?>
