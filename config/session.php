<?php
/**
 * Session Management Configuration
 * 
 * This file handles session initialization and management for admin authentication.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings for security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    // Set session name
    session_name('PLANTIANS_SESSION');
    
    // Start the session
    session_start();
}

// Check if user is logged in as admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Get logged in admin ID
function getAdminId() {
    return isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
}

// Get logged in admin username
function getAdminUsername() {
    return isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : null;
}

// Set admin session data after successful login
function setAdminSession($adminId, $username) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['admin_username'] = $username;
    $_SESSION['login_time'] = time();
}

// Destroy admin session (logout)
function destroyAdminSession() {
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

// Require admin login - redirect to login page if not authenticated
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /FoodFest/admin/login.php');
        exit();
    }
}

?>
