<?php
/**
 * Utility Functions
 * 
 * This file contains helper functions used throughout the application.
 */

/**
 * Generate a unique token ID for orders
 * Format: FF2025-XXX (where XXX is a sequential number)
 * 
 * @param int $orderId The order ID from database
 * @return string The formatted token ID
 */
function generateTokenId($orderId) {
    // Generate 4-digit token (0001 to 9999) using modulo
    // Use modulo 10000 to keep it within 4 digits
    $tokenNum = $orderId % 10000;
    // Handle case where modulo 0 might occur (though order IDs usually start at 1)
    if ($tokenNum == 0) $tokenNum = 1; 
    
    return str_pad($tokenNum, 4, '0', STR_PAD_LEFT);
}

/**
 * Hash password using PHP's password_hash function
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password from database
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize input data to prevent XSS attacks
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Send JSON response
 * 
 * @param bool $success Success status
 * @param mixed $data Data to send (array or string)
 * @param string $message Optional message
 */
function sendJsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit();
}

/**
 * Get current timestamp in MySQL format
 * 
 * @return string Current timestamp
 */
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

/**
 * Format price for display
 * 
 * @param float $price Price value
 * @return string Formatted price with currency symbol
 */
function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

/**
 * Format date/time for display
 * 
 * @param string $datetime MySQL datetime string
 * @return string Formatted date/time
 */
function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

/**
 * Get order status badge color
 * 
 * @param string $status Order status
 * @return string CSS class for status badge
 */
function getStatusColor($status) {
    switch ($status) {
        case 'Pending':
            return 'status-pending';
        case 'Preparing':
            return 'status-preparing';
        case 'Completed':
            return 'status-completed';
        default:
            return 'status-default';
    }
}

/**
 * Log error message
 * 
 * @param string $message Error message to log
 */
function logError($message) {
    error_log("[PLANTIANS] " . date('Y-m-d H:i:s') . " - " . $message);
}

/**
 * Check if request is POST
 * 
 * @return bool True if POST request
 */
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 * 
 * @return bool True if GET request
 */
function isGetRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Check if request is PUT
 * 
 * @return bool True if PUT request
 */
function isPutRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'PUT';
}

/**
 * Check if request is DELETE
 * 
 * @return bool True if DELETE request
 */
function isDeleteRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'DELETE';
}

/**
 * Get JSON input from request body
 * 
 * @return array Decoded JSON data
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

?>
