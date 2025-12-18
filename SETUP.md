# PLANTIANS Setup Guide

## Step-by-Step Setup Instructions

### Step 1: Install XAMPP

1. Download XAMPP from: https://www.apachefriends.org/
2. Run the installer
3. Install to default location: `C:\xampp`
4. Complete the installation

### Step 2: Start XAMPP Services

1. Open **XAMPP Control Panel** (search in Windows Start menu)
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both should show green "Running" status

### Step 3: Import Database

1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click **New** in the left sidebar
4. Enter database name: `foodfest`
5. Click **Create**
6. Click on the `foodfest` database in the left sidebar
7. Click the **Import** tab at the top
8. Click **Choose File**
9. Navigate to: `C:\xampp\htdocs\FoodFest\database.sql`
10. Click **Go** at the bottom
11. Wait for "Import has been successfully finished" message

### Step 4: Test on Laptop

1. Open browser
2. Go to: `http://localhost/FoodFest/admin/login.php`
3. Login with:
   - Username: `admin`
   - Password: `admin123`
4. You should see the admin dashboard

### Step 5: Find Your Local IP Address

1. Press `Windows Key + R`
2. Type: `cmd`
3. Press Enter
4. In the black window, type: `ipconfig`
5. Press Enter
6. Look for **IPv4 Address** under your Wi-Fi adapter
7. Write down this number (example: `192.168.1.10`)

### Step 6: Configure XAMPP for Network Access

1. Open file: `C:\xampp\apache\conf\extra\httpd-xampp.conf`
   - Right-click → Open with → Notepad
2. Find this line (around line 18):
   ```
   Require local
   ```
3. Replace it with:
   ```
   Require all granted
   ```
4. Save the file (Ctrl + S)
5. Go back to XAMPP Control Panel
6. Click **Stop** next to Apache
7. Click **Start** next to Apache again

### Step 7: Configure Windows Firewall

1. Open Windows Settings
2. Go to **Update & Security** → **Windows Security**
3. Click **Firewall & network protection**
4. Click **Allow an app through firewall**
5. Click **Change settings**
6. Find **Apache HTTP Server** in the list
7. Check both **Private** and **Public** boxes
8. Click **OK**

### Step 8: Test from Mobile Device

1. Connect your mobile phone to the **same Wi-Fi network** as your laptop
2. Open browser on mobile
3. Type in address bar: `http://YOUR_IP/FoodFest/order/`
   - Replace `YOUR_IP` with the IP address from Step 5
   - Example: `http://192.168.1.10/FoodFest/order/`
4. You should see the Food Fest ordering page

### Step 9: Share URL with Customers

Print or display this URL for customers:
```
http://YOUR_IP/FoodFest/order/
```

You can also create a QR code for this URL using any free QR code generator online.

## Quick Reference

### Admin Access (Laptop)
- **URL:** `http://localhost/FoodFest/admin/login.php`
- **Username:** `admin`
- **Password:** `admin123`

### Customer Access (Mobile)
- **URL:** `http://YOUR_IP/FoodFest/order/`
- Replace `YOUR_IP` with your laptop's local IP address

## Common Issues

### Issue: Cannot access from mobile
**Solution:**
- Make sure mobile and laptop are on same Wi-Fi
- Check if Apache is running in XAMPP
- Verify firewall settings (Step 7)
- Try turning off Windows Firewall temporarily to test

### Issue: Database error
**Solution:**
- Make sure MySQL is running in XAMPP
- Re-import database.sql file (Step 3)
- Check database name is exactly `foodfest` (lowercase)

### Issue: Login not working
**Solution:**
- Clear browser cache
- Make sure you imported the database
- Check username is `admin` and password is `admin123`

### Issue: Items not showing
**Solution:**
- Login to admin panel
- Go to "Manage Items"
- Add some food items first
- Make sure items are marked as "Available"

## Tips

1. **Keep XAMPP running** during the entire event
2. **Don't close XAMPP Control Panel** - just minimize it
3. **Test everything** before the event starts
4. **Have a backup laptop** if possible
5. **Print the customer URL** as a QR code for easy access

## Need Help?

1. Check the main README.md file for detailed documentation
2. Look at XAMPP error logs: `C:\xampp\apache\logs\error.log`
3. Make sure all steps were followed exactly

---

**You're all set! Good luck with your PLANTIANS event! 🎉**
