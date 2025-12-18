<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PLANTIANS - Order Now</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Main Page -->
    <div id="mainPage" class="page active">
        <header class="header">
            <div class="brand-wrapper">
                <h1 class="brand-title">🍔 PLANTIANS</h1>
                <p class="tagline">Order your favorite food!</p>
            </div>
        </header>
        
        <div class="container">
            <!-- Menu Items -->
            <section class="menu-section">
                <h2>Menu</h2>
                <div id="menuItems" class="menu-grid">
                    <!-- Items will be loaded here -->
                    <div class="loading">Loading menu...</div>
                </div>
            </section>
            
            <!-- Cart Summary -->
            <div id="cartSummary" class="cart-summary" style="display: none;">
                <div class="cart-header">
                    <h3>Your Order</h3>
                    <button class="btn-clear" onclick="clearCart()">Clear</button>
                </div>
                <div id="cartItems" class="cart-items"></div>
                <div class="cart-total">
                    <span>Total:</span>
                    <span id="cartTotal" class="total-price">Rs. 0.00</span>
                </div>
                <button class="btn btn-primary btn-block" onclick="showCheckout()">
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
    
    <!-- Checkout Page -->
    <div id="checkoutPage" class="page">
        <header class="header">
            <button class="btn-back" onclick="backToMenu()">← Back</button>
            <h1>Checkout</h1>
        </header>
        
        <div class="container">
            <div class="checkout-section">
                <h2>Order Summary</h2>
                <div id="checkoutItems" class="checkout-items"></div>
                <div class="checkout-total">
                    <span>Total Amount:</span>
                    <span id="checkoutTotal" class="total-price">Rs. 0.00</span>
                </div>
            </div>
            
            <div class="checkout-section">
                <h2>Your Details</h2>
                <form id="checkoutForm">
                    <div class="form-group">
                        <label for="customerName">Your Name</label>
                        <input type="text" id="customerName" class="form-control" required placeholder="Enter your name">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Order Confirmation Page -->
    <div id="confirmationPage" class="page">
        <header class="header" style="display: none;">
            <h1>Order Placed! 🎉</h1>
        </header>
        
        <div class="container">
            <div class="confirmation-card">
                <div class="success-icon">✓</div>
                <h2>Thank you for your order!</h2>
                
                <div class="token-display">
                    <p class="token-label">Your Token Number</p>
                    <h1 id="tokenNumber" class="token-number">PL2025-001</h1>
                </div>
                
                <div class="qr-section">
                    <p>Show this QR code to collect your order</p>
                    <div id="qrCode" class="qr-code">
                        <!-- QR code will be displayed here -->
                    </div>
                </div>
                
                <div class="order-details">
                    <h3>Order Details</h3>
                    <div id="confirmationItems"></div>
                    <div class="confirmation-total">
                        <span>Total Paid:</span>
                        <span id="confirmationTotal" class="total-price">Rs. 0.00</span>
                    </div>
                </div>
                
                <button class="btn btn-primary btn-block" onclick="newOrder()">
                    Place New Order
                </button>
            </div>
        </div>
    </div>
    
    <script src="js/qrcode.min.js"></script>
    <script src="js/html2canvas.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
