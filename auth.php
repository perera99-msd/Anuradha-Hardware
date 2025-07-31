<?php
require_once 'config/db.php';

session_start();

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Verify user still exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() === 0) {
            // User no longer exists
            header("Location: logout.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Auth error: " . $e->getMessage());
        header("Location: logout.php");
        exit();
    }
} 
// Check remember me cookie
elseif (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, email, first_name, last_name FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Valid token - log user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        } else {
            // Invalid token - clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Remember me error: " . $e->getMessage());
        header("Location: login.php");
        exit();
    }
} 
// Not logged in
else {
    header("Location: login.php");
    exit();
}
?>