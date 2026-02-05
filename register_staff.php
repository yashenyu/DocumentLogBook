<?php
session_start();
require_once 'config/database.php';

// Access Control: Only Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'Staff';

    if (!empty($username) && !empty($password)) {
        // Check availability
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already exists.";
        }
        else {
            // Hash and Insert
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed, $role])) {
                $success = "User '$username' created successfully!";
            }
            else {
                $error = "Database error.";
            }
        }
    }
    else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff - Document LogBook</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page" style="display: flex; overflow-y: auto;">
    <!-- Static Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="container" style="max-width: 500px; position: relative; z-index: 10;">
        <div class="auth-card" style="display: block;">
            <div class="auth-content">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <h2 style="margin-bottom: 0;">Add New Staff</h2>
                </div>
                <p class="subtitle">Create a new account for your team member.</p>

                <?php if (isset($error)): ?>
                    <div style="background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #ff8a80; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php
endif; ?>
                <?php if (isset($success)): ?>
                    <div style="background: rgba(52, 211, 153, 0.2); border: 1px solid #34d399; color: #a7f3d0; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php
endif; ?>

                <form action="" method="POST">
                    <div style="margin-bottom: 1.2rem;">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="Enter Username">
                    </div>
                    
                    <div style="margin-bottom: 1.2rem;">
                        <label for="password">Initial Password</label>
                        <input type="text" id="password" name="password" required placeholder="Set Password">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="role">Role</label>
                        <select id="role" name="role" style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 0.375rem; background-color: #ffffff; color: #334155; font-size: 0.9rem; font-family: 'Poppins', sans-serif;">
                            <option value="Staff">Staff</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-auth" style="width: 100%;">Create User</button>
                        <a href="documents.php" class="auth-link" style="text-align: center; text-decoration: none; font-size: 0.85rem;">Cancel and Go Back</a>
                    </div>
                </form>
            </div>

            <!-- Decorative Logo Element -->
            <div class="auth-decoration" style="position: absolute; bottom: -23px; right: -12px; width: 140px; height: 140px; z-index: 5;">
                <img src="assets/images/Logbook Logo.png" alt="Logo" class="auth-logo-img" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>