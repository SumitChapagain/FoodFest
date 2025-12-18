<?php
/**
 * Orders API
 * 
 * Handles order operations
 * GET: Fetch all orders or single order by token
 * POST: Create new order
 * PUT: Update order status (admin only)
 */

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$conn = getDBConnection();

// GET - Fetch orders
if (isGetRequest()) {
    // Check if requesting single order by token
    if (isset($_GET['token'])) {
        $token = sanitizeInput($_GET['token']);
        
        // Fetch order with items
        $stmt = $conn->prepare("
            SELECT o.*, 
                   GROUP_CONCAT(
                       CONCAT(i.name, '|', oi.quantity, '|', oi.price) 
                       SEPARATOR ';;'
                   ) as items_data
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN items i ON oi.item_id = i.id
            WHERE o.token_id = ?
            GROUP BY o.id
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            sendJsonResponse(false, null, 'Order not found');
        }
        
        $order = $result->fetch_assoc();
        
        // Parse items data
        $items = [];
        if ($order['items_data']) {
            $itemsArray = explode(';;', $order['items_data']);
            foreach ($itemsArray as $itemData) {
                list($name, $quantity, $price) = explode('|', $itemData);
                $items[] = [
                    'name' => $name,
                    'quantity' => intval($quantity),
                    'price' => floatval($price)
                ];
            }
        }
        $order['items'] = $items;
        unset($order['items_data']);
        
        $stmt->close();
        sendJsonResponse(true, $order);
        
    } else {
        // Fetch all orders
        $query = "
            SELECT o.id, o.token_id, o.customer_name, o.total_price, o.status, o.created_at,
                   COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ";
        $result = $conn->query($query);
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        sendJsonResponse(true, $orders);
    }
}

// POST - Create new order
if (isPostRequest()) {
    $input = getJsonInput();
    
    // Validate input
    if (!isset($input['customer_name']) || !isset($input['items']) || empty($input['items'])) {
        sendJsonResponse(false, null, 'Customer name and items are required');
    }
    
    $customerName = sanitizeInput($input['customer_name']);
    $items = $input['items'];
    
    // Calculate total price and validate items
    $totalPrice = 0;
    $validatedItems = [];
    
    foreach ($items as $item) {
        if (!isset($item['id']) || !isset($item['quantity'])) {
            sendJsonResponse(false, null, 'Invalid item data');
        }
        
        $itemId = intval($item['id']);
        $quantity = intval($item['quantity']);
        
        if ($quantity <= 0) {
            continue;
        }
        
        // Fetch item details
        $stmt = $conn->prepare("SELECT id, name, price, availability FROM items WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            sendJsonResponse(false, null, "Item with ID {$itemId} not found");
        }
        
        $itemData = $result->fetch_assoc();
        $stmt->close();
        
        if ($itemData['availability'] == 0) {
            sendJsonResponse(false, null, "{$itemData['name']} is currently unavailable");
        }
        
        $itemPrice = floatval($itemData['price']);
        $totalPrice += $itemPrice * $quantity;
        
        $validatedItems[] = [
            'id' => $itemId,
            'quantity' => $quantity,
            'price' => $itemPrice
        ];
    }
    
    if (empty($validatedItems)) {
        sendJsonResponse(false, null, 'No valid items in order');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert order (without token first to get ID)
        $stmt = $conn->prepare("INSERT INTO orders (token_id, customer_name, total_price, status) VALUES (?, ?, ?, 'Pending')");
        $tempToken = 'TEMP';
        $stmt->bind_param("ssd", $tempToken, $customerName, $totalPrice);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();
        
        // Generate token based on order ID
        $tokenId = generateTokenId($orderId);
        
        // Update order with real token
        $stmt = $conn->prepare("UPDATE orders SET token_id = ? WHERE id = ?");
        $stmt->bind_param("si", $tokenId, $orderId);
        $stmt->execute();
        $stmt->close();
        
        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($validatedItems as $item) {
            $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Fetch complete order data
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        sendJsonResponse(true, $order, 'Order placed successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        logError("Order creation failed: " . $e->getMessage());
        sendJsonResponse(false, null, 'Failed to create order');
    }
}

// PUT - Update order status (admin only)
if (isPutRequest()) {
    requireAdminLogin();
    
    $input = getJsonInput();
    
    if (!isset($input['id']) || !isset($input['status'])) {
        sendJsonResponse(false, null, 'Order ID and status are required');
    }
    
    $orderId = intval($input['id']);
    $status = sanitizeInput($input['status']);
    
    // Validate status
    $validStatuses = ['Pending', 'Preparing', 'Completed', 'Cancelled'];
    if (!in_array($status, $validStatuses)) {
        sendJsonResponse(false, null, 'Invalid status value');
    }
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Fetch updated order
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        sendJsonResponse(true, $order, 'Order status updated successfully');
    } else {
        sendJsonResponse(false, null, 'Failed to update order status');
    }
}

// DELETE - Clear all orders (admin only, requires password)
if (isDeleteRequest()) {
    requireAdminLogin();
    
    $input = getJsonInput();
    $password = $input['password'] ?? '';
    $action = $input['action'] ?? '';
    
    if ($action !== 'deleteAll') {
        sendJsonResponse(false, null, 'Invalid action');
    }
    
    if (empty($password)) {
        sendJsonResponse(false, null, 'Password is required');
    }
    
    // Verify password against logged-in admin
    $adminId = $_SESSION['admin_id']; // Assumed set by login
    
    $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendJsonResponse(false, null, 'Admin not found');
    }
    
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($password, $admin['password_hash'])) {
        sendJsonResponse(false, null, 'Incorrect password');
    }
    
    // Password correct, proceed to delete
    $conn->begin_transaction();
    
    try {
        // Disable foreign key checks to allow truncating if needed, but delete is safer for integrity
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Truncate tables to reset auto_increment if possible, or just delete
        $conn->query("TRUNCATE TABLE order_items");
        $conn->query("TRUNCATE TABLE orders");
        
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $conn->commit();
        sendJsonResponse(true, null, 'Order history cleared successfully');
        
    } catch (Exception $e) {
        $conn->rollback();
        logError("Failed to clear history: " . $e->getMessage());
        sendJsonResponse(false, null, 'Failed to clear history');
    }
}
?>
