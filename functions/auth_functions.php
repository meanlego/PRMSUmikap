<?php
/**
 * Authentication Functions
 * Remember Me & Direct Password Reset System
 * Compatible with both `user` and `users` tables
 */

// ============================================
// DATABASE UTILITIES
// ============================================

/**
 * Detect the correct users table name.
 */
function getUsersTableName(PDO $pdo): string {
    try {
        $pdo->query("SELECT 1 FROM user LIMIT 1");
        return "user";
    } catch (PDOException $e) {
        return "users"; // fallback
    }
}

// ============================================
// REMEMBER ME FUNCTIONS
// ============================================

/**
 * Set a Remember Me cookie and store its hashed token in the database.
 */
function setRememberMeCookie(PDO $pdo, int $userId): bool {
    try {
        $token = bin2hex(random_bytes(32));
        $tokenHash = password_hash($token, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $tokenHash, $expiresAt]);

        $cookieValue = $userId . ':' . $token;
        $cookieExpiry = time() + (30 * 24 * 60 * 60); // 30 days

        return setcookie('remember_me', $cookieValue, $cookieExpiry, '/', '', false, true);
    } catch (PDOException $e) {
        error_log("Remember me error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check Remember Me cookie and log user in automatically.
 */
function checkRememberMeCookie(PDO $pdo): bool {
    if (isset($_SESSION['user_id']) || empty($_COOKIE['remember_me'])) {
        return false;
    }

    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) !== 2) {
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        return false;
    }

    [$userId, $token] = $parts;
    $usersTable = getUsersTableName($pdo);

    try {
        $stmt = $pdo->prepare("
            SELECT u.*, rt.token_hash 
            FROM {$usersTable} u
            JOIN remember_tokens rt ON u.user_id = rt.user_id
            WHERE u.user_id = ? AND rt.expires_at > NOW()
        ");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            if (password_verify($token, $row['token_hash'])) {
                // Log in user
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['role'] = $row['role'];
                return true;
            }
        }

        // Invalid token: delete cookie
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        return false;
    } catch (PDOException $e) {
        error_log("Remember me check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove Remember Me tokens from database and delete the cookie.
 */
function removeRememberMeCookie(PDO $pdo, int $userId): void {
    try {
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log("Remove remember me error: " . $e->getMessage());
    }
    setcookie('remember_me', '', time() - 3600, '/', '', false, true);
}

// ============================================
// DIRECT PASSWORD RESET FUNCTIONS
// ============================================

/**
 * Reset a user's password directly (no email).
 */
function resetPasswordByEmail(PDO $pdo, string $email, string $newPassword, string $confirmPassword): array {
    $usersTable = getUsersTableName($pdo);

    if ($newPassword !== $confirmPassword) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    if (strlen($newPassword) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }

    try {
        // Verify email exists
        $stmt = $pdo->prepare("SELECT user_id FROM {$usersTable} WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'Email not found.'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE {$usersTable} SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $user['user_id']]);

        // Remove remember me tokens to force re-login
        removeRememberMeCookie($pdo, $user['user_id']);

        return ['success' => true, 'message' => 'Password updated successfully! You can now log in.'];
    } catch (PDOException $e) {
        error_log("Reset password error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred. Please try again.'];
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Clean up expired Remember Me tokens.
 */
function cleanupExpiredTokens(PDO $pdo): void {
    try {
        $pdo->exec("DELETE FROM remember_tokens WHERE expires_at < NOW()");
    } catch (PDOException $e) {
        error_log("Cleanup error: " . $e->getMessage());
    }
}

/**
 * Auto-login if Remember Me cookie exists.
 */
function autoLogin(PDO $pdo): void {
    checkRememberMeCookie($pdo);
}
?>
