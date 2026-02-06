<?php
session_start();
require_once 'config/database.php';

// Check if any users exist
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$userCount = $stmt->fetchColumn();

// If users already exist, redirect to login
if ($userCount > 0) {
    header('Location: login.php');
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($username) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            try {
                // Initial Admin account creation
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Admin')");

                if ($stmt->execute([$username, $hashed])) {
                    $_SESSION['success'] = "First Admin account created successfully! You can now login.";
                    header('Location: login.php');
                    exit;
                }
                else {
                    $error = "Failed to create account.";
                }
            }
            catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
        else {
            $error = "Passwords do not match.";
        }
    }
    else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Setup - Document LogBook</title>
    
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
                <h2>Initial Setup</h2>
                <p class="subtitle">Create the first Admin account!</p>
                
                <?php if (isset($error)): ?>
                    <div style="color: #ff6b6b; margin-bottom: 1rem; text-align: left; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></div>
                <?php
endif; ?>

                <form action="" method="POST">
                    <!-- Username -->
                    <div style="margin-bottom: 1rem;">
                        <label for="username">Admin Username</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Set Admin Username">
                    </div>
                    
                    <!-- Password -->
                    <div style="margin-bottom: 1rem;">
                        <label for="password">Admin Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Set Admin Password">
                    </div>

                    <!-- Confirm Password -->
                    <div style="margin-bottom: 0.5rem;">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm Admin Password">
                    </div>
                    
                    <!-- Buttons Area -->
                    <div style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
                        <button type="submit" class="btn btn-auth">Create Admin</button>
                    </div>
                </form>
            </div>

            <div class="auth-decoration" style="position: absolute; bottom: 1.5rem; right: 1.5rem; width: 110px; height: 110px; z-index: 5;">
                <!-- User Logo Image -->
                <img src="assets/images/HAU.png" alt="Logo" class="auth-logo-img" style="width: 100%; height: 100%; object-fit: contain;">
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
