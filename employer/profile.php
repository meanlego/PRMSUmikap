<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

$employerId = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->execute([$employerId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT company_name, company_address, contact_number, company_description, contact_person, profile_pic FROM employers_profile WHERE user_id = ?");
$stmt->execute([$employerId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$companyName = $profile['company_name'] ?? 'Employer';
$employerEmail = $userData['email'] ?? 'example@email.com';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Company Profile | PRMSUmikap</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<link rel="icon" type="image/png" href="/prmsumikap-rebase/assets/images/favicon.png">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">

<style>

#successMessage {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1050;
    min-width: 300px;
    max-width: 90%;
    text-align: center;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}
</style>
</head>

<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>


<div id="successMessage" class="alert alert-success alert-dismissible fade d-none" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>Profile successfully saved!
    <button type="button" class="btn-close" onclick="hideSuccessMessage()"></button>
</div>

<div id="main-content" class="p-4">

    <div class="welcome-card mb-4 d-flex align-items-center profile-saved">
        <div class="position-relative me-4">
            <img id="profilePreview" 
                 src="<?= htmlspecialchars('../' . ($profile['profile_pic'] ?? 'assets/images/default-pfp.png')) ?>" 
                 class="bg-white rounded-circle p-2 border" 
                 style="width:100px; height:100px; object-fit:cover;">

            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute bottom-0 end-0 border"
                    onclick="document.getElementById('profilePicInput').click();">
                <i class="bi bi-camera"></i>
            </button>
        </div>
        <div>
            <h2 class="fw-bold"><?= htmlspecialchars($companyName) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($employerEmail) ?></p>
        </div>
    </div>

    <form id="profileForm" enctype="multipart/form-data">
 
        <input type="file" id="profilePicInput" name="profile_pic" accept=".jpg,.jpeg,.png" class="d-none">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-file-earmark-text me-2"></i>Business Information</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Business Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="company_name" required value="<?= htmlspecialchars($profile['company_name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="contact_person" required value="<?= htmlspecialchars($profile['contact_person'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">+63</span>
                        <input type="tel" class="form-control" name="contact_number" 
                            value="<?= htmlspecialchars($profile['contact_number'] ?? '') ?>" 
                            placeholder="9123456789" 
                            pattern="\d{10}" 
                            maxlength="10"
                            title="Please enter exactly 10 digits"
                            required>
                    </div>
                </div>

               <div class="mb-4">
                    <label class="form-label fw-semibold">Business Description <span class="text-danger">*</span></label>
                    <textarea id="companyDescription" class="form-control" name="company_description" rows="4" required
                        placeholder="Tell candidates about your company..." minlength="50"><?= htmlspecialchars($profile['company_description'] ?? '') ?></textarea>
                    <small id="charCountHelp" class="text-muted">Minimum 50 characters required.</small>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Address Information</h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Complete Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="company_address" rows="1" placeholder="Street, Barangay" required><?= htmlspecialchars($profile['company_address'] ?? '') ?></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">City/Municipality</label>
                        <input type="text" class="form-control" name="city" value="Iba" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Province</label>
                        <input type="text" class="form-control" name="province" value="Zambales" readonly>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn text-white fw-semibold py-3" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
                        <i class="bi bi-save me-2"></i>Save Profile
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function showSuccessMessage() {
    const alertBox = document.getElementById('successMessage');
    alertBox.classList.remove('d-none');
    alertBox.classList.add('show');
    setTimeout(() => hideSuccessMessage(), 3000);
}

function hideSuccessMessage() {
    const alertBox = document.getElementById('successMessage');
    alertBox.classList.remove('show');
    alertBox.classList.add('d-none');
}

document.getElementById('profileForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);


    const fileInput = document.getElementById('profilePicInput');
    if(fileInput.files.length > 0){
        formData.set('profile_pic', fileInput.files[0]);
    }

    fetch('../config/save_employer_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            showSuccessMessage();
            if(data.profile_pic){
                document.getElementById('profilePreview').src = '../' + data.profile_pic;
            }
        } else {
            alert(data.error || 'An error occurred.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('An unexpected error occurred.');
    });
});


document.getElementById('profilePicInput').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(event){
            document.getElementById('profilePreview').src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});

document.getElementById('profileForm').addEventListener('submit', function(e){
    const textarea = document.getElementById('companyDescription');
    const charCount = textarea.value.trim().length;

    if(charCount < 50){
        e.preventDefault();
        alert('Business Description must have at least 100 characters. Currently: ' + charCount);
        textarea.focus();
    }
});
</script>

</body>
</html>