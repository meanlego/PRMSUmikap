<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Collect POST data
$company_name = $_POST['company_name'] ?? '';
$company_description = $_POST['company_description'] ?? '';
$contact_person = $_POST['contact_person'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$company_address = $_POST['company_address'] ?? '';
$city = 'Iba';
$province = 'Zambales';

$profilePicPath = null;

// Handle profile picture upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = basename($_FILES['profile_pic']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExts = ['jpg','jpeg','png'];
    
    // Validate file extension
    if (in_array($fileExt, $allowedExts)) {
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);
        
        $allowedMimes = ['image/jpeg','image/png'];
        
        if (in_array($mimeType, $allowedMimes)) {
            $uploadsDir = __DIR__ . '/../uploads/company_pics/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            $newFileName = 'profile_'.$user_id.'_'.time().'.'.$fileExt;
            $destPath = $uploadsDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $profilePicPath = '/../uploads/company_pics/'.$newFileName;
                
                // Delete old profile picture if exists
                try {
                    $stmt = $pdo->prepare("SELECT profile_pic FROM employers_profile WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $oldProfile = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($oldProfile && $oldProfile['profile_pic']) {
                        $oldPath = __DIR__ . '/../' . $oldProfile['profile_pic'];
                        if (file_exists($oldPath) && $oldProfile['profile_pic'] !== 'assets/images/default-pfp.png') {
                            unlink($oldPath);
                        }
                    }
                } catch(Exception $e) {
                    // Continue even if deletion fails
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Error moving uploaded file.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, JPEG, PNG allowed.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file extension. Only JPG, JPEG, PNG allowed.']);
        exit;
    }
}

try {
    // Check if profile exists
    $stmt = $pdo->prepare("SELECT employer_id FROM employers_profile WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() > 0) {
        // Update existing profile
        $query = "UPDATE employers_profile SET 
                  company_name = ?, 
                  company_description = ?, 
                  contact_person = ?, 
                  contact_number = ?, 
                  company_address = ?, 
                  city = ?, 
                  province = ?";
        
        $params = [
            $company_name, 
            $company_description, 
            $contact_person, 
            $contact_number, 
            $company_address, 
            $city, 
            $province
        ];

        // Only update profile_pic if a new one was uploaded
        if ($profilePicPath) {
            $query .= ", profile_pic = ?";
            $params[] = $profilePicPath;
        }

        $query .= " WHERE user_id = ?";
        $params[] = $user_id;

        $update = $pdo->prepare($query);
        $update->execute($params);
    } else {
        // Insert new profile
        $insert = $pdo->prepare("INSERT INTO employers_profile 
                                (user_id, company_name, company_description, contact_person, 
                                 contact_number, company_address, city, province, profile_pic) 
                                VALUES (?,?,?,?,?,?,?,?,?)");
        $insert->execute([
            $user_id, 
            $company_name, 
            $company_description, 
            $contact_person, 
            $contact_number, 
            $company_address, 
            $city, 
            $province, 
            $profilePicPath
        ]);
    }

    echo json_encode([
        'success' => true,
        'profile_pic' => $profilePicPath
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: '.$e->getMessage()
    ]);
}
?>