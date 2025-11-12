<?php
include __DIR__ . '/../database/prmsumikap_db.php'; 

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search === '') {
    exit;
}

$query = "
    SELECT job_id, job_title, work_arrangement, job_location, date_posted
    FROM jobs
    WHERE status = 'Active'
    AND (
        job_title LIKE :search OR
        job_description LIKE :search OR
        job_location LIKE :search OR
        job_type LIKE :search OR
        work_arrangement LIKE :search
    )
    ORDER BY date_posted DESC
    LIMIT 50
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':search', "%$search%");
$stmt->execute();

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($jobs) {
    foreach ($jobs as $job) {
        echo '<div class="job-card border rounded p-4 shadow-sm w-75 text-start">'; // text-start = left align
        echo '<h5 class="text-primary mb-2">' . htmlspecialchars($job['job_title']) . '</h5>';
        echo '<p class="mb-1"><strong>Work Arrangement:</strong> ' . htmlspecialchars($job['work_arrangement']) . '</p>';
        echo '<p class="mb-1"><strong>Location:</strong> ' . htmlspecialchars($job['job_location']) . '</p>';
        echo '<small class="text-muted">Posted on: ' . date("F j, Y", strtotime($job['date_posted'])) . '</small>';
        echo '</div>';
    }
} else {
    echo '<p class="text-muted">No jobs found.</p>';
}