<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';
include __DIR__ . '/../functions/auth_functions.php';


if (!empty($_SESSION['user_id'])) {
    removeRememberMeCookie($pdo, $_SESSION['user_id']);
}


$_SESSION = [];
session_destroy();


header("Location: ../auth/login.php?success=" . urlencode("Logged out successfully."));
exit;
?>
