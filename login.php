<?php
session_start();
<<<<<<< Updated upstream
=======
require_once 'config/database.php';

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    // Form field might be 'password' or 'password'
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check password: hash verification first, then fallback to plain text (legacy support)
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            $_SESSION['user_id'] = $user['UserId'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: documents.php');
            exit;
        }
        else {
            $error = "Invalid username or password.";
        }
    }
    else {
        $error = "Please fill in all fields.";
    }
}

>>>>>>> Stashed changes
// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header('Location: documents.php');
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

<div class="container" style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100vh; position: relative; z-index: 10;">
        <div class="auth-card">
            
<<<<<<< Updated upstream
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
=======
            <div class="auth-content">
                <h2>Document Logbook</h2>
                <p class="subtitle">Welcome! Please login!</p>
                
                <?php if (isset($error)): ?>
                    <div style="color: #ff6b6b; margin-bottom: 1rem; text-align: left; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></div>
                <?php
endif; ?>

                <form action="" method="POST">
                    <!-- Email/Username -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="username">Email Address</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Your Email Address Here">
                    </div>
                    
                    <!-- Password -->
                    <div style="margin-bottom: 0.5rem;">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Your Password Here">
                    </div>
                    
                    <!-- Buttons and Links Area -->
                    <div style="display: flex; align-items: center; justify-content: flex-start; gap: 2rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-auth">Login</button>
                        
                        <a href="signup.php" class="auth-link">Sign Up?</a>
                    </div>
                </form>
            </div>

            <!-- Decorative Logo Element (Bottom Right) -->
            <div class="auth-decoration">
                <!-- Using CSS to create a similar isometric shape pattern -->
                <div class="iso-box iso-box-1"></div>
                <div class="iso-box iso-box-2"></div>
            </div>

            <!-- Footer Links (absolute bottom right inside card) -->
            <div class="auth-footer">
                <a href="#">Privacy Policy</a> | <a href="#">System Status</a>
            </div>
>>>>>>> Stashed changes
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
