# 🍔 PLANTIANS - Local Food Ordering System

A complete local food ordering system for school food fests, running on XAMPP (Apache + MySQL + PHP) over Wi-Fi with no internet required.

## 📋 Features

### Admin Interface (Laptop)
- ✅ Secure login with session management
- ✅ Dashboard with real-time statistics
- ✅ Food item management (Add, Edit, Delete, Toggle availability)
- ✅ Order management with status tracking
- ✅ Real-time order updates (auto-refresh every 5 seconds)
- ✅ Sound alerts for new orders
- ✅ QR code display for order verification

### Customer Interface (Mobile Browser)
- ✅ Mobile-optimized ordering page
- ✅ Dynamic menu display
- ✅ Shopping cart with quantity controls
- ✅ Simple checkout process
- ✅ Order token generation (Format: PL2025-XXX)
- ✅ QR code generation for order pickup

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Windows PC (for admin interface)
- Mobile devices on same Wi-Fi network (for customers)

### Installation

1. **Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install to default location (C:\xampp)

2. **Copy Project Files**
   - Copy the `FoodFest` folder to `C:\xampp\htdocs\`
   - Final path should be: `C:\xampp\htdocs\FoodFest\`

3. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache
   - Start MySQL

4. **Create Database**
   - Open browser and go to: `http://localhost/phpmyadmin`
   - Click "New" to create a database
   - Name it: `foodfest`
   - Click "Import" tab
   - Choose file: `C:\xampp\htdocs\FoodFest\database.sql`
   - Click "Go" to import

5. **Test Local Access**
   - Admin: `http://localhost/FoodFest/admin/login.php`
   - Customer: `http://localhost/FoodFest/order/`
   - Default admin login: `admin` / `admin123`

## 🌐 Network Setup (For Multiple Devices)

### Find Your Local IP Address

**Windows:**
1. Press `Win + R`
2. Type `cmd` and press Enter
3. Type `ipconfig` and press Enter
4. Look for "IPv4 Address" under your Wi-Fi adapter
5. Example: `192.168.1.10`

### Configure XAMPP for Network Access

1. **Edit Apache Configuration**
   - Open: `C:\xampp\apache\conf\extra\httpd-xampp.conf`
   - Find the section with `Require local`
   - Change to:
   ```apache
   Require all granted
   ```
   - Save the file

2. **Restart Apache**
   - In XAMPP Control Panel, stop Apache
   - Start Apache again

3. **Configure Firewall**
   - Open Windows Firewall
   - Allow Apache HTTP Server through firewall
   - Allow port 80 (HTTP)

### Access from Mobile Devices

1. **Connect to Same Wi-Fi**
   - Ensure all devices are on the same Wi-Fi network

2. **Access URLs**
   - Replace `localhost` with your local IP
   - Admin (Laptop): `http://192.168.1.10/FoodFest/admin/login.php`
   - Customer (Mobile): `http://192.168.1.10/FoodFest/order/`
   - Replace `192.168.1.10` with your actual IP address

## 📱 Usage Guide

### For Administrators

1. **Login**
   - Open admin URL on laptop
   - Login with: `admin` / `admin123`
   - **IMPORTANT:** Change password after first login!

2. **Manage Food Items**
   - Click "Manage Items"
   - Add new items with name and price
   - Toggle availability for out-of-stock items
   - Edit or delete items as needed

3. **Monitor Orders**
   - Click "Manage Orders"
   - View all incoming orders in real-time
   - Update order status: Pending → Preparing → Completed
   - Click "View Details" to see full order information
   - QR code is displayed for verification

4. **Dashboard**
   - View statistics (total, pending, preparing, completed)
   - See recent orders
   - Quick access to management pages

### For Customers

1. **Browse Menu**
   - Open customer URL on mobile browser
   - View available food items with prices

2. **Add to Cart**
   - Click "Add" button on desired items
   - Use +/- buttons to adjust quantity
   - View cart summary at bottom

3. **Checkout**
   - Click "Proceed to Checkout"
   - Review your order
   - Enter your name
   - Click "Place Order"

4. **Get Token**
   - Receive unique token number (e.g., FF2025-001)
   - QR code is generated automatically
   - Screenshot or show QR code for order pickup

## 🗂️ Project Structure

```
FoodFest/
├── admin/                  # Admin interface
│   ├── css/
│   │   └── admin.css      # Admin styles
│   ├── js/
│   │   └── admin.js       # Admin JavaScript
│   ├── index.php          # Dashboard
│   ├── login.php          # Login page
│   ├── items.php          # Item management
│   ├── orders.php         # Order management
│   └── logout.php         # Logout handler
├── api/                    # Backend API endpoints
│   ├── auth.php           # Authentication
│   ├── items.php          # Items CRUD
│   ├── orders.php         # Orders CRUD
│   └── qrcode.php         # QR code generation
├── config/                 # Configuration files
│   ├── database.php       # Database connection
│   └── session.php        # Session management
├── includes/               # Utility functions
│   └── functions.php      # Helper functions
├── order/                  # Customer interface
│   ├── css/
│   │   └── style.css      # Customer styles
│   ├── js/
│   │   └── app.js         # Customer JavaScript
│   └── index.php          # Ordering page
├── assets/                 # Static assets
│   └── sounds/
│       └── notification.mp3
└── database.sql           # Database schema
```

## 🔧 Configuration

### Database Settings
Edit `config/database.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'foodfest');
```

### Change Admin Password
1. Login to admin panel
2. Use phpMyAdmin to update password:
   - Go to `http://localhost/phpmyadmin`
   - Select `foodfest` database
   - Click on `admins` table
   - Edit the admin row
   - Use PHP to generate new password hash:
   ```php
   <?php echo password_hash('your_new_password', PASSWORD_DEFAULT); ?>
   ```

## 🐛 Troubleshooting

### Cannot Access from Mobile
- Check if devices are on same Wi-Fi network
- Verify firewall allows Apache
- Ensure Apache is configured for network access
- Try disabling Windows Firewall temporarily to test

### Database Connection Error
- Verify MySQL is running in XAMPP
- Check database name is `foodfest`
- Ensure database.sql was imported correctly

### Orders Not Appearing
- Check browser console for errors
- Verify API endpoints are accessible
- Clear browser cache and refresh

### QR Code Not Displaying
- Ensure internet connection for Google Charts API
- Check browser console for errors
- Try different browser

## 📊 Database Schema

### Tables
- **admins**: Admin user credentials
- **items**: Food items with prices and availability
- **orders**: Order headers with token, customer, total, status
- **order_items**: Individual items in each order

## 🔒 Security Notes

- Change default admin password immediately
- Use HTTPS in production (requires SSL certificate)
- Implement rate limiting for API endpoints
- Add CSRF protection for forms
- Sanitize all user inputs (already implemented)

## 📝 License

This project is created for educational purposes for school food fests.

## 👥 Support

For issues or questions:
1. Check troubleshooting section
2. Review code comments
3. Check XAMPP error logs: `C:\xampp\apache\logs\error.log`

## 🎉 Credits

Built with:
- PHP 7.4+
- MySQL 5.7+
- Vanilla JavaScript
- Google Charts API (for QR codes)

---

**Enjoy your Food Fest! 🍔🍕🍟**
