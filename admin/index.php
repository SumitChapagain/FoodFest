<?php
require_once '../config/session.php';
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PLANTIANS</title>
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <img src="../assets/images/logo.png" alt="Food Fest" class="navbar-logo">
            <h2>PLANTIANS Admin</h2>
        </div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link active">Dashboard</a>
            <a href="items.php" class="nav-link">Manage Items</a>
            <a href="orders.php" class="nav-link">Manage Orders</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars(getAdminUsername()); ?>!</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3 id="totalOrders">0</h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3 id="pendingOrders">0</h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h3 id="cancelledOrders">0</h3>
                    <p>Cancelled</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3 id="completedOrders">0</h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>

        <div class="chart-section">
            <h2>Order Statistics</h2>
            <div class="chart-container">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="items.php" class="btn btn-primary">Manage Food Items</a>
                <a href="orders.php" class="btn btn-success">View Orders</a>
            </div>
        </div>
        
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <div id="recentOrdersList"></div>
        </div>
    </div>
    
    <script src="js/dashboard-chart.js"></script>
    <script src="js/admin.js"></script>
    <script>
        // Load dashboard stats
        async function loadDashboardStats() {
            try {
                const response = await fetch('/FoodFest/api/orders.php');
                const data = await response.json();
                
                if (data.success) {
                    const orders = data.data;
                    
                    // Calculate stats
                    document.getElementById('totalOrders').textContent = orders.length;
                    document.getElementById('pendingOrders').textContent = 
                        orders.filter(o => o.status === 'Pending').length;
                    document.getElementById('cancelledOrders').textContent = 
                        orders.filter(o => o.status === 'Cancelled').length;
                    document.getElementById('completedOrders').textContent = 
                        orders.filter(o => o.status === 'Completed').length;
                    
                    // Display recent orders (last 5)
                    const recentOrders = orders.slice(0, 5);
                    const ordersList = document.getElementById('recentOrdersList');
                    
                    if (recentOrders.length === 0) {
                        ordersList.innerHTML = '<p class="no-data">No orders yet</p>';
                    } else {
                        ordersList.innerHTML = recentOrders.map(order => `
                            <div class="order-item">
                                <div class="order-info">
                                    <strong>${order.token_id}</strong> - ${order.customer_name}
                                    <span class="status-badge ${order.status.toLowerCase()}">${order.status}</span>
                                </div>
                                <div class="order-meta">
                                    Rs. ${parseFloat(order.total_price).toFixed(2)} | ${new Date(order.created_at).toLocaleString()}
                                </div>
                            </div>
                        `).join('');
                    }
                }
            } catch (error) {
                console.error('Failed to load dashboard stats:', error);
            }
        }
        
        // Initialize Chart
        const chart = new DashboardChart('ordersChart');

        // Hook into existing stats update
        const originalLoadStats = loadDashboardStats;
        loadDashboardStats = async function() {
            await originalLoadStats();
            
            // Get values from DOM since original function updates them
            const pending = parseInt(document.getElementById('pendingOrders').textContent) || 0;
            const cancelled = parseInt(document.getElementById('cancelledOrders').textContent) || 0;
            const completed = parseInt(document.getElementById('completedOrders').textContent) || 0;
            
            chart.setData({
                'Pending': pending,
                'Cancelled': cancelled,
                'Completed': completed
            });
        };
        
        // Load stats on page load
        loadDashboardStats();
        
        // Refresh stats every 10 seconds
        setInterval(loadDashboardStats, 10000);
    </script>
</body>
</html>
