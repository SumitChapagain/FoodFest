<?php
require_once '../config/session.php';
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - PLANTIANS</title>
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <img src="../assets/images/logo.png" alt="PLANTIANS" class="navbar-logo">
            <h2>PLANTIANS Admin</h2>
        </div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="items.php" class="nav-link">Manage Items</a>
            <a href="orders.php" class="nav-link active">Manage Orders</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Manage Orders</h1>
            <div class="order-filters">
                <button class="filter-btn active" data-status="all">All</button>
                <button class="filter-btn" data-status="Pending">Pending</button>
                <button class="filter-btn" data-status="Preparing">Preparing</button>
                <button class="filter-btn" data-status="Completed">Completed</button>
                <button class="filter-btn" data-status="Cancelled">Cancelled</button>
            </div>
        </div>
        
        <div class="orders-list" id="ordersList">
            <!-- Orders will be loaded here -->
        </div>
    </div>
    
    <div id="orderModal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeOrderModal()">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetails"></div>
        </div>
    </div>
    
    <!-- Clear History FAB -->
    <button class="fab-clear" onclick="showClearHistoryModal()" title="Clear Order History">
        🗑️
    </button>
    
    <!-- Clear History Modal -->
    <div id="clearHistoryModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close" onclick="closeClearHistoryModal()">&times;</span>
            <h2 style="color: var(--danger-color);">⚠️ Clear History</h2>
            <p>This will <strong>permanently delete all orders</strong>. This action cannot be undone.</p>
            <form id="clearHistoryForm" style="margin-top: 1.5rem;">
                <div class="form-group">
                    <label for="adminPasswordConfirmation">Enter Admin Password to Confirm</label>
                    <input type="password" id="adminPasswordConfirmation" required placeholder="Admin Password">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeClearHistoryModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Audio for notification -->
    <audio id="notificationSound" preload="auto">
        <source src="../assets/sounds/notification.mp3" type="audio/mpeg">
    </audio>
    
    <script src="js/admin.js"></script>
    <script src="js/qrcode.min.js"></script>
    <script>
        // Detect base path dynamically
        const basePath = window.location.pathname.includes('/FoodFest') ? '/FoodFest' : '';

        // State variables
        let orders = [];
        let currentFilter = 'all';
        let lastOrderId = 0;
        let isFirstLoad = true;
        
        // Audio Context for Beep Sound
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        function playNotificationSound() {
            // Browsers require user interaction to resume audio context
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(e => console.log('Audio resume failed', e));
            }
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(500, audioCtx.currentTime); // 500Hz
            oscillator.frequency.exponentialRampToValueAtTime(1000, audioCtx.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
            
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.5);
        }

        // Show Toast Notification
        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #f97316;
                color: white;
                padding: 1rem 2rem;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.3);
                z-index: 1000;
                animation: slideUp 0.3s ease-out;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        // Load all orders
        async function loadOrders() {
            try {
                const response = await fetch(`${basePath}/api/orders.php`);
                const data = await response.json();
                
                if (data.success) {
                    const newOrders = data.data; // Assumed sorted DESC by ID or CreatedAt
                    
                    if (newOrders.length > 0) {
                        const latestOrder = newOrders[0];
                        const latestId = parseInt(latestOrder.id);
                        
                        // Check for new orders
                        if (isFirstLoad) {
                            lastOrderId = latestId;
                            isFirstLoad = false;
                        } else if (latestId > lastOrderId) {
                            // New order detected
                            lastOrderId = latestId;
                            playNotificationSound();
                            showToast(`New Order Received! Token: ${latestOrder.token_id}`);
                        }
                    }
                    
                    orders = newOrders;
                    displayOrders();
                }
            } catch (error) {
                console.error('Failed to load orders:', error);
            }
        }
        
        // Display orders
        function displayOrders() {
            const ordersList = document.getElementById('ordersList');
            
            // Filter orders
            let filteredOrders = orders;
            if (currentFilter !== 'all') {
                filteredOrders = orders.filter(o => o.status === currentFilter);
            }
            
            if (filteredOrders.length === 0) {
                ordersList.innerHTML = '<p class="no-data">No orders found</p>';
                return;
            }
            
            ordersList.innerHTML = filteredOrders.map(order => `
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>${order.token_id}</h3>
                            <p class="customer-name">${order.customer_name}</p>
                        </div>
                        <div class="order-status">
                            <div class="status-btn-group">
                                <button class="btn-status ${order.status === 'Pending' ? 'active pending' : ''}" 
                                        onclick="updateOrderStatus(${order.id}, 'Pending')">Pending</button>
                                <button class="btn-status ${order.status === 'Completed' ? 'active completed' : ''}" 
                                        onclick="updateOrderStatus(${order.id}, 'Completed')">Completed</button>
                                <button class="btn-status ${order.status === 'Cancelled' ? 'active cancelled' : ''}" 
                                        onclick="updateOrderStatus(${order.id}, 'Cancelled')">Cancelled</button>
                            </div>
                        </div>
                    </div>
                    <div class="order-info">
                        <span><strong>Total:</strong> Rs. ${parseFloat(order.total_price).toFixed(2)}</span>
                        <span><strong>Items:</strong> ${order.item_count}</span>
                        <span><strong>Time:</strong> ${new Date(order.created_at).toLocaleString()}</span>
                    </div>
                    <div class="order-actions">
                        <button class="btn btn-sm btn-primary" onclick="viewOrderDetails('${order.token_id}')">
                            View Details
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.status;
                displayOrders();
            });
        });
        
        // Update order status
        async function updateOrderStatus(orderId, newStatus) {
            try {
                const response = await fetch(`${basePath}/api/orders.php`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: orderId, status: newStatus })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update local data without full reload to prevent UI jump
                    const orderIndex = orders.findIndex(o => o.id == orderId);
                    if (orderIndex !== -1) {
                        orders[orderIndex].status = newStatus;
                        // Don't call displayOrders() here to keep dropdown focus if needed, 
                        // but refreshing list style is good.
                        displayOrders();
                    }
                } else {
                    alert('Error: ' + data.message);
                    loadOrders(); // Revert
                }
            } catch (error) {
                alert('Failed to update order status');
                loadOrders();
            }
        }
        
        // View order details
        async function viewOrderDetails(token) {
            try {
                const response = await fetch(`${basePath}/api/orders.php?token=${token}`);
                const data = await response.json();
                
                if (data.success) {
                    const order = data.data;
                    const detailsDiv = document.getElementById('orderDetails');
                    
                    // Prepare enriched QR data
                    const qrData = `Customer Name : ${order.customer_name}\nToken: ${order.token_id}\n` + 
                        order.items.map((item, i) => `Item ${i+1}: ${item.name}\nPrice: Rs. ${parseFloat(item.price).toFixed(2)}`).join('\n') +
                        `\nTotal Price: Rs. ${parseFloat(order.total_price).toFixed(2)}`;

                    detailsDiv.innerHTML = `
                        <div class="order-detail-section">
                            <h3>Order Information</h3>
                            <p><strong>Token ID:</strong> ${order.token_id}</p>
                            <p><strong>Customer Name:</strong> ${order.customer_name}</p>
                            <p><strong>Status:</strong> <span class="status-badge ${order.status.toLowerCase()}">${order.status}</span></p>
                            <p><strong>Order Time:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        </div>
                        
                        <div class="order-detail-section">
                            <h3>Items Ordered</h3>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${order.items.map(item => `
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    ${item.image_path ? `<img src="${basePath}/${item.image_path}" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">` : ''}
                                                    ${item.name}
                                                </div>
                                            </td>
                                            <td>${item.quantity}</td>
                                            <td>Rs. ${parseFloat(item.price).toFixed(2)}</td>
                                            <td>Rs. ${(item.quantity * item.price).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"><strong>Total</strong></td>
                                        <td><strong>Rs. ${parseFloat(order.total_price).toFixed(2)}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="order-detail-section">
                            <h3>QR Code</h3>
                            <div class="qr-code-display" id="adminQrCode"></div>
                        </div>
                    `;
                    
                    document.getElementById('orderModal').style.display = 'block';

                    // Generate local QR code
                    document.getElementById('adminQrCode').innerHTML = '';
                    new QRCode(document.getElementById("adminQrCode"), {
                        text: qrData,
                        width: 200,
                        height: 200,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.M
                    });
                }
            } catch (error) {
                console.error(error);
                alert('Failed to load order details');
            }
        }
        
        // Close modal
        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            const clearModal = document.getElementById('clearHistoryModal');
            if (event.target == modal) {
                closeOrderModal();
            }
            if (event.target == clearModal) {
                closeClearHistoryModal();
            }
        }
        
        // Clear History Logic
        function showClearHistoryModal() {
            document.getElementById('clearHistoryModal').style.display = 'block';
            document.getElementById('adminPasswordConfirmation').value = '';
            document.getElementById('adminPasswordConfirmation').focus();
        }
        
        function closeClearHistoryModal() {
            document.getElementById('clearHistoryModal').style.display = 'none';
        }
        
        document.getElementById('clearHistoryForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('adminPasswordConfirmation').value;
            
            if (!confirm('Are you absolutely sure? All data will be lost.')) return;
            
            try {
                const response = await fetch(`${basePath}/api/orders.php`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'deleteAll', password: password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    closeClearHistoryModal();
                    loadOrders();
                    // Optional: reload page to reset counters if any
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error(error);
                alert('Failed to clear history');
            }
        });
        
        // Load orders on page load
        loadOrders();
        
        // Auto-refresh every 2 seconds (Real-time feel)
        setInterval(loadOrders, 2000);
    </script>
</body>
</html>
