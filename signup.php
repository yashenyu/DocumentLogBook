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
    <title>Sign Up - Document LogBook</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <!-- Static Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Interactive Background Canvas -->
    <canvas id="bgCanvas"></canvas>

    <div class="container" style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100vh; position: relative; z-index: 10;">
        <div class="auth-card">
            
            <div class="auth-content">
                <h2>Document Logbook</h2>
                <p class="subtitle">Welcome! Please Signup!</p>
                
                <form action="auth_register.php" method="POST">
                    <!-- Username -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Choose a Username" maxlength="100">
                    </div>

                    <!-- Email -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Your Email Address Here">
                    </div>
                    
                    <!-- Password -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Your Password Here">
                    </div>

                    <!-- Confirm Password -->
                    <div style="margin-bottom: 0.5rem;">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Re-enter Your Password Here">
                    </div>
                    
                    <!-- Buttons and Links Area -->
                    <div style="display: flex; align-items: center; justify-content: flex-start; gap: 2rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-auth">Signup</button>
                        
                        <a href="login.php" class="auth-link">Login?</a>
                    </div>
                </form>
            </div>

            <!-- Decorative Logo Element (Bottom Right) -->
            <div class="auth-decoration" style="position: absolute; bottom: -20px; right: -15px; width: 160px; height: 160px; z-index: 5;">
                <!-- User Logo Image -->
                <img src="assets/images/Logbook Logo.png" alt="Logo" class="auth-logo-img" style="width: 100%; height: 100%; object-fit: contain;">
            </div>

            <!-- Footer Links (absolute bottom right inside card) -->
            <div class="auth-footer">
                <a href="#">Privacy Policy</a> | <a href="#">System Status</a>
            </div>
        </div>
    </div>

    <script src="assets/js/interactive-bg.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
