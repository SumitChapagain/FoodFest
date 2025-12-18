@echo off
echo ================================================
echo Adding Apache to Windows Firewall
echo ================================================
echo.
echo This script must be run as Administrator!
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script requires Administrator privileges.
    echo Please right-click and select "Run as Administrator"
    pause
    exit /b 1
)

echo Adding firewall rules for Apache HTTP Server...
echo.

REM Add inbound rule for Apache on port 80
netsh advfirewall firewall add rule name="Apache HTTP Server (Port 80)" dir=in action=allow protocol=TCP localport=80 program="C:\xampp\apache\bin\httpd.exe" enable=yes

REM Add inbound rule for Apache on port 443 (HTTPS)
netsh advfirewall firewall add rule name="Apache HTTPS Server (Port 443)" dir=in action=allow protocol=TCP localport=443 program="C:\xampp\apache\bin\httpd.exe" enable=yes

echo.
echo ================================================
echo Firewall rules added successfully!
echo ================================================
echo.
echo Apache HTTP Server is now allowed through Windows Firewall.
echo.
echo Next steps:
echo 1. Make sure Apache configuration allows network access
echo 2. Restart Apache in XAMPP Control Panel
echo 3. Test from mobile: http://192.168.1.254/FoodFest/order/
echo.
pause
