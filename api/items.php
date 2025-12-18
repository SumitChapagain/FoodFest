<?php
/**
 * Items API
 * 
 * Handles CRUD operations for food items
 */

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Disable error printing to output (breaks JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL); // Keep logging enabled

try {

    // Helper function to handle image processing (Returns Base64 string)
    function handleImageProcess($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            return null; // Invalid file type
        }
        
        // Read file content
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            return null;
        }
        
        // Convert to base64
        $base64 = base64_encode($content);
        
        // Determine mime type safely
        $mimeType = 'image/jpeg'; // Default fallback
        
        // Suppress warnings from these functions just in case
        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($file['tmp_name']);
            if ($detected) $mimeType = $detected;
        } elseif (function_exists('getimagesize')) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo && isset($imageInfo['mime'])) {
                $mimeType = $imageInfo['mime'];
            }
        }
        
        return 'data:' . $mimeType . ';base64,' . $base64;
    }

    // GET: Fetch all items
    if (isGetRequest()) {
        $conn = getDBConnection();
        
        // Check if we need to filter by availability
        $whereClause = "";
        if (isset($_GET['available']) && $_GET['available'] === 'true') {
            $whereClause = "WHERE availability = 1";
        }
        
        $query = "SELECT * FROM items $whereClause ORDER BY name ASC";
        $result = $conn->query($query);
        
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        
        sendJsonResponse(true, $items);
    }

    // POST: Create new item (Admin only)
    // Explicitly check that _method is NOT set, to avoid catching PUT-over-POST requests
    if (isPostRequest() && !isset($_POST['_method'])) {
        if (!isAdminLoggedIn()) {
            sendJsonResponse(false, null, 'Unauthorized access');
        }
        
        // Check if it's a multipart form data (file upload)
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $availability = isset($_POST['availability']) ? $_POST['availability'] : 1;
        $description = $_POST['description'] ?? '';
        $tags = $_POST['tags'] ?? '';
        $unit = $_POST['unit'] ?? 'plate';
        
        if (empty($name) || $price < 0) {
            sendJsonResponse(false, null, 'Invalid item data');
        }
        
        $imageData = null;
        $imagePath = null; // Deprecated but kept for schema compatibility or fallback
        
        if (isset($_FILES['image'])) {
            $imageData = handleImageProcess($_FILES['image']);
        }
        
        $conn = getDBConnection();
        // image_data is the new column
        $stmt = $conn->prepare("INSERT INTO items (name, price, availability, image_data, image_path, description, tags, unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdisssss", $name, $price, $availability, $imageData, $imagePath, $description, $tags, $unit);
        
        if ($stmt->execute()) {
            sendJsonResponse(true, ['id' => $conn->insert_id], 'Item added successfully');
        } else {
            sendJsonResponse(false, null, 'Failed to add item: ' . $conn->error);
        }
        $stmt->close();
    }

    // Handle POST with _method=PUT override for file uploads
    if (isPostRequest() && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
        if (!isAdminLoggedIn()) {
            sendJsonResponse(false, null, 'Unauthorized access');
        }

        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $availability = isset($_POST['availability']) ? $_POST['availability'] : 1;
        $description = $_POST['description'] ?? '';
        $tags = $_POST['tags'] ?? '';
        $unit = $_POST['unit'] ?? 'plate';
        
        if ($id <= 0) {
            sendJsonResponse(false, null, 'Invalid item ID');
        }
        
        $conn = getDBConnection();
        
        // Check for new image
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $imageData = handleImageProcess($_FILES['image']);
            if ($imageData) {
                $stmt = $conn->prepare("UPDATE items SET name = ?, price = ?, availability = ?, image_data = ?, description = ?, tags = ?, unit = ? WHERE id = ?");
                $stmt->bind_param("sdissssi", $name, $price, $availability, $imageData, $description, $tags, $unit, $id);
            } else {
                 // Image upload failed or invalid, just update other fields
                 $stmt = $conn->prepare("UPDATE items SET name = ?, price = ?, availability = ?, description = ?, tags = ?, unit = ? WHERE id = ?");
                 $stmt->bind_param("sdisssi", $name, $price, $availability, $description, $tags, $unit, $id);
            }
        } else {
            // No new image, update other fields
            $stmt = $conn->prepare("UPDATE items SET name = ?, price = ?, availability = ?, description = ?, tags = ?, unit = ? WHERE id = ?");
            $stmt->bind_param("sdisssi", $name, $price, $availability, $description, $tags, $unit, $id);
        }
        
        if ($stmt->execute()) {
            sendJsonResponse(true, null, 'Item updated successfully');
        } else {
            sendJsonResponse(false, null, 'Failed to update item: ' . $conn->error);
        }
        $stmt->close();
        exit;
    }

    // Handle raw PUT input for simple toggle/updates without file
    if (isPutRequest()) {
        if (!isAdminLoggedIn()) {
            sendJsonResponse(false, null, 'Unauthorized access');
        }
        
        $input = getJsonInput();
        $id = $input['id'] ?? 0;
        
        if ($id <= 0) {
            sendJsonResponse(false, null, 'Invalid item ID');
        }
        
        $conn = getDBConnection();
        
        // Partial update (availability only) or full update
        if (isset($input['availability']) && count($input) == 2) { // only id and availability
            $availability = $input['availability'];
            $stmt = $conn->prepare("UPDATE items SET availability = ? WHERE id = ?");
            $stmt->bind_param("ii", $availability, $id);
        } else {
            $name = $input['name'];
            $price = $input['price'];
            $availability = $input['availability'];
            $stmt = $conn->prepare("UPDATE items SET name = ?, price = ?, availability = ? WHERE id = ?");
            $stmt->bind_param("sdii", $name, $price, $availability, $id);
        }
        
        if ($stmt->execute()) {
            sendJsonResponse(true, null, 'Item updated successfully');
        } else {
            sendJsonResponse(false, null, 'Failed to update item: ' . $conn->error);
        }
        $stmt->close();
    }


    // DELETE: Delete item (Admin only)
    if (isDeleteRequest()) {
        if (!isAdminLoggedIn()) {
            sendJsonResponse(false, null, 'Unauthorized access');
        }
        
        $input = getJsonInput();
        $id = $input['id'] ?? 0;
        
        if ($id <= 0) {
            sendJsonResponse(false, null, 'Invalid item ID');
        }
        
        $conn = getDBConnection();
        
        // Check if item is used in any orders first to prevent foreign key errors
        // Ideally we would soft delete, but for this simple app we'll restrict delete
        $check = $conn->query("SELECT COUNT(*) as count FROM order_items WHERE item_id = $id");
        $row = $check->fetch_assoc();
        
        if ($row['count'] > 0) {
            sendJsonResponse(false, null, 'Cannot delete item: it is part of existing orders. Disable it instead.');
        }
        
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendJsonResponse(true, null, 'Item deleted successfully');
        } else {
            sendJsonResponse(false, null, 'Failed to delete item: ' . $conn->error);
        }
        $stmt->close();
    }

} catch (Throwable $e) {
    // Catch any crash/fatal error and return it as JSON
    error_log("API Error: " . $e->getMessage());
    // Manual JSON construction to be safe
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
    exit;
}
?>
