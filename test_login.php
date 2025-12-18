<?php
/**
 * Test script to verify admin credentials and database connection
 */

require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";

// Test database connection
$conn = getDBConnection();
if ($conn) {
    echo "✅ Database connection successful<br><br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Check if admins table exists and has data
echo "<h3>Admin Users in Database:</h3>";
$result = $conn->query("SELECT id, username, password_hash FROM admins");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Password Hash</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . substr($row['password_hash'], 0, 50) . "...</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "❌ No admin users found in database<br>";
}

// Test password verification
echo "<h3>Password Verification Test:</h3>";
$testPassword = 'admin123';
$stmt = $conn->prepare("SELECT password_hash FROM admins WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $hash = $admin['password_hash'];
    
    echo "Testing password: <strong>admin123</strong><br>";
    echo "Stored hash: " . substr($hash, 0, 60) . "...<br>";
    
    if (password_verify($testPassword, $hash)) {
        echo "✅ Password verification SUCCESSFUL<br>";
    } else {
        echo "❌ Password verification FAILED<br>";
        echo "<br><strong>Generating new hash for 'admin123':</strong><br>";
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "New hash: " . $newHash . "<br>";
        echo "<br><strong>SQL to update password:</strong><br>";
        echo "<code>UPDATE admins SET password_hash = '$newHash' WHERE username = 'admin';</code><br>";
    }
} else {
    echo "❌ Admin user not found<br>";
}

$stmt->close();
?>
