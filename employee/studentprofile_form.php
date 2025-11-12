<?php if(isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  Profile saved successfully!
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="profileForm">

<!-- Header / Profile Pic -->
<div class="welcome-card mb-4 d-flex align-items-center profile-saved">
    <div class="position-relative me-4">
        <img id="profilePreview" src="<?= htmlspecialchars('../' . ($profile['profile_pic'] ?? 'assets/images/default-pfp.png')) ?>"
             class="bg-white rounded-circle p-2 border" 
             style="width:100px; height:100px; object-fit:cover;">
        <input type="file" id="profilePicInput" name="profile_pic" accept=".jpg,.jpeg,.png" class="d-none">
        <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute bottom-0 end-0 border"
                onclick="document.getElementById('profilePicInput').click();">
            <i class="bi bi-camera"></i>
        </button>
    </div>
    <div>
        <h2 class="fw-bold"><?= htmlspecialchars($studentName) ?></h2>
        <p class="text-muted"><?= htmlspecialchars($studentEmail) ?></p>
    </div>
</div>

<!-- Personal Information -->
<div class="card border-0 shadow-sm mb-4 profile-saved">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-person-vcard me-2"></i>Personal Information</h6>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Birth Date *</label>
        <input type="date" class="form-control" name="birthdate" 
               value="<?= htmlspecialchars($profile['birthdate'] ?? '') ?>" 
               max="<?= date('Y-m-d', strtotime('-16 years')) ?>" 
               data-validate="required">
        <div class="validation-feedback" data-feedback="birthdate"></div>
        <small class="form-text text-muted">Required for age verification. Must be at least 16 years old.</small>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Gender *</label>
        <select class="form-select" name="gender" data-validate="required">
          <option value="">Select Gender</option>
          <option value="Male" <?= ($profile['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= ($profile['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
          <option value="Other" <?= ($profile['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
        <div class="validation-feedback" data-feedback="gender"></div>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Student Type</label>
        <select class="form-select" name="student_type">
            <option value="">Select Type</option>
            <option value="Regular Student" <?= ($profile['student_type'] ?? '') === 'Regular Student' ? 'selected' : '' ?>>Regular Student</option>
            <option value="Irregular Student" <?= ($profile['student_type'] ?? '') === 'Irregular Student' ? 'selected' : '' ?>>Irregular Student</option>
        </select>
        <small class="form-text text-muted">Your current student status</small>
      </div>
    </div>
  </div>
</div>

<!-- Basic Info -->
<div class="card border-0 shadow-sm mb-4 profile-saved">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Professional Information</h6>
    <div class="mb-3">
      <label class="form-label fw-semibold">Professional Headline *</label>
      <input type="text" class="form-control" name="headline" value="<?= htmlspecialchars($profile['headline'] ?? '') ?>" 
             placeholder="e.g., IT Student, Web Developer Intern, Accounting Graduate" data-validate="required">
      <div class="validation-feedback" data-feedback="headline"></div>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Bio/Summary *</label>
      <textarea class="form-control" name="bio" rows="4" placeholder="Tell us about your career goals, skills, and what you're looking for in a job..." 
                data-validate="required,minLength:50"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
      <div class="validation-feedback" data-feedback="bio"></div>
      <small class="form-text text-muted">Minimum 50 characters. Describe your career objectives and strengths.</small>
    </div>
  </div>
</div>

<!-- Contact Info -->
<div class="card border-0 shadow-sm mb-4 profile-saved">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Address Information</h6>
    <div class="mb-3">
      <label class="form-label fw-semibold">Complete Address</label>
      <textarea class="form-control" name="address" rows="2" placeholder="House No., Street, Barangay"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
      <small class="form-text text-muted">Your complete address for potential employers</small>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">City/Municipality *</label>
        <select class="form-select" name="city" data-validate="required">
          <option value="">Select City/Municipality</option>
          <?php foreach($zambalesCities as $city): ?>
            <option value="<?= $city ?>" <?= ($profile['city'] ?? '') === $city ? 'selected' : '' ?>><?= $city ?></option>
          <?php endforeach; ?>
          <option value="Other" <?= !in_array($profile['city'] ?? '', $zambalesCities) && !empty($profile['city']) ? 'selected' : '' ?>>Other</option>
        </select>
        <div class="validation-feedback" data-feedback="city"></div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Province *</label>
        <select class="form-select" name="province" data-validate="required">
          <option value="">Select Province</option>
          <?php foreach($phProvinces as $province): ?>
            <option value="<?= $province ?>" <?= ($profile['province'] ?? '') === $province ? 'selected' : '' ?>><?= $province ?></option>
          <?php endforeach; ?>
        </select>
        <div class="validation-feedback" data-feedback="province"></div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4 profile-saved">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-telephone me-2"></i>Contact Details</h6>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Mobile Number *</label>
        <div class="input-group">
          <span class="input-group-text">+63</span>
          <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" 
                 placeholder="9123456789" data-validate="required,phMobile">
        </div>
        <div class="validation-feedback" data-feedback="phone"></div>
        <small class="form-text text-muted">Philippines mobile number (e.g., 9123456789)</small>
      </div>
    </div>
  </div>
</div>

<!-- Skills -->
<div class="card border-0 shadow-sm mb-4 profile-saved">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-award me-2"></i>Skills & Competencies</h6>
    <div id="skills-container">
      <?php if(empty($skills)): ?>
      <div class="empty-section">
        <i class="bi bi-award display-4 text-muted mb-3"></i>
        <p class="text-muted mb-2">No skills added yet</p>
        <small class="text-muted">Add your first skill to get started</small>
      </div>
      <?php else: ?>
        <?php foreach($skills as $index => $skill): ?>
        <div class="d-flex mb-2 skill-entry">
          <div class="flex-grow-1 me-2">
            <input type="text" name="skills[]" class="form-control" value="<?= htmlspecialchars($skill['skill_name']) ?>" placeholder="e.g., Microsoft Office, Programming, Communication">
          </div>
          <button type="button" class="btn btn-outline-danger btn-remove-skill align-self-start"><i class="bi bi-trash"></i></button>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" id="add-skill" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-plus-lg me-1"></i>Add Skill</button>
    <small class="form-text text-muted d-block mt-1">List your technical and soft skills relevant to your desired job</small>
  </div>
</div>

<!-- Work Experience -->
<div class="card border-0 shadow-sm mb-4 profile-saved">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-briefcase me-2"></i>Work Experience</h6>
    <div id="experience-container">
      <?php if(empty($experience)): ?>
      <div class="empty-section">
        <i class="bi bi-briefcase display-4 text-muted mb-3"></i>
        <p class="text-muted mb-2">No work experience added yet</p>
        <small class="text-muted">Add your first work experience to get started</small>
      </div>
      <?php else: ?>
        <?php foreach($experience as $index => $exp): ?>
        <div class="row g-2 mb-3 experience-entry border-bottom pb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Company/Organization *</label>
            <input type="text" name="experience_company[]" class="form-control" value="<?= htmlspecialchars($exp['company_name']) ?>" placeholder="Company Name" data-validate="required">
            <div class="validation-feedback" data-feedback="company-<?= $index ?>"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Position/Role *</label>
            <input type="text" name="experience_position[]" class="form-control" value="<?= htmlspecialchars($exp['position']) ?>" placeholder="Your Position" data-validate="required">
            <div class="validation-feedback" data-feedback="position-<?= $index ?>"></div>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Start Year *</label>
            <input type="number" name="experience_start[]" class="form-control year-input" value="<?= htmlspecialchars($exp['start_year']) ?>" placeholder="2020" min="2000" max="<?= date('Y') ?>" data-validate="required,year">
            <div class="validation-feedback" data-feedback="start-<?= $index ?>"></div>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">End Year</label>
            <input type="number" name="experience_end[]" class="form-control year-input" value="<?= htmlspecialchars($exp['end_year']) ?>" placeholder="2022" min="2000" max="<?= date('Y') ?>" data-validate="year,endYear">
            <div class="validation-feedback" data-feedback="end-<?= $index ?>"></div>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="experience_description[]" class="form-control" rows="2" placeholder="Describe your responsibilities and achievements"><?= htmlspecialchars($exp['description'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <button type="button" class="btn btn-outline-danger btn-remove-experience mt-2"><i class="bi bi-trash"></i> Remove Experience</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" id="add-experience" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-plus-lg me-1"></i>Add Experience</button>
    <small class="form-text text-muted d-block mt-1">Include internships, part-time jobs, and volunteer work</small>
  </div>
</div>

<!-- Education -->
<div class="card border-0 shadow-sm mb-4 profile-saved">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-mortarboard me-2"></i>Education</h6>
    <div id="education-container">
      <?php if(empty($education)): ?>
      <div class="empty-section">
        <i class="bi bi-mortarboard display-4 text-muted mb-3"></i>
        <p class="text-muted mb-2">No education added yet</p>
        <small class="text-muted">Add your first educational background to get started</small>
      </div>
      <?php else: ?>
        <?php foreach($education as $index => $edu): ?>
        <div class="row g-2 mb-3 education-entry border-bottom pb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">School/University *</label>
            <input type="text" name="education_school[]" class="form-control" value="<?= htmlspecialchars($edu['school_name']) ?>" placeholder="School Name" data-validate="required">
            <div class="validation-feedback" data-feedback="school-<?= $index ?>"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Degree/Course *</label>
            <input type="text" name="education_degree[]" class="form-control" value="<?= htmlspecialchars($edu['degree']) ?>" placeholder="e.g., BS Information Technology" data-validate="required">
            <div class="validation-feedback" data-feedback="degree-<?= $index ?>"></div>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Start Year *</label>
            <input type="number" name="education_start[]" class="form-control year-input" value="<?= htmlspecialchars($edu['start_year']) ?>" placeholder="2018" min="2000" max="<?= date('Y') ?>" data-validate="required,year">
            <div class="validation-feedback" data-feedback="edu-start-<?= $index ?>"></div>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">End Year</label>
            <input type="number" name="education_end[]" class="form-control year-input" value="<?= htmlspecialchars($edu['end_year']) ?>" placeholder="2022" min="2000" max="<?= date('Y') ?>" data-validate="year,eduEndYear">
            <div class="validation-feedback" data-feedback="edu-end-<?= $index ?>"></div>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Honors/Awards</label>
            <input type="text" name="education_honors[]" class="form-control" value="<?= htmlspecialchars($edu['honors'] ?? '') ?>" placeholder="e.g., Cum Laude, Dean's Lister">
          </div>
          <div class="col-12">
            <button type="button" class="btn btn-outline-danger btn-remove-education mt-2"><i class="bi bi-trash"></i> Remove Education</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <button type="button" id="add-education" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-plus-lg me-1"></i>Add Education</button>
    <small class="form-text text-muted d-block mt-1">Include your highest educational attainment</small>
  </div>
</div>

<!-- Resume -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body px-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text me-2"></i>Resume</h6>
    <label class="d-block p-5 border border-dashed rounded text-center cursor-pointer" id="resumeUploadLabel">
      <i class="bi bi-upload display-4 text-muted"></i>
      <p class="mt-2 mb-0">Upload your resume</p>
      <small class="text-muted">PDF, DOC, DOCX up to 10MB</small>
      <input type="file" accept=".pdf,.doc,.docx" class="d-none" name="resume" id="resumeInput">
    </label>
    <?php if(!empty($profile['resume'])): ?>
      <div class="mt-3 p-3 bg-light rounded">
        <i class="bi bi-file-earmark-text me-2"></i>
        <strong>Current resume:</strong> 
        <a href="../uploads/resumes/<?= htmlspecialchars($profile['resume']) ?>" target="_blank" class="ms-2">
          <?= htmlspecialchars($profile['resume']) ?>
        </a>
        <a href="../uploads/resumes/<?= htmlspecialchars($profile['resume']) ?>" download class="btn btn-sm btn-outline-primary ms-2">
          <i class="bi bi-download me-1"></i>Download
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Save Button -->
<div class="d-grid mb-5">
  <button type="submit" class="btn text-white fw-semibold py-3" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
    <i class="bi bi-save me-2"></i>Save Profile
  </button>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === FIX FOR CHECKMARK PERSISTENCE ===
    // Clear validation states immediately when page loads
    setTimeout(() => {
        console.log('Clearing validation states...');
        document.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid');
        });
        document.querySelectorAll('.validation-feedback').forEach(feedback => {
            feedback.textContent = '';
        });
    }, 100);

    // Clear validation when coming from sidebar navigation
    if (performance.navigation.type === 2 || document.referrer.includes(window.location.pathname)) {
        console.log('Sidebar navigation detected - clearing validation');
        document.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid');
        });
    }

    const currentYear = new Date().getFullYear();
    let skillCounter = <?= count($skills) ?>;
    let experienceCounter = <?= count($experience) ?>;
    let educationCounter = <?= count($education) ?>;

    <?php if(isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
    // Highlight sections after successful save
    const sections = document.querySelectorAll('.profile-saved');
    sections.forEach(section => {
        section.style.animation = 'highlight 2s ease-in-out';
    });
    
    setTimeout(() => {
        sections.forEach(section => {
            section.style.animation = '';
        });
    }, 2000);
    <?php endif; ?>
    
    // Validation rules
    const validators = {
        required: (value) => value.trim() !== '' ? null : 'This field is required',
        minLength: (value, min) => value.length >= min ? null : `Minimum ${min} characters required`,
        phMobile: (value) => {
            if (!value.trim()) return 'Mobile number is required';
            const mobileRegex = /^9\d{9}$/;
            return mobileRegex.test(value.replace(/\s+/g, '')) ? null : 'Enter a valid Philippine mobile number (e.g., 9123456789)';
        },
        year: (value) => {
            if (!value.trim()) return null;
            const year = parseInt(value);
            const currentYear = new Date().getFullYear();
            
            if (year < 1950 || year > currentYear) {
                return `Enter a valid year between 1950 and ${currentYear}`;
            }
            
            return null;
        },
        workStartYear: (value) => {
            if (!value.trim()) return null;
            const year = parseInt(value);
            const birthdate = document.querySelector('input[name="birthdate"]');
            
            if (birthdate && birthdate.value) {
                const birthYear = parseInt(birthdate.value.split('-')[0]);
                const age = year - birthYear;
                
                if (age < 14) return `You would have been ${age} years old - too young for work experience`;
                if (age > 70) return `Please verify the year - you would have been ${age} years old`;
            }
            
            return null;
        },
        eduStartYear: (value) => {
            if (!value.trim()) return null;
            const year = parseInt(value);
            const birthdate = document.querySelector('input[name="birthdate"]');
            
            if (birthdate && birthdate.value) {
                const birthYear = parseInt(birthdate.value.split('-')[0]);
                const age = year - birthYear;
                
                if (age < 5) return `You would have been ${age} years old - too young for school`;
                if (age > 25) return `Please verify the year - you would have been ${age} years old`;
            }
            
            return null;
        },
        endYear: (value, field, formData) => {
            if (!value.trim()) return null;
            const startYear = parseInt(formData.startYear || '0');
            const endYear = parseInt(value);
            
            if (endYear < startYear) {
                return 'End year must be greater than or equal to start year';
            }
            
            const currentYear = new Date().getFullYear();
            if (endYear > currentYear) {
                return 'End year cannot be in the future';
            }
            
            const duration = endYear - startYear;
            if (duration > 10) {
                return `Long duration (${duration} years) - please verify years are correct`;
            }
            
            return null;
        },
        eduEndYear: (value, field, formData) => {
            if (!value.trim()) return null;
            const startYear = parseInt(formData.eduStartYear || '0');
            const endYear = parseInt(value);
            
            if (endYear < startYear) {
                return 'End year must be greater than or equal to start year';
            }
            
            const currentYear = new Date().getFullYear();
            if (endYear > currentYear) {
                return 'End year cannot be in the future';
            }
            
            const duration = endYear - startYear;
            if (duration > 8) {
                return `Long education duration (${duration} years) - please verify years are correct`;
            }
            
            return null;
        }
    };

    function validateField(field) {
        const value = field.value.trim();
        const validationRules = field.getAttribute('data-validate');
        const feedbackElement = field.parentElement.querySelector('.validation-feedback');
        
        if (!validationRules) return true;

        const rules = validationRules.split(',');
        let isValid = true;
        let errorMessage = '';

        for (const rule of rules) {
            const [ruleName, param] = rule.split(':');
            
            if (validators[ruleName]) {
                const formData = {};
                if (ruleName === 'endYear') {
                    const startField = field.closest('.experience-entry').querySelector('input[name="experience_start[]"]');
                    formData.startYear = startField ? startField.value : '';
                } else if (ruleName === 'eduEndYear') {
                    const startField = field.closest('.education-entry').querySelector('input[name="education_start[]"]');
                    formData.eduStartYear = startField ? startField.value : '';
                }

                const result = validators[ruleName](value, param, formData);
                if (result) {
                    isValid = false;
                    errorMessage = result;
                    break;
                }
            }
        }

        field.classList.remove('is-valid', 'is-invalid');
        if (value === '') {
            if (feedbackElement) feedbackElement.textContent = '';
        } else if (isValid) {
            field.classList.add('is-valid');
            if (feedbackElement) feedbackElement.textContent = '';
        } else {
            field.classList.add('is-invalid');
            if (feedbackElement) {
                feedbackElement.textContent = errorMessage;
                feedbackElement.className = 'validation-feedback text-danger';
            }
        }

        return isValid;
    }

    function attachValidation(field) {
        field.addEventListener('input', function() {
            validateField(this);
        });
        
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        // Only validate if field has content
        if (field.value.trim()) {
            validateField(field);
        }
    }

    // Attach validation to all fields
    document.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach(field => {
        attachValidation(field);
    });

    // Profile picture preview
    document.getElementById('profilePicInput').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Resume upload handler function
    function handleResumeUpload(e) {
        if (e.target.files && e.target.files[0]) {
            const fileName = e.target.files[0].name;
            const resumeLabel = document.getElementById('resumeUploadLabel');
            
            resumeLabel.innerHTML = `
                <i class="bi bi-file-earmark-check display-4 text-success"></i>
                <p class="mt-2 mb-1 fw-semibold">Resume Ready</p>
                <small class="text-muted">Selected file: ${fileName}</small>
                <br><small class="text-muted">Click to change file</small>
                <input type="file" accept=".pdf,.doc,.docx" class="d-none" name="resume" id="resumeInput">
            `;
            
            const resumeFilename = document.createElement('div');
            resumeFilename.className = 'resume-filename mt-3';
            resumeFilename.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-file-earmark-text me-2"></i>
                        <span>New resume: ${fileName}</span>
                    </div>
                    <small class="text-muted">Will replace current file when saved</small>
                </div>
            `;
            
            const existingTemp = document.querySelector('.resume-filename.mt-3');
            if (existingTemp) {
                existingTemp.remove();
            }
            
            resumeLabel.parentNode.insertBefore(resumeFilename, resumeLabel.nextSibling);
            
            // Re-attach event listener to the new input
            document.getElementById('resumeInput').addEventListener('change', handleResumeUpload);
        }
    }

    document.getElementById('resumeUploadLabel').addEventListener('click', function() {
        document.getElementById('resumeInput').click();
    });

    document.getElementById('resumeInput').addEventListener('change', handleResumeUpload);

    // === SKILL MANAGEMENT ===
    document.getElementById('add-skill').addEventListener('click', function() {
        const container = document.getElementById('skills-container');
        const emptySection = container.querySelector('.empty-section');
        if (emptySection) {
            emptySection.remove();
        }
        
        const div = document.createElement('div');
        div.className = 'd-flex mb-2 skill-entry';
        div.innerHTML = `
            <div class="flex-grow-1 me-2">
                <input type="text" name="skills[]" class="form-control" placeholder="e.g., Microsoft Office, Programming, Communication">
            </div>
            <button type="button" class="btn btn-outline-danger btn-remove-skill align-self-start"><i class="bi bi-trash"></i></button>
        `;
        container.appendChild(div);
        skillCounter++;
    });

    document.getElementById('skills-container').addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-skill')) {
            const skillEntry = e.target.closest('.skill-entry');
            skillEntry.remove();
            
            const container = document.getElementById('skills-container');
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="empty-section">
                        <i class="bi bi-award display-4 text-muted mb-3"></i>
                        <p class="text-muted mb-2">No skills added yet</p>
                        <small class="text-muted">Add your first skill to get started</small>
                    </div>
                `;
            }
        }
    });

    // === EXPERIENCE MANAGEMENT ===
    document.getElementById('add-experience').addEventListener('click', function() {
        const container = document.getElementById('experience-container');
        const emptySection = container.querySelector('.empty-section');
        if (emptySection) {
            emptySection.remove();
        }
        
        const div = document.createElement('div');
        div.className = 'row g-2 mb-3 experience-entry border-bottom pb-3';
        div.innerHTML = `
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Company/Organization *</label>
                <input type="text" name="experience_company[]" class="form-control" placeholder="Company Name" data-validate="required">
                <div class="validation-feedback" data-feedback="company-${experienceCounter}"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Position/Role *</label>
                <input type="text" name="experience_position[]" class="form-control" placeholder="Your Position" data-validate="required">
                <div class="validation-feedback" data-feedback="position-${experienceCounter}"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Start Year *</label>
                <input type="number" name="experience_start[]" class="form-control year-input" placeholder="2020" min="2000" max="${currentYear}" data-validate="required,year,workStartYear">
                <div class="validation-feedback" data-feedback="start-${experienceCounter}"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">End Year</label>
                <input type="number" name="experience_end[]" class="form-control year-input" placeholder="2022" min="2000" max="${currentYear}" data-validate="year,endYear">
                <div class="validation-feedback" data-feedback="end-${experienceCounter}"></div>
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Description</label>
                <textarea name="experience_description[]" class="form-control" rows="2" placeholder="Describe your responsibilities and achievements"></textarea>
            </div>
            <div class="col-12">
                <button type="button" class="btn btn-outline-danger btn-remove-experience mt-2"><i class="bi bi-trash"></i> Remove Experience</button>
            </div>
        `;
        container.appendChild(div);
        
        div.querySelectorAll('input[data-validate]').forEach(field => {
            attachValidation(field);
        });
        experienceCounter++;
    });

    document.getElementById('experience-container').addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-experience')) {
            const experienceEntry = e.target.closest('.experience-entry');
            experienceEntry.remove();
            
            const container = document.getElementById('experience-container');
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="empty-section">
                        <i class="bi bi-briefcase display-4 text-muted mb-3"></i>
                        <p class="text-muted mb-2">No work experience added yet</p>
                        <small class="text-muted">Add your first work experience to get started</small>
                    </div>
                `;
            }
        }
    });

    // === EDUCATION MANAGEMENT ===
    document.getElementById('add-education').addEventListener('click', function() {
        const container = document.getElementById('education-container');
        const emptySection = container.querySelector('.empty-section');
        if (emptySection) {
            emptySection.remove();
        }
        
        const div = document.createElement('div');
        div.className = 'row g-2 mb-3 education-entry border-bottom pb-3';
        div.innerHTML = `
            <div class="col-md-6">
                <label class="form-label small fw-semibold">School/University *</label>
                <input type="text" name="education_school[]" class="form-control" placeholder="School Name" data-validate="required">
                <div class="validation-feedback" data-feedback="school-${educationCounter}"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Degree/Course *</label>
                <input type="text" name="education_degree[]" class="form-control" placeholder="e.g., BS Information Technology" data-validate="required">
                <div class="validation-feedback" data-feedback="degree-${educationCounter}"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Start Year *</label>
                <input type="number" name="education_start[]" class="form-control year-input" placeholder="2018" min="2000" max="${currentYear}" data-validate="required,year,eduStartYear">
                <div class="validation-feedback" data-feedback="edu-start-${educationCounter}"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">End Year</label>
                <input type="number" name="education_end[]" class="form-control year-input" placeholder="2022" min="2000" max="${currentYear}" data-validate="year,eduEndYear">
                <div class="validation-feedback" data-feedback="edu-end-${educationCounter}"></div>
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Honors/Awards</label>
                <input type="text" name="education_honors[]" class="form-control" placeholder="e.g., Cum Laude, Dean's Lister">
            </div>
            <div class="col-12">
                <button type="button" class="btn btn-outline-danger btn-remove-education mt-2"><i class="bi bi-trash"></i> Remove Education</button>
            </div>
        `;
        container.appendChild(div);
        
        div.querySelectorAll('input[data-validate]').forEach(field => {
            attachValidation(field);
        });
        educationCounter++;
    });

    document.getElementById('education-container').addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-education')) {
            const educationEntry = e.target.closest('.education-entry');
            educationEntry.remove();
            
            const container = document.getElementById('education-container');
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="empty-section">
                        <i class="bi bi-mortarboard display-4 text-muted mb-3"></i>
                        <p class="text-muted mb-2">No education added yet</p>
                        <small class="text-muted">Add your first educational background to get started</small>
                    </div>
                `;
            }
        }
    });

    // Form validation before submit
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        let isValid = true;
        const fields = document.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]');
        
        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            alert('Please fix the errors before saving your profile.');
        }
    });
});
</script>