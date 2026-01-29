<?php
/**
 * Database Configuration File for Music Database System
 * This file handles the MySQL connection using XAMPP
 * 
 * To use this file in other PHP pages:
 * require_once 'db_config.php';
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Default XAMPP MySQL user
define('DB_PASS', '');               // Default XAMPP MySQL password (empty)
define('DB_NAME', 'music_database_system');

// Create database connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4 for proper character support
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Initialize connection
$conn = getDBConnection();

/**
 * Secure query execution function
 * @param string $query SQL query with ? placeholders
 * @param string $types Parameter types (i, d, s, b)
 * @param array $params Parameters to bind
 * @return mysqli_stmt Prepared statement
 */
function executeQuery($query, $types = "", $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt;
}

/**
 * Fetch all results as associative array
 */
function fetchAll($query, $types = "", $params = []) {
    $stmt = executeQuery($query, $types, $params);
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

/**
 * Fetch single row as associative array
 */
function fetchOne($query, $types = "", $params = []) {
    $stmt = executeQuery($query, $types, $params);
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

/**
 * Start session if not already started
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Initialize session
initSession();

// Timezone setting
date_default_timezone_set('UTC');
?>
