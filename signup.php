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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 450px; margin-top: 3rem;">
        <div class="card">
            <h2 style="margin-bottom: 0.5rem; text-align: center;">Create Account</h2>
            <p style="text-align: center; color: #64748b; margin-bottom: 2rem;">Document LogBook</p>
            
            <form action="auth_register.php" method="POST">
                <!-- Username -->
                <div style="margin-bottom: 1rem;">
                    <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username</label>
                    <input type="text" id="username" name="username" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           placeholder="Choose a username">
                </div>

                <!-- Email -->
                <div style="margin-bottom: 1rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">HAU Email Address</label>
                    <input type="email" id="email" name="email" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           placeholder="name@example.com">
                </div>
                
                <!-- Password -->
                <div style="margin-bottom: 1rem;">
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
                    <input type="password" id="password" name="password" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           placeholder="Create a strong password">
                </div>

                <!-- Confirm Password -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           placeholder="Repeat your password">
                </div>
                
                <!-- Submit -->
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                <p>Already have an account? <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Sign In</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
