<?php
session_start();
include __DIR__ . '/../database/prmsumikap_db.php';

session_regenerate_id(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in all fields.";
        header("Location: ../auth/login.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = "Please enter a valid email address.";
        header("Location: ../auth/login.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password, role, created_at FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

                unset($_SESSION['login_error']);

                $base_url = "http://localhost/prmsumikap-rebase";

                switch ($user['role']) {
                    case 'student':
                        header("Location: {$base_url}/employee/dashboard.php");
                        exit;
                    
                    case 'employer':
                        header("Location: {$base_url}/employer/dashboard.php");
                        exit;
                    
                    default:
                        $_SESSION['login_error'] = "Invalid user role.";
                        header("Location: ../auth/login.php");
                        exit;
                }

            } else {
                $_SESSION['login_error'] = "Invalid email or password.";
                header("Location: ../auth/login.php");
                exit;
            }
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: ../auth/login.php");
            exit;
        }

    } catch (PDOException $e) {
        error_log("Login PDO Error: " . $e->getMessage());
        
        $_SESSION['login_error'] = "A system error occurred. Please try again.";
        header("Location: ../auth/login.php");
        exit;
    }
} else {
    header("Location: ../auth/login.php");
    exit;
}
?>