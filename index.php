<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Logger</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<<<<<<< Updated upstream
    <h1>Welcome to Document Logger</h1>
    <p>User: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        
=======
    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h1>
            <nav>
                <a href="documents.php" class="btn btn-primary">View Documents</a>
                <a href="add_document.php" class="btn btn-secondary">Add Document</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                    <!-- Admin Only Link -->
                    <a href="register_staff.php" class="btn btn-secondary" style="background-color: #e74c3c;">Add Staff</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </nav>
        </header>

        <main>
            <p>Select an option above to manage documents.</p>
        </main>
    </div>

    <!-- Scripts -->
>>>>>>> Stashed changes
    <script src="assets/js/main.js"></script>
</body>
</html>