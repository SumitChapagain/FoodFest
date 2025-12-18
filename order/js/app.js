/**
 * Customer Interface JavaScript
 * 
 * Handles menu loading, cart management, and order placement
 */

let menuItems = [];
let cart = [];

// Page navigation
function showPage(pageId) {
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    document.getElementById(pageId).classList.add('active');
}

// Load menu items
async function loadMenu() {
    try {
        const response = await fetch('/FoodFest/api/items.php?available=true');
        const data = await response.json();

        if (data.success) {
            menuItems = data.data;
            displayMenu();
        } else {
            document.getElementById('menuItems').innerHTML =
                '<p class="loading">Failed to load menu. Please refresh.</p>';
        }
    } catch (error) {
        console.error('Failed to load menu:', error);
        document.getElementById('menuItems').innerHTML =
            '<p class="loading">Failed to load menu. Please refresh.</p>';
    }
}

// Display menu items
function displayMenu() {
    const menuContainer = document.getElementById('menuItems');
    const loadingDiv = document.querySelector('.loading');

    if (loadingDiv) {
        loadingDiv.style.display = 'none';
    }

    if (menuItems.length === 0) {
        menuContainer.innerHTML = '<div class="no-items">No items available at the moment.</div>';
        return;
    }

    menuContainer.innerHTML = menuItems.map(item => {
        // Check if item is in cart to show quantity controls
        const cartItem = cart.find(c => c.id === item.id);
        const quantity = cartItem ? cartItem.quantity : 0;

        // Handle tags
        let tagsHtml = '';
        if (item.tags) {
            tagsHtml = item.tags.split(',').map(tag =>
                `<span class="tag-pill">${tag.trim()}</span>`
            ).join('');
        }

        return `
            <div class="menu-item-card">
                ${item.image_path ?
                `<div class="item-card-image" style="background-image: url('/FoodFest/${item.image_path}')"></div>` :
                `<div class="item-card-image placeholder">🍔</div>`
            }
                <div class="item-card-content">
                    <h3 class="item-card-title">${item.name}</h3>
                    <p class="item-card-description">${item.description || ''}</p>
                    
                    <div class="item-card-tags">
                        ${tagsHtml}
                    </div>
                    
                    <div class="item-card-footer">
                        <div class="price-unit">
                            <span class="price">Rs.${parseFloat(item.price).toFixed(2)}</span>
                            <span class="unit">${item.unit || 'per plate'}</span>
                        </div>
                        
                        <div class="card-actions">
                             ${quantity > 0 ? `
                                <div class="quantity-control sm">
                                    <button class="btn-qty" onclick="decreaseQuantity('${item.id}')">−</button>
                                    <span class="qty-display">${quantity}</span>
                                    <button class="btn-qty" onclick="increaseQuantity('${item.id}')">+</button>
                                </div>
                            ` : `
                                <button class="btn-add-card" onclick="addToCart('${item.id}')">
                                    <span>+</span> Add
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Add item to cart
function addToCart(itemId) {
    // Ensure itemId is string for comparison
    const searchId = String(itemId);
    const item = menuItems.find(i => i.id === searchId);
    if (!item) return;

    cart.push({
        id: item.id,
        name: item.name,
        price: parseFloat(item.price),
        quantity: 1
    });

    updateCart();
}

// Increase quantity
function increaseQuantity(itemId) {
    const searchId = String(itemId);
    const cartItem = cart.find(c => c.id === searchId);
    if (cartItem) {
        cartItem.quantity++;
    } else {
        addToCart(searchId);
    }
    updateCart();
}

// Decrease quantity
function decreaseQuantity(itemId) {
    const searchId = String(itemId);
    const cartItem = cart.find(c => c.id === searchId);
    if (!cartItem) return;

    cartItem.quantity--;
    if (cartItem.quantity <= 0) {
        cart = cart.filter(c => c.id !== searchId);
    }

    updateCart();
}

// Update cart display
function updateCart() {
    // Save cart to local storage
    localStorage.setItem('plantians_cart', JSON.stringify(cart));

    // Update menu display to reflect quantities
    displayMenu();

    const cartSummary = document.getElementById('cartSummary');

    // Switch to popup style class
    cartSummary.className = 'cart-popup';

    if (cart.length === 0) {
        cartSummary.classList.remove('visible');
        cartSummary.style.display = 'none'; // Ensure hidden if empty
        return;
    }

    // Force display to flex (overriding index.php inline style)
    cartSummary.style.display = 'flex';

    // Calculate totals
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    // Render Popup Content
    cartSummary.innerHTML = `
        <div class="cart-popup-info">
            <div class="cart-count-badge">
                <span>🛍️</span> ${totalItems} Items
            </div>
            <div class="cart-total-price">
                Rs. ${totalPrice.toFixed(2)}
            </div>
        </div>
        <button class="btn-checkout" onclick="showCheckout()">
            Proceed to Checkout <span>→</span>
        </button>
    `;

    // Show popup with small delay for animation if not already visible
    if (!cartSummary.classList.contains('visible')) {
        setTimeout(() => cartSummary.classList.add('visible'), 50);
    } else {
        // Pulse effect on update
        cartSummary.classList.add('animate-pulse');
        setTimeout(() => cartSummary.classList.remove('animate-pulse'), 300);
    }
}

// Clear cart
function clearCart() {
    if (confirm('Clear all items from cart?')) {
        cart = [];
        updateCart();
    }
}

// Show checkout page
function showCheckout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    // Display checkout items
    const checkoutItemsDiv = document.getElementById('checkoutItems');
    checkoutItemsDiv.innerHTML = cart.map(item => `
        <div class="checkout-item">
            <div style="flex: 1;">
                <div style="font-weight: 500; margin-bottom: 4px;">${item.name}</div>
                <div class="checkout-qty-wrapper">
                    <div class="checkout-qty-control">
                        <button class="btn-qty-sm" onclick="updateCheckoutQuantity('${item.id}', -1)">−</button>
                        <span style="min-width: 20px; text-align: center;">${item.quantity}</span>
                        <button class="btn-qty-sm" onclick="updateCheckoutQuantity('${item.id}', 1)">+</button>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">× Rs. ${item.price.toFixed(2)}</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="font-weight: 600;">Rs. ${(item.quantity * item.price).toFixed(2)}</div>
                <button class="btn-remove-item" onclick="removeFromCheckout('${item.id}')" aria-label="Remove item">×</button>
            </div>
        </div>
    `).join('');

    // Display total
    const total = cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
    document.getElementById('checkoutTotal').textContent = `Rs. ${total.toFixed(2)}`;

    showPage('checkoutPage');
}


// Remove item from checkout
function removeFromCheckout(itemId) {
    const searchId = String(itemId);
    cart = cart.filter(c => c.id !== searchId);

    if (cart.length === 0) {
        backToMenu();
        updateCart();
    } else {
        updateCart(); // Update local storage and cart summary
        showCheckout(); // Re-render checkout page
    }
}

// Update checkout quantity
function updateCheckoutQuantity(itemId, change) {
    const searchId = String(itemId);
    const cartItem = cart.find(c => c.id === searchId);
    if (!cartItem) return;

    cartItem.quantity += change;

    if (cartItem.quantity <= 0) {
        removeFromCheckout(itemId);
    } else {
        updateCart();
        showCheckout();
    }
}

// Back to menu
function backToMenu() {
    showPage('mainPage');
}

// Handle checkout form submission
document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    const customerName = document.getElementById('customerName').value.trim();

    if (!customerName) {
        alert('Please enter your name');
        return;
    }

    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    // Lock button
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Placing Order...';

    // Prepare order data
    const orderData = {
        customer_name: customerName,
        items: cart.map(item => ({
            id: item.id,
            quantity: item.quantity
        }))
    };

    try {
        const response = await fetch('/FoodFest/api/orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        });

        const data = await response.json();

        if (data.success) {
            showConfirmation(data.data);
        } else {
            alert('Error: ' + data.message);
            // Unlock on specific API error
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    } catch (error) {
        console.error('Order failed:', error);
        alert('Failed to place order. Please try again.');
        // Unlock on network error
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});

// Show confirmation page
function showConfirmation(orderData) {
    // Display token number
    document.getElementById('tokenNumber').textContent = orderData.token_id;

    // Prepare items for QR code and display
    const confirmationItems = cart.map(item => ({
        name: item.name,
        quantity: item.quantity,
        price: item.price
    }));

    // Generate QR code as formatted text (Receipt style)
    let qrText = `Customer Name : ${orderData.customer_name}\n`;
    qrText += `Token: ${orderData.token_id}\n`;

    confirmationItems.forEach((item, index) => {
        qrText += `Item ${index + 1}: ${item.name}\n`;
        qrText += `Price: Rs. ${parseFloat(item.price).toFixed(2)}\n`;
    });

    qrText += `Total Price: Rs. ${parseFloat(orderData.total_price).toFixed(2)}`;

    const qrData = qrText;

    const qrContainer = document.getElementById('qrCode');
    qrContainer.innerHTML = ''; // Clear previous QR code

    // Create new QR code instance
    new QRCode(qrContainer, {
        text: qrData,
        width: 180,
        height: 180,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.M
    });

    // Display order items
    const confirmationItemsDiv = document.getElementById('confirmationItems');
    confirmationItemsDiv.innerHTML = confirmationItems.map(item => `
        <div class="confirmation-item">
            <div>
                <div style="font-weight: 500;">${item.name}</div>
                <div style="color: #6b7280; font-size: 0.9rem;">${item.quantity} × Rs. ${item.price.toFixed(2)}</div>
            </div>
            <div>Rs. ${(item.quantity * item.price).toFixed(2)}</div>
        </div>
    `).join('');

    // Remove old donation message if it exists (cleanup from previous implementation if needed, though simpler to just not add it here)
    // PREVIOUS IMPLEMENTATION REMOVED FROM HERE

    // Display total
    document.getElementById('confirmationTotal').textContent = `Rs. ${parseFloat(orderData.total_price).toFixed(2)}`;

    showPage('confirmationPage');

    // Add Donation Message below Token Number (and ensure no duplicates)
    const tokenDisplay = document.querySelector('.token-display');
    // Check if message already exists to avoid duplication on re-shows (though showConfirmation is usually one-off per order)
    let donationMsg = document.getElementById('donationMessage');
    if (!donationMsg) {
        donationMsg = document.createElement('div');
        donationMsg.id = 'donationMessage';
        donationMsg.style.cssText = "text-align: center; margin-top: 5px; margin-bottom: 20px; font-style: italic; color: #16a34a; font-weight: 600; font-size: 1.2rem;";
        donationMsg.textContent = "Thank You for Donating Rs.5";
        tokenDisplay.after(donationMsg);
    }

    // Generate Screenshot Receipt
    setTimeout(() => {
        // Create a temporary container for the receipt to ensure a clean look (White BG, Black Text)
        const receiptContainer = document.createElement('div');
        receiptContainer.id = 'receipt-container';
        receiptContainer.style.cssText = `
            position: fixed; 
            top: -9999px; 
            left: 0; 
            width: 400px; 
            background: #fff; 
            color: #000; 
            padding: 20px; 
            font-family: monospace; 
            border: 1px solid #ccc;
            z-index: 9999;
        `;

        // Clone QR code content (it might be a canvas or img)
        // Clone QR code content (with mobile fix)
        const qrSource = document.getElementById('qrCode');
        const qrClone = document.createElement('div');

        // Check for canvas (desktop/some browsers) or img (some mobile)
        const canvas = qrSource.querySelector('canvas');
        const img = qrSource.querySelector('img');

        let qrImage = new Image();

        if (canvas) {
            qrImage.src = canvas.toDataURL();
        } else if (img) {
            qrImage.src = img.src;
        }

        qrImage.style.cssText = 'margin: 10px auto; display: block; max-width: 100%;';
        qrClone.appendChild(qrImage);

        receiptContainer.innerHTML = `
            <div style="text-align: center; margin-bottom: 15px;">
                <h2 style="margin:0; font-size: 24px;">PLANTIANS 2025</h2>
                <p style="margin:5px 0 0 0; font-size: 14px;">Order Receipt</p>
            </div>
            <div style="border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                <p style="margin:2px 0;"><strong>Token:</strong> <span style="font-size: 18px;">${orderData.token_id}</span></p>
                <p style="margin:2px 0;"><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                <p style="margin:2px 0;"><strong>Customer:</strong> ${orderData.customer_name}</p>
            </div>
            <div style="margin-bottom: 10px;">
                ${cart.map(item => `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>${item.name} x${item.quantity}</span>
                        <span>Rs. ${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `).join('')}
            </div>
            <div style="border-top: 2px dashed #000; padding-top: 10px; margin-top: 10px; text-align: right;">
                <h3 style="margin:0;">Total: Rs. ${parseFloat(orderData.total_price).toFixed(2)}</h3>
            </div>
             <div style="text-align: center; margin-top: 15px; color: #16a34a; font-weight: bold; font-style: italic;">
                Thank You for Donating Rs.5
            </div>
            <div id="receipt-qr-target" style="text-align: center; margin-top: 15px;">
                <!-- QR Code will be appended here -->
            </div>
            <div style="text-align: center; margin-top: 10px; font-size: 12px;">
                Please show this receipt at the counter.
            </div>
        `;

        document.body.appendChild(receiptContainer);
        document.getElementById('receipt-qr-target').appendChild(qrClone);

        // Force a small delay to ensure rendering of the clone
        setTimeout(() => {
            html2canvas(receiptContainer, {
                scale: 2,
                backgroundColor: "#ffffff",
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = `Receipt_${orderData.token_id}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();

                // Cleanup
                document.body.removeChild(receiptContainer);
            }).catch(err => {
                console.error('Receipt generation failed:', err);
                // Fallback cleanup
                if (document.body.contains(receiptContainer)) {
                    document.body.removeChild(receiptContainer);
                }
            });
        }, 500);
    }, 1500); // Wait for main QR generation to finish
}

// Start new order
function newOrder() {
    cart = [];
    document.getElementById('customerName').value = '';
    updateCart();
    showPage('mainPage');
    loadMenu(); // Refresh menu
}

// Initialize app
loadMenu();
