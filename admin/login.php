<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PLANTIANS</title>
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="../assets/images/logo.png" alt="Food Fest" style="width: 80px; margin-bottom: 1rem;">
                <h1>PLANTIANS</h1>
                <p>Admin Portal</p>
            </div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="login-info">
                <p><small>Default credentials: admin / admin123</small></p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');
            
            try {
                const response = await fetch('/FoodFest/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorMessage.textContent = data.message;
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                errorMessage.textContent = 'Login failed. Please try again.';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>
