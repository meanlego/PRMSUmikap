
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../database/prmsumikap_db.php'; ?> <!-- ✅ fixed filename -->

<div class="d-flex justify-content-center align-items-center bg-light" style="min-height: calc(100vh - 150px);">
  <div class="card shadow border-0 p-4" style="width: 400px; border-radius: 15px;">
    <h2 class="text-center mb-4 text-primary fw-bold">LOGIN</h2>

    <?php
    // ✅ Display messages
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($_GET['error']) . '</div>';
    } elseif (isset($_GET['success'])) {
        echo '<div class="alert alert-success text-center">' . htmlspecialchars($_GET['success']) . '</div>';
    }
    ?>

    <!-- ✅ fixed action path -->
    <form method="POST" action="../config/login_process.php">
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
        <a href="#" class="text-decoration-none small text-primary">Forgot your password?</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">Log In</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>