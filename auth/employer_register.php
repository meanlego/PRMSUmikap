<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../database/prmsumikap_db.php'; ?>

<div class="container mt-3" style="max-width: 500px;">
  <div class="card shadow border-0 px-3">
    <div class="card-body">
    <h3 class="text-center mt-3 mb-4 text-primary fw-bold d-flex align-items-center justify-content-center">
      <i class="bi bi-building-fill me-2 fs-3"></i>
      Employer Registration
    </h3>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
      <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
      <?php endif; ?>

      <form action="../config/employer_register_process.php" method="POST">
        
        <input type="hidden" name="role" value="employer">
        
        <div class="mb-3">
          <label for="company_name" class="form-label fw-semibold">Company Name</label>
          <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Enter company name" required>
        </div>
        
        <div class="mb-3">
          <label for="name" class="form-label fw-semibold">Contact Person Name</label>
          <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Email</label>
          <input type="email" name="email" id="email" class="form-control" placeholder="Enter company email" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>

        <div class="mb-3">
          <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">Register as Employer</button>

        <p class="text-center mt-3 text-secondary">
          Already have an account? <a href="../auth/login.php" class="text-primary fw-semibold">Login here</a>
        </p>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>