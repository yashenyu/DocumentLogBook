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
    <title>Documents - Document LogBook</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Table styles specific to this page for now */
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; background-color: #f1f5f9; }
        .search-bar { margin-bottom: 1.5rem; }
        .pagination { display: flex; gap: 0.5rem; justify-content: flex-end; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Documents</h1>
            <div>
                <a href="add_document.php" class="btn btn-primary">Add New Document</a>
                <a href="index.php" class="btn btn-secondary">Dashboard</a>
            </div>
        </header>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" placeholder="Search documents..." 
                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
        </div>

        <!-- Documents Table -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Doc Name</th>
                            <th>Description</th>
                            <th>Received By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Empty body as requested, will be filled with data later -->
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b;">No documents found (Placeholder)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Placeholder -->
        <div class="pagination" style="margin-top: 1.5rem;">
            <button class="btn btn-secondary" disabled>Previous</button>
            <button class="btn btn-secondary" disabled>1</button>
            <button class="btn btn-secondary">Next</button>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
