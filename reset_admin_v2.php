<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();
$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
$stmt->bind_param("ss", $hash, $username);

if ($stmt->execute()) {
    echo "Password reset successfully for user '$username'. New hash: $hash";
} else {
    echo "Error resetting password: " . $conn->error;
}
?>
