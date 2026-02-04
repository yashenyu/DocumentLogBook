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
            header('Location: index.php');
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
            
            <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
            <?php
endif; ?>

            <form action="" method="POST">
                <!-- Email/Username -->
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter Username">
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter Password">
                </div>
                
                <!-- Footer area with Button -->
                <div style="margin-top: 1rem; display: flex; justify-content: center; align-items: center;">
                    <button type="submit" class="btn btn-auth" style="width: 100%;">Login</button>
                    <!-- <a href="signup.php" class="auth-footer-link" style="float: none; margin: 0;">Sign Up?</a> Public Signup Disabled -->
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
