<?php
/**
 * Revenue API
 * 
 * Handles revenue statistics operations
 * GET: Fetch revenue statistics from completed orders
 */

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$conn = getDBConnection();

// GET - Fetch revenue statistics
if (isGetRequest()) {
    requireAdminLogin();
    
    try {
        // Query to get item-level revenue statistics from completed orders only
        $query = "
            SELECT 
                i.name as item_name,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            INNER JOIN items i ON oi.item_id = i.id
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'Completed'
            GROUP BY i.id, i.name
            ORDER BY total_revenue DESC
        ";
        
        $result = $conn->query($query);
        
        $items = [];
        $totalRevenue = 0;
        
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'item_name' => $row['item_name'],
                'total_quantity' => intval($row['total_quantity']),
                'total_revenue' => floatval($row['total_revenue'])
            ];
            $totalRevenue += floatval($row['total_revenue']);
        }
        
        $data = [
            'items' => $items,
            'total_revenue' => $totalRevenue
        ];
        
        sendJsonResponse(true, $data);
        
    } catch (Exception $e) {
        logError("Revenue stats failed: " . $e->getMessage());
        sendJsonResponse(false, null, 'Failed to fetch revenue statistics');
    }
}

$conn->close();
?>
