<?php
// Simple database connection test
require_once 'config.php';

if ($conn) {
    echo "Database connection successful!\n";
    
    // Test if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        echo "Users table exists.\n";
        
        // Try to fetch a user
        $userResult = $conn->query("SELECT * FROM users LIMIT 1");
        if ($userResult && $userResult->num_rows > 0) {
            echo "Can read from users table.\n";
            $user = $userResult->fetch_assoc();
            echo "Sample user: " . $user['username'] . "\n";
        } else {
            echo "Cannot read from users table or table is empty.\n";
        }
    } else {
        echo "Users table does not exist.\n";
    }
} else {
    echo "Database connection failed.\n";
}
?>