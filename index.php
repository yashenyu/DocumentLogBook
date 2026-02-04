<?php
session_start();

// Simple check for login, redirect if not logged in
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
    <title>Dashboard - Document LogBook</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h1>
            <nav>
                <a href="documents.php" class="btn btn-primary">View Documents</a>
                <a href="add_document.php" class="btn btn-secondary">Add Document</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </nav>
        </header>

        <main>
            <p>Select an option above to manage documents.</p>
        </main>
    </div>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>