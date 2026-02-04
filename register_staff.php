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
    // Optional: Auto-generate password or let Admin set it? Let's let Admin set it based on previous flow.
    $password = $_POST['password'] ?? ''; 
    $role = $_POST['role'] ?? 'Staff';

    if (!empty($username) && !empty($password)) {
        // Check availability
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already exists.";
        } else {
            // Hash and Insert
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed, $role])) {
                $success = "User '$username' created successfully!";
            } else {
                $error = "Database error.";
            }
        }
    } else {
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <!-- Static Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="container" style="display: flex; justify-content: center; align-items: center; width: 100%; position: relative; z-index: 10; flex-direction: column;">
        
        <div class="auth-card" style="margin-top: 2rem;">
            <h2>Add New Staff</h2>
            <p class="subtitle">Create a new account for your team</p>

            <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div style="color: green; margin-bottom: 1rem; text-align: center;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <!-- Username -->
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter Username" style="width: 100%;">
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password">Password</label>
                    <input type="text" id="password" name="password" required 
                           placeholder="Set Initial Password" style="width: 100%;">
                </div>

                <!-- Role -->
                <div>
                    <label for="role">Role</label>
                    <select id="role" name="role" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; margin-top: 0.5rem;">
                        <option value="Staff">Staff</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                
                <!-- Actions -->
                <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    <button type="submit" class="btn btn-auth" style="width: 100%;">Create User</button>
                    <a href="index.php" style="color: #cbd5e1; text-decoration: none; font-size: 0.9rem; text-align: center; display: block;">&larr; Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>