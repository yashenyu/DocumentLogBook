<?php
session_start();
// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Document LogBook</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <!-- Static Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="container" style="display: flex; justify-content: center; align-items: center; width: 100%; position: relative; z-index: 10;">
        <div class="auth-card">
            <h2>Document Logbook</h2>
            <p class="subtitle">Welcome! Please login!</p>
            
            <form action="auth_login.php" method="POST">
                <!-- Email/Username -->
                <div>
                    <label for="username">Email Address</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Your Email Address Here">
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Your Password Here">
                </div>
                
                <!-- Footer area with Button and Link -->
                <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <button type="submit" class="btn btn-auth">Login</button>
                    <a href="signup.php" class="auth-footer-link" style="float: none; margin: 0;">Sign Up?</a>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
