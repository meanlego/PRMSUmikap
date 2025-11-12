<?php
session_start();
require_once '../database/prmsumikap_db.php';

// Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

$studentId = $_SESSION['user_id'];
$studentName = $_SESSION['name'];

// Fetch email from users table
$stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->execute([$studentId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$studentEmail = $userData['email'] ?? 'example@email.com';

// Dropdowns
$phProvinces = [
    'Zambales', 'Other'
];

$zambalesCities = [
    'Olongapo City', 'Subic', 'Castillejos', 'San Marcelino', 'San Antonio',
    'San Narciso', 'San Felipe', 'Cabangan', 'Botolan', 'Iba', 'Palauig',
    'Masinloc', 'Candelaria', 'Santa Cruz'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Update basic profile
        $stmt = $pdo->prepare("UPDATE students_profile SET 
            headline = ?, bio = ?, phone = ?, city = ?, province = ?, 
            birthdate = ?, gender = ?, student_type = ?, year_level = ?, address = ?
            WHERE user_id = ?");

        $result = $stmt->execute([
            $_POST['headline'] ?? '',
            $_POST['bio'] ?? '',
            $_POST['phone'] ?? '',
            $_POST['city'] ?? '',
            $_POST['province'] ?? '',
            $_POST['birthdate'] ?? null,
            $_POST['gender'] ?? '',
            $_POST['student_type'] ?? '',  
            $_POST['year_level'] ?? '',        
            $_POST['address'] ?? '',
            $studentId
        ]);

        if (!$result) {
            throw new Exception("Failed to update basic profile");
        }

        // Profile picture upload
        if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $fileType = mime_content_type($_FILES['profile_pic']['tmp_name']);
            
            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = '../uploads/profile_pics/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = time() . '_' . uniqid() . '_' . basename($_FILES['profile_pic']['name']);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filePath)) {
                    $stmt = $pdo->prepare("UPDATE students_profile SET profile_pic = ? WHERE user_id = ?");
                    $stmt->execute(['uploads/profile_pics/' . $fileName, $studentId]);
                }
            }
        }

        // RESUME UPLOAD 
        if(isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK){
            $uploadDir = '../uploads/resumes/';
            if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = $studentId . '_' . time() . '_' . basename($_FILES['resume']['name']);
            if(move_uploaded_file($_FILES['resume']['tmp_name'], $uploadDir . $filename)){
                $stmt = $pdo->prepare("UPDATE students_profile SET resume=? WHERE user_id=?");
                $stmt->execute([$filename, $studentId]);
            }
        }
        // Skills - Delete and reinsert
        $deleteSkills = $pdo->prepare("DELETE FROM student_skills WHERE user_id = ?");
        if (!$deleteSkills->execute([$studentId])) {
            throw new Exception("Failed to delete skills");
        }

        if (!empty($_POST['skills'])) {
            $stmt = $pdo->prepare("INSERT INTO student_skills (user_id, skill_name) VALUES (?, ?)");
            foreach ($_POST['skills'] as $skill) {
                $trimmedSkill = trim($skill);
                if (!empty($trimmedSkill)) {
                    if (!$stmt->execute([$studentId, $trimmedSkill])) {
                        throw new Exception("Failed to insert skill: " . $trimmedSkill);
                    }
                }
            }
        }

        // Work Experience - Delete and reinsert
        $deleteExp = $pdo->prepare("DELETE FROM student_experience WHERE user_id = ?");
        if (!$deleteExp->execute([$studentId])) {
            throw new Exception("Failed to delete experience");
        }

        if (!empty($_POST['experience_company'])) {
            $stmt = $pdo->prepare("INSERT INTO student_experience (user_id, company_name, position, start_year, end_year, description) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['experience_company'] as $i => $company) {
                $company = trim($company);
                if (!empty($company)) {
                    $position = trim($_POST['experience_position'][$i] ?? '');
                    $start_year = !empty($_POST['experience_start'][$i]) ? (int)$_POST['experience_start'][$i] : null;
                    $end_year = !empty($_POST['experience_end'][$i]) ? (int)$_POST['experience_end'][$i] : null;
                    $description = trim($_POST['experience_description'][$i] ?? '');
                    
                    if (!$stmt->execute([$studentId, $company, $position, $start_year, $end_year, $description])) {
                        throw new Exception("Failed to insert experience: " . $company);
                    }
                }
            }
        }

        // Education - Delete and reinsert
        $deleteEdu = $pdo->prepare("DELETE FROM student_education WHERE user_id = ?");
        if (!$deleteEdu->execute([$studentId])) {
            throw new Exception("Failed to delete education");
        }

        if (!empty($_POST['education_school'])) {
            $stmt = $pdo->prepare("INSERT INTO student_education (user_id, school_name, degree, start_year, end_year, honors) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['education_school'] as $i => $school) {
                $school = trim($school);
                if (!empty($school)) {
                    $degree = trim($_POST['education_degree'][$i] ?? '');
                    $start_year = !empty($_POST['education_start'][$i]) ? (int)$_POST['education_start'][$i] : null;
                    $end_year = !empty($_POST['education_end'][$i]) ? (int)$_POST['education_end'][$i] : null;
                    $honors = trim($_POST['education_honors'][$i] ?? '');
                    
                    if (!$stmt->execute([$studentId, $school, $degree, $start_year, $end_year, $honors])) {
                        throw new Exception("Failed to insert education: " . $school);
                    }
                }
            }
        }

    $pdo->commit();
    header("Location: " . $_SERVER['PHP_SELF'] . "?saved=1");
    exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Profile update error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to save profile: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch fresh data
try {
    $profile = $pdo->prepare("SELECT * FROM students_profile WHERE user_id = ?");
    $profile->execute([$studentId]);
    $profile = $profile->fetch(PDO::FETCH_ASSOC);

    $skills = $pdo->prepare("SELECT * FROM student_skills WHERE user_id = ?");
    $skills->execute([$studentId]);
    $skills = $skills->fetchAll(PDO::FETCH_ASSOC);

    $experience = $pdo->prepare("SELECT * FROM student_experience WHERE user_id = ?");
    $experience->execute([$studentId]);
    $experience = $experience->fetchAll(PDO::FETCH_ASSOC);

    $education = $pdo->prepare("SELECT * FROM student_education WHERE user_id = ?");
    $education->execute([$studentId]);
    $education = $education->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    $profile = [];
    $skills = [];
    $experience = [];
    $education = [];
}

// Force no cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>