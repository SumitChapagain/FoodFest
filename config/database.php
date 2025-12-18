<?php
/**
 * Database Configuration and Connection Handler
 * 
 * This file handles the MySQL database connection for the Food Fest system.
 * It uses mysqli for database operations with error handling.
 */

// Database configuration
// Database configuration
// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'foodfest');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Create database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = mysqli_init();
        
        // Auto-enable SSL for remote connections (Aiven requires this)
        if (getenv('DB_HOST') && getenv('DB_HOST') !== 'localhost') {
            mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        }

        // Connect with port and error handling
        if (!mysqli_real_connect($conn, DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT)) {
            error_log("Database connection failed: " . mysqli_connect_error());
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please check your configuration.'
            ]));
        }
        
        // Set charset to utf8mb4 for proper emoji and special character support
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Close database connection
function closeDBConnection() {
    $conn = getDBConnection();
    if ($conn) {
        $conn->close();
    }
}

// Execute a prepared statement with error handling
function executeQuery($query, $types = '', $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    // Bind parameters if provided
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute query
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    return $stmt;
}

// Get last insert ID
function getLastInsertId() {
    $conn = getDBConnection();
    return $conn->insert_id;
}

?>
