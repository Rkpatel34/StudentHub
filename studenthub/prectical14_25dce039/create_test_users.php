<?php
/**
 * create_test_users.php — Create Test Users for Practical 10 Testing
 * 
 * Run this once to create test accounts:
 * - Student: student@test.com / password123
 * - Admin: admin@test.com / password123
 * 
 * DELETE THIS FILE AFTER USE IN PRODUCTION!
 */

require_once __DIR__ . '/db.php';

echo "<h1>Creating Test Users for Practical 10</h1>";

try {
    // Test Student
    $studentEmail = 'student@test.com';
    $studentUsername = 'student';
    $studentPassword = 'password123';
    $studentHash = password_hash($studentPassword, PASSWORD_DEFAULT);
    
    // Check if already exists
    $existing = db_mysqli_select("SELECT user_id FROM `users` WHERE `email` = ? OR `username` = ?", [$studentEmail, $studentUsername]);
    
    if (empty($existing)) {
        $studentId = db_mysqli_insert(
            "INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`) VALUES (?, ?, ?, ?, ?)",
            [$studentUsername, $studentEmail, $studentHash, 'Test Student', 'student']
        );
        echo "<p style='color:green;'>✅ Created student user: $studentUsername (ID: $studentId)</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Student user already exists (ID: {$existing[0]['user_id']})</p>";
    }
    
    // Test Admin
    $adminEmail = 'admin@test.com';
    $adminUsername = 'admin';
    $adminPassword = 'password123';
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $existing = db_mysqli_select("SELECT user_id FROM `users` WHERE `email` = ? OR `username` = ?", [$adminEmail, $adminUsername]);
    
    if (empty($existing)) {
        $adminId = db_mysqli_insert(
            "INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`) VALUES (?, ?, ?, ?, ?)",
            [$adminUsername, $adminEmail, $adminHash, 'Test Admin', 'admin']
        );
        echo "<p style='color:green;'>✅ Created admin user: $adminUsername (ID: $adminId)</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Admin user already exists (ID: {$existing[0]['user_id']})</p>";
    }
    
    echo "<hr>";
    echo "<h2>Test Credentials</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>Role</th><th>Username</th><th>Email</th><th>Password</th></tr>";
    echo "<tr><td>Student</td><td>student</td><td>student@test.com</td><td>password123</td></tr>";
    echo "<tr><td>Admin</td><td>admin</td><td>admin@test.com</td><td>password123</td></tr>";
    echo "</table>";
    
    echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this file (create_test_users.php) after testing!</p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("create_test_users.php error: " . $e->getMessage());
}