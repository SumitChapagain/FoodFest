<?php
require_once '../config/session.php';
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - PLANTIANS</title>
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
            <a href="items.php" class="nav-link active">Manage Items</a>
            <a href="orders.php" class="nav-link">Manage Orders</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Manage Food Items</h1>
            <button class="btn btn-primary" onclick="showAddItemModal()">+ Add New Item</button>
        </div>
        
            <div class="items-grid" id="itemsGrid">
            <!-- Items will be loaded here -->
        </div>
    </div>
    
    <!-- Add/Edit Item Modal -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeItemModal()">&times;</span>
            <h2 id="modalTitle">Add New Item</h2>
            <form id="itemForm">
                <input type="hidden" id="itemId">
                
                <div class="form-group">
                    <label for="itemName">Item Name</label>
                    <input type="text" id="itemName" required>
                </div>
                
                <div class="form-group">
                    <label for="itemPrice">Price (Rs.)</label>
                    <input type="number" id="itemPrice" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="itemImage">Item Image</label>
                    <input type="file" id="itemImage" accept="image/*">
                    <div id="imagePreview" class="mt-2"></div>
                </div>

                <div class="form-group">
                    <label for="itemDescription">Description</label>
                    <textarea id="itemDescription" rows="3" placeholder="Brief description of the item"></textarea>
                </div>

                <div class="form-group">
                    <label for="itemTags">Tags (comma separated)</label>
                    <input type="text" id="itemTags" placeholder="e.g. Vegetarian, Hot, Spicy">
                </div>

                <div class="form-group">
                    <label for="itemUnit">Unit</label>
                    <input type="text" id="itemUnit" placeholder="e.g. per plate, per cup" value="per plate">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="itemAvailability" checked>
                        Available for ordering
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeItemModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="js/admin.js"></script>
    <script>
        // Detect base path dynamically
        const basePath = window.location.pathname.includes('/FoodFest') ? '/FoodFest' : '';

        let items = [];
        
        // Load all items
        async function loadItems() {
            try {
                const response = await fetch(`${basePath}/api/items.php`);
                const data = await response.json();
                
                if (data.success) {
                    items = data.data;
                    displayItems();
                }
            } catch (error) {
                console.error('Failed to load items:', error);
            }
        }
        
        // Display items in grid
        function displayItems() {
            const grid = document.getElementById('itemsGrid');
            
            if (items.length === 0) {
                grid.innerHTML = '<p class="no-data">No items yet. Add your first item!</p>';
                return;
            }
            
            grid.innerHTML = items.map(item => `
                <div class="item-card ${item.availability == 0 ? 'unavailable' : ''}">
                    <div class="item-image-container">
                        ${item.image_data ? 
                            `<img src="${item.image_data}" alt="${item.name}" class="item-img-preview">` :
                            (item.image_path ? 
                                `<img src="${basePath}/${item.image_path}" alt="${item.name}" class="item-img-preview">` : 
                                `<div class="no-image">🍔</div>`)
                        }
                    </div>
                    <div class="item-header">
                        <h3>${item.name}</h3>
                        <span class="item-price">Rs. ${parseFloat(item.price).toFixed(2)}</span>
                    </div>
                    <div class="item-status">
                        ${item.availability == 1 ? 
                            '<span class="badge badge-success">Available</span>' : 
                            '<span class="badge badge-danger">Out of Stock</span>'}
                    </div>
                    <div class="item-actions">
                        <button class="btn btn-sm btn-secondary" onclick="editItem(${item.id})">Edit</button>
                        <button class="btn btn-sm ${item.availability == 1 ? 'btn-warning' : 'btn-success'}" 
                                onclick="toggleAvailability(${item.id}, ${item.availability})">
                            ${item.availability == 1 ? 'Disable' : 'Enable'}
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.id})">Delete</button>
                    </div>
                </div>
            `).join('');
        }
        
        // Show add item modal
        function showAddItemModal() {
            document.getElementById('modalTitle').textContent = 'Add New Item';
            document.getElementById('itemForm').reset();
            document.getElementById('itemId').value = '';
            document.getElementById('itemUnit').value = 'per plate';
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('itemModal').style.display = 'block';
        }
        
        // Edit item
        function editItem(id) {
            const item = items.find(i => i.id == id);
            if (!item) return;
            
            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemPrice').value = item.price;
            document.getElementById('itemDescription').value = item.description || '';
            document.getElementById('itemTags').value = item.tags || '';
            document.getElementById('itemUnit').value = item.unit || 'per plate';
            document.getElementById('itemAvailability').checked = item.availability == 1;
            
            const preview = document.getElementById('imagePreview');
            if (item.image_data) {
                 preview.innerHTML = `<img src="${item.image_data}" style="max-width: 100px; max-height: 100px; margin-top: 5px; border-radius: 4px;">`;
            } else if (item.image_path) {
                preview.innerHTML = `<img src="${basePath}/${item.image_path}" style="max-width: 100px; max-height: 100px; margin-top: 5px; border-radius: 4px;">`;
            } else {
                preview.innerHTML = '';
            }
            
            document.getElementById('itemModal').style.display = 'block';
        }
        
        // Close modal
        function closeItemModal() {
            document.getElementById('itemModal').style.display = 'none';
        }
        
        // Save item (add or update)
        document.getElementById('itemForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const itemId = document.getElementById('itemId').value;
            const formData = new FormData();
            
            formData.append('name', document.getElementById('itemName').value);
            formData.append('price', document.getElementById('itemPrice').value);
            formData.append('description', document.getElementById('itemDescription').value);
            formData.append('tags', document.getElementById('itemTags').value);
            formData.append('unit', document.getElementById('itemUnit').value);
            formData.append('availability', document.getElementById('itemAvailability').checked ? 1 : 0);
            
            const imageFile = document.getElementById('itemImage').files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }
            
            if (itemId) {
                formData.append('id', itemId);
                formData.append('_method', 'PUT'); // Trick to handle PUT with files
            }
            
            try {
                const response = await fetch(`${basePath}/api/items.php`, {
                    method: 'POST', // Always POST for FormData with files
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeItemModal();
                    loadItems();
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error(error);
                alert('Failed to save item');
            }
        });
        
        // Toggle availability
        async function toggleAvailability(id, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;
            
            try {
                const response = await fetch(`${basePath}/api/items.php`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, availability: newStatus })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadItems();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Failed to update item');
            }
        }
        
        // Delete item
        async function deleteItem(id) {
            if (!confirm('Are you sure you want to delete this item?')) return;
            
            try {
                const response = await fetch(`${basePath}/api/items.php`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadItems();
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Failed to delete item');
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('itemModal');
            if (event.target == modal) {
                closeItemModal();
            }
        }
        
        // Load items on page load
        loadItems();
    </script>
</body>
</html>
