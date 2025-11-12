<?php include 'includes/header.php'; ?>
<div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link text-secondary" href="welcome_page.php">Home</a></li>
          <li class="nav-item"><a class="nav-link text-secondary" href="pages/job_listing.php">Jobs</a></li>
          <li class="nav-item"><a class="nav-link text-secondary" href="pages/about.php">About</a></li>
          <li class="nav-item">
            <a href="auth/login.php" class="btn btn-outline-primary me-2 fw-semibold">Login</a>
          </li>
        </ul>
      </div>

    <div class="card shadow-sm border-0 text-center mb-5 py-5 d-flex flex-column justify-content-center">
      <h1 class="fw-bold text-primary">Welcome to PRMSUmikap</h1>

      <img src="assets/images/student.png" alt="PRMSU Students" class="d-block mx-auto my-3" style="object-fit:contain;">

      <p class="mt-3 text-secondary fs-5">
        A web-based platform for PRMSU students to find part-time jobs that fit their skills and academic schedules.
      </p>

 
  <div class="d-flex justify-content-center mt-4 mb-5">
    <div class="d-flex w-75 justify-content-center" style="max-width: 600px; gap: 10px;">
      <input id="searchInput" type="text" class="form-control border-primary rounded px-3" 
            placeholder="Search for jobs or locations..." aria-label="Search">
      <button id="searchBtn" class="btn btn-primary fw-semibold rounded px-4" type="button">Search</button>
    </div>
  </div>


  <div id="jobContainer" class="d-flex flex-column align-items-center gap-3"></div>

      <div class="mt-3">
        <a href="auth/student_register.php" class="btn btn-primary fw-semibold me-2">I'm a Student</a>
        <a href="auth/employer_register.php" class="btn btn-outline-primary fw-semibold">I'm an Employer</a>
      </div>
    </div>


  <div class="row g-4 justify-content-center">
    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <i class="bi bi-mortarboard-fill text-primary fs-1 mb-3"></i>
          <h5 class="card-title text-primary fw-semibold">Student Opportunities</h5>
          <p class="card-text text-secondary">Find part-time jobs that match your academic schedule and interests.</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <i class="bi bi-briefcase-fill text-primary fs-1 mb-3"></i>
          <h5 class="card-title text-primary fw-semibold">Employer Access</h5>
          <p class="card-text text-secondary">Post openings, review applications, and hire qualified PRMSU students.</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <i class="bi bi-shield-lock-fill text-primary fs-1 mb-3"></i>
          <h5 class="card-title text-primary fw-semibold">Secure & Reliable</h5>
          <p class="card-text text-secondary">A university-verified platform designed for trustworthy student employment.</p>
        </div>
      </div>
    </div>
  </div>

  <h3 class="fw-bold text-secondary text-center mt-5">Get Started in 3 Easy Steps</h3>

<div class="row g-4 justify-content-center mt-3">
  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body text-center">
        <i class="bi bi-person-plus-fill text-dark fs-1 mb-3"></i>
        <h6 class="card-title fw-semibold">1. Sign Up</h6>
        <p class="card-text text-secondary">Create an account as a student or employer.</p>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body text-center">
        <i class="bi bi-people-fill text-dark fs-1 mb-3"></i>
        <h6 class="card-title fw-semibold">2. Connect</h6>
        <p class="card-text text-secondary">Students browse jobs while employers post openings.</p>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body text-center">
        <i class="bi bi-briefcase-fill text-dark fs-1 mb-3"></i>
        <h6 class="card-title fw-semibold">3. Get Hired</h6>
        <p class="card-text text-secondary">Start working, gain experience, and grow your career.</p>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  const searchInput = document.getElementById('searchInput');
  const searchBtn = document.getElementById('searchBtn');
  const jobContainer = document.getElementById('jobContainer');


  function performSearch() {
    const query = searchInput.value.trim();


    if (query === '') {
      jobContainer.innerHTML = ''; 
      return;
    }


    jobContainer.innerHTML = '<p class="text-muted">Loading...</p>';

    fetch('jobs/fetch_jobs.php?search=' + encodeURIComponent(query))
      .then(response => {
        if (!response.ok) throw new Error('Network response was not OK');
        return response.text();
      })
      .then(html => {
        jobContainer.innerHTML = html;
      })
      .catch(error => {
        console.error(error);
        jobContainer.innerHTML = '<p class="text-danger">Error loading jobs. Check console/network.</p>';
      });
  }

  searchBtn.addEventListener('click', performSearch);


  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      performSearch();
    }
  });


})();
</script>

<?php include 'includes/footer.php'; ?>
