<?php
// Script untuk testing executeQuery function
require_once 'db_connect.php';

if ($conn) {
    echo "<h2>Testing executeQuery Function</h2>";
    
    // Test 1: Simple query without parameters
    echo "<h3>Test 1: Simple query without parameters</h3>";
    $result = executeQuery("SELECT COUNT(*) as count FROM users");
    if ($result) {
        if ($result instanceof mysqli_result) {
            $row = $result->fetch_assoc();
            echo "<p style='color: green;'>✓ Query executed successfully. User count: " . $row['count'] . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Query executed but returned: " . gettype($result) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Query failed</p>";
    }
    
    // Test 2: Query with parameters
    echo "<h3>Test 2: Query with parameters</h3>";
    $result = executeQuery("SELECT * FROM users WHERE username = ?", ['admin']);
    if ($result) {
        if ($result instanceof mysqli_result) {
            $rowCount = $result->num_rows;
            echo "<p style='color: green;'>✓ Parameterized query executed successfully. Rows found: " . $rowCount . "</p>";
            
            if ($rowCount > 0) {
                $user = $result->fetch_assoc();
                echo "<p>User found: " . $user['username'] . " (" . $user['nama_lengkap'] . ")</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Parameterized query executed but returned: " . gettype($result) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Parameterized query failed</p>";
    }
    
    // Test 3: Direct mysqli query for comparison
    echo "<h3>Test 3: Direct mysqli query for comparison</h3>";
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", "admin");
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                $rowCount = $result->num_rows;
                echo "<p style='color: green;'>✓ Direct prepared statement executed successfully. Rows found: " . $rowCount . "</p>";
                
                if ($rowCount > 0) {
                    $user = $result->fetch_assoc();
                    echo "<p>User found: " . $user['username'] . " (" . $user['nama_lengkap'] . ")</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Direct prepared statement get_result failed</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Direct prepared statement execute failed: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>✗ Direct prepared statement prepare failed: " . $conn->error . "</p>";
    }
} else {
    echo "<h2 style='color: red;'>Koneksi database gagal!</h2>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}

echo "<br><a href='debug_login.php'>Debug Login</a> | <a href='login.php'>Try Login</a> | <a href='index.php'>Home</a>";
?>