<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container" style="max-width: 400px; margin-top:50px;">
    <h3 class="text-center mb-4">Reset Password</h3>

    <?php
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger text-center">'.htmlspecialchars($_GET['error']).'</div>';
    } elseif (isset($_GET['success'])) {
        echo '<div class="alert alert-success text-center">'.htmlspecialchars($_GET['success']).'</div>';
    }
    ?>

    <form method="POST" action="../config/reset_password_request.php">
        <div class="mb-3">
            <label for="email" class="form-label">Enter your email</label>
            <input type="email" class="form-control" id="email" name="email" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">Next</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
