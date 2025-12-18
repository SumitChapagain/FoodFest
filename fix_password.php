<?php
/**
 * Script to fix the admin password in the database
 */

require_once 'config/database.php';

echo "<h2>Fixing Admin Password</h2>";

// Generate correct hash for 'admin123'
$correctPassword = 'admin123';
$correctHash = password_hash($correctPassword, PASSWORD_DEFAULT);

echo "Generating new password hash for: <strong>admin123</strong><br>";
echo "New hash: " . $correctHash . "<br><br>";

// Update the database
$conn = getDBConnection();
$stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'");
$stmt->bind_param("s", $correctHash);

if ($stmt->execute()) {
    echo "✅ <strong>Password updated successfully!</strong><br><br>";
    
    // Verify the update
    $verifyStmt = $conn->prepare("SELECT password_hash FROM admins WHERE username = 'admin'");
    $verifyStmt->execute();
    $result = $verifyStmt->get_result();
    $admin = $result->fetch_assoc();
    
    if (password_verify($correctPassword, $admin['password_hash'])) {
        echo "✅ <strong>Password verification successful!</strong><br>";
        echo "<br>You can now login with:<br>";
        echo "Username: <strong>admin</strong><br>";
        echo "Password: <strong>admin123</strong><br><br>";
        echo "<a href='admin/login.php'>Go to Admin Login</a>";
    } else {
        echo "❌ Password verification failed after update<br>";
    }
    
    $verifyStmt->close();
} else {
    echo "❌ Failed to update password: " . $stmt->error . "<br>";
}

$stmt->close();
?>
