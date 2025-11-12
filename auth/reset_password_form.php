<?php
session_start();
$error = $_SESSION['reset_error'] ?? '';
$success = $_SESSION['reset_success'] ?? '';
$email = $_SESSION['reset_email'] ?? '';

unset($_SESSION['reset_error'], $_SESSION['reset_success']);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-5">
    <div class="card p-4 shadow" style="max-width: 500px; margin:auto;">
        <h3 class="text-center mb-4">Reset Password</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="reset_password_process.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
