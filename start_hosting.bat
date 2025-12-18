@echo off
title PLANTIANS Hosting Setup
echo ========================================================
echo          PLANTIANS HOSTING SETUP (INTERNET)
echo ========================================================
echo.
echo Phase 1: Authentication (One time only)
echo ---------------------------------------
echo 1. Go to: https://dashboard.ngrok.com/signup
echo 2. Login/Signup with Google
echo 3. Copy your "Authtoken" from the dashboard
echo.
echo If you have already done this, just press Enter to skip.
echo.
set /p token=Paste your Authtoken here (or press Enter to skip): 

if not "%token%"=="" (
    ngrok config add-authtoken %token%
    echo.
    echo Token saved!
)

echo.
echo Phase 2: Starting Server
echo ------------------------
echo Starting Ngrok Tunnel...
echo.
echo INSTRUCTIONS FOR MOBILE:
echo 1. Look for the line that says "Forwarding    https://xxxx.ngrok-free.app -> http://localhost:80"
echo 2. Type that highlighted URL into your mobile browser.
echo 3. Enjoy!
echo.
echo (Keep this window OPEN while using the app)
echo.
ngrok http 80
pause
