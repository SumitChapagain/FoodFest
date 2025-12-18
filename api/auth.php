<?php
/**
 * Authentication API
 * 
 * Handles admin login authentication
 */

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Only allow POST requests
if (!isPostRequest()) {
    sendJsonResponse(false, null, 'Invalid request method');
}

// Get JSON input
$input = getJsonInput();

// Validate input
if (!isset($input['username']) || !isset($input['password'])) {
    sendJsonResponse(false, null, 'Username and password are required');
}

$username = sanitizeInput($input['username']);
$password = $input['password'];

// Query database for admin
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendJsonResponse(false, null, 'Invalid username or password');
}

$admin = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!verifyPassword($password, $admin['password_hash'])) {
    sendJsonResponse(false, null, 'Invalid username or password');
}

// Set session
setAdminSession($admin['id'], $admin['username']);

// Send success response
sendJsonResponse(true, [
    'username' => $admin['username']
], 'Login successful');

?>
