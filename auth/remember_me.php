<?php
session_start();
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../database/prmsumikap_db.php';
?>

<div class="d-flex justify-content-center align-items-center bg-light" style="min-height: calc(100vh - 150px);">
  <div class="card shadow border-0 p-4" style="width: 400px; border-radius: 15px;">
    <h2 class="text-center mb-4 text-primary fw-bold d-flex align-items-center justify-content-center">
      <i class="bi bi-person-circle me-2 fs-3"></i>
      LOGIN
    </h2>

    <?php
    if (!empty($_GET['error'])) {
        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($_GET['error']) . '</div>';
    } elseif (!empty($_GET['success'])) {
        echo '<div class="alert alert-success text-center">' . htmlspecialchars($_GET['success']) . '</div>';
    }
    ?>

    <form method="POST" action="../config/remember_me_process.php" autocomplete="on">
      <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autofocus>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">Remember me</label>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        <a href="forgot_password.php" class="text-decoration-none small text-primary">Forgot your password?</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">Log In</button>
      </div>

      <div class="text-center mt-3">
        <p class="small text-muted mb-0">
          Don’t have an account yet?
          <a href="student_register.php" class="text-primary text-decoration-underline">Register</a>
        </p>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

