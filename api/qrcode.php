<?php
/**
 * QR Code Generator API
 * 
 * Generates QR codes for orders using Google Charts API (works offline after first load)
 * Alternative: Uses a simple QR code generation library
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get order token from request
if (!isset($_GET['token'])) {
    sendJsonResponse(false, null, 'Token is required');
}

$token = sanitizeInput($_GET['token']);

// Fetch order details
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT o.id, o.token_id, o.customer_name, o.total_price, o.status
    FROM orders o
    WHERE o.token_id = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendJsonResponse(false, null, 'Order not found');
}

$order = $result->fetch_assoc();
$stmt->close();

// Create QR code data (JSON format)
$qrData = json_encode([
    'token' => $order['token_id'],
    'name' => $order['customer_name'],
    'order_id' => $order['id']
]);

// Generate QR code URL using Google Charts API
// This creates a QR code image URL that can be used in <img> tags
$qrCodeUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrData) . '&choe=UTF-8';

// For local-only solution, we'll return the data and let the frontend use a JS library
// But also provide the Google Charts URL as fallback

sendJsonResponse(true, [
    'qr_url' => $qrCodeUrl,
    'qr_data' => $qrData,
    'order' => $order
]);

?>
