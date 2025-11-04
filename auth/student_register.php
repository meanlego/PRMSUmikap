<?php 
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../database/prmsumikap_db.php';

// Determine role from URL, default to student
$role = $_GET['role'] ?? 'student';
?>

<div class="container mt-5" style="max-width: 500px;">
  <div class="card shadow border-0">
    <div class="card-body">
      <h3 class="text-center mb-4 text-primary fw-bold">Student Registration</h3>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
      <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
      <?php endif; ?>

      <form action="../config/student_register_process.php" method="POST">

        <input type="hidden" name="role" value="student">

        <div class="mb-3">
          <label for="name" class="form-label fw-semibold">Name</label>
          <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" required>
        </div>

        <div class="mb-3">
          <label for="student_number" class="form-label fw-semibold">Student Number</label>
          <input type="text" name="student_number" id="student_number" class="form-control" placeholder="Enter your student number" required>
        </div>

        <div class="mb-3">
          <label for="course" class="form-label fw-semibold">Course</label>
          <input type="text" name="course" id="course" class="form-control" placeholder="Enter your course" required>
        </div>

        <div class="mb-3">
          <label for="year_level" class="form-label fw-semibold">Year Level</label>
          <input type="text" name="year_level" id="year_level" class="form-control" placeholder="e.g., 3rd Year" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Email</label>
          <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>

        <div class="mb-3">
          <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">Register</button>

        <p class="text-center mt-3 text-secondary">
          Already have an account? <a href="../auth/login.php" class="text-primary fw-semibold">Login here</a>
        </p>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
