<?php
session_start();
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <!-- Centering Wrapper -->
    <div class="auth-wrapper">
        <div class="auth-card">
            
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
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter Your Username">
                    </div>
                    
                    <!-- Password -->
                    <div style="margin-bottom: 0.5rem;">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Your Password Here">
                    </div>
                    
                    <!-- Buttons and Links Area -->
                    <div style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
                        <button type="submit" class="btn btn-auth">Login</button>
                    </div>
                </form>
            </div>

            <div class="auth-decoration" style="position: absolute; bottom: 3.5rem; right: 2rem; width: 140px; height: 140px; z-index: 5;">
                <!-- User Logo Image -->
                <img src="assets/images/HAU.png" alt="Logo" class="auth-logo-img" style="width: 110%; height: 125%; object-fit: contain;">
            </div>

            <!-- Footer Links (absolute bottom right inside card) -->
            <div class="auth-footer" style="bottom: 1.5rem; right: 2.5rem; text-align: right;">
                <a href="#">Privacy Policy</a>
            </div>
        </div>
    </div>
    
    <!-- Interactive Background Canvas -->
    <canvas id="bgCanvas"></canvas>

    <!-- Background Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <div class="blob blob-4"></div>

    <script src="assets/js/interactive-bg.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
