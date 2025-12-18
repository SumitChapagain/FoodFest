@echo off
echo ================================================
echo PLANTIANS - Network Access Configuration Helper
echo ================================================
echo.

echo Your laptop's IP address:
ipconfig | findstr /i "IPv4"
echo.

echo ================================================
echo Step 1: Checking Apache Configuration
echo ================================================
echo.

set "APACHE_CONF=C:\xampp\apache\conf\extra\httpd-xampp.conf"

if exist "%APACHE_CONF%" (
    echo Found Apache configuration file.
    echo Location: %APACHE_CONF%
    echo.
    echo Checking current configuration...
    findstr /n "Require local" "%APACHE_CONF%"
    findstr /n "Require all granted" "%APACHE_CONF%"
    echo.
) else (
    echo ERROR: Apache configuration file not found!
    echo Expected location: %APACHE_CONF%
    pause
    exit /b 1
)

echo ================================================
echo Step 2: Checking Windows Firewall Status
echo ================================================
echo.

netsh advfirewall show allprofiles state
echo.

echo ================================================
echo Step 3: Checking if Apache is allowed through firewall
echo ================================================
echo.

netsh advfirewall firewall show rule name=all | findstr /i "apache"
echo.

echo ================================================
echo INSTRUCTIONS TO FIX MOBILE ACCESS:
echo ================================================
echo.
echo 1. CONFIGURE APACHE:
echo    - Open: %APACHE_CONF%
echo    - Find the line that says: "Require local"
echo    - Replace it with: "Require all granted"
echo    - Save the file
echo.
echo 2. RESTART APACHE:
echo    - Open XAMPP Control Panel
echo    - Click Stop next to Apache
echo    - Click Start next to Apache
echo.
echo 3. CONFIGURE FIREWALL:
echo    - Run this script as Administrator, OR
echo    - Manually add Apache to Windows Firewall exceptions
echo.
echo 4. TEST FROM MOBILE:
echo    - Connect mobile to same Wi-Fi as laptop
echo    - Open browser on mobile
echo    - Go to: http://192.168.1.254/FoodFest/order/
echo.
echo ================================================
pause
