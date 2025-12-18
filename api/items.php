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

// Helper function to handle image upload
function handleImageUpload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = '../assets/images/items/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $allowedExtensions)) {
        return null; // Invalid file type
    }

    // Generate unique filename
    $filename = uniqid('item_') . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'assets/images/items/' . $filename; // Return relative path for DB
    }

    return null;
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
if (isPostRequest()) {
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
    
    $imagePath = null;
    if (isset($_FILES['image'])) {
        $imagePath = handleImageUpload($_FILES['image']);
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO items (name, price, availability, image_path, description, tags, unit) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdissss", $name, $price, $availability, $imagePath, $description, $tags, $unit);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, ['id' => $conn->insert_id], 'Item added successfully');
    } else {
        sendJsonResponse(false, null, 'Failed to add item: ' . $conn->error);
    }
    $stmt->close();
}

// PUT: Update item (Admin only)
if (isPutRequest()) {
    if (!isAdminLoggedIn()) {
        sendJsonResponse(false, null, 'Unauthorized access');
    }
    
    // PHP doesn't parse multipart/form-data for PUT requests automatically
    // We'll use POST with a _method=PUT parameter for file uploads, or handle raw JSON for simple updates
    
    // Since we're updating to use FormData which sends POST, this block might be bypassed
    // But let's keep basic logic for non-file updates or use a special header/param
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
        $imagePath = handleImageUpload($_FILES['image']);
        if ($imagePath) {
            $stmt = $conn->prepare("UPDATE items SET name = ?, price = ?, availability = ?, image_path = ?, description = ?, tags = ?, unit = ? WHERE id = ?");
            $stmt->bind_param("sdissssi", $name, $price, $availability, $imagePath, $description, $tags, $unit, $id);
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
        // This fallback logic handles JSON updates if ever used fully
        // But the main update logic is now in the POST override block
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
?>
