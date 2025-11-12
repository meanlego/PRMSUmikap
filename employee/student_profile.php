<?php
include 'studentprofile_auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | PRMSUmikap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" sizes="512x512" href="/prmsumikap/assets/images/favicon.png">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
    .is-invalid { border-color: #dc3545 !important; }
    .is-valid { border-color: #198754 !important; }
    .border-dashed { border: 2px dashed #dee2e6; }
    .cursor-pointer { cursor: pointer; }
    .validation-feedback { 
        font-size: 0.875rem; 
        margin-top: 0.25rem; 
        display: block; 
    }
    .resume-filename {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        margin-top: 0.5rem;
    }
    .year-input { max-width: 120px; }
    .empty-section { 
        background-color: #f8f9fa; 
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    .profile-saved {
        animation: highlight 2s ease-in-out;
    }
    @keyframes highlight {
        0% { background-color: #d1edff; }
        100% { background-color: transparent; }
    }
    </style>
</head>
<body>
<?php include __DIR__. '/../includes/sidebar.php'; ?>

    <div id="main-content" class="p-4">
        <?php include 'studentprofile_form.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>