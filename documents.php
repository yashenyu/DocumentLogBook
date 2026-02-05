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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar" style="justify-content: space-between;">
        <div style="display: flex; align-items: center;">
            <div class="logo-area">
                <span class="logo-text">LogBook</span>
                <div class="nav-iso-box-container" style="display: flex; align-items: center; justify-content: center;">
                    <img src="assets/images/Logbook Logo.png" alt="Logo" style="width: 150%; height: 150%; object-fit: contain;">
                </div>
            </div>
            <div class="nav-divider"></div>
            <div class="nav-subtitle" style="display: flex; align-items: center; gap: 0.6rem;">
                Simple Document Logbook 
                <span style="opacity: 0.2;">|</span>
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                    <span style="background-color: rgba(52, 211, 153, 0.1); color: #34d399; padding: 2px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; border: 1px solid rgba(52, 211, 153, 0.2); letter-spacing: 0.5px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px;">
                        <span style="width: 5px; height: 5px; background-color: #34d399; border-radius: 50%;"></span>
                        Admin
                    </span>
                <?php
else: ?>
                    <span style="color: #94a3b8; font-size: 0.8rem; font-weight: 500;">Staff</span>
                <?php
endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="register_staff.php" class="btn-dark" style="text-decoration: none; font-size: 0.9rem; padding: 0.4rem 1rem; background-color: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #ff8a80;">
                    <i class="fa-solid fa-user-plus"></i> Add Staff
                </a>
            <?php
endif; ?>
            <a href="logout.php" class="btn-dark" style="text-decoration: none; font-size: 0.9rem; padding: 0.4rem 1rem;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Toolbar -->
        <div class="doc-toolbar">
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="search-input" placeholder="Search">
            </div>
            
            <div class="toolbar-buttons">
                <button class="btn-dark">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="add_document.php" class="btn-dark" style="text-decoration: none;">
                    <i class="fa-solid fa-plus"></i> Add
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Document id</th>
                        <th>Image</th>
                        <th>Date</th>
                        <th>Office</th>
                        <th>Description</th>
                        <th>Received By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Placeholder Rows matching the image -->
                    <?php for ($i = 0; $i < 8; $i++): ?>
                    <tr>
                        <td>Document id</td>
                        <td></td>
                        <td>12/05/2025</td>
                        <td>SOC</td>
                        <td>SOC</td>
                        <td>SOC</td>
                        <td>SOC</td>
                        <td>
                            <div class="actions">
                                <button class="action-btn" title="Download"><i class="fa-solid fa-download"></i></button>
                                <button class="action-btn" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
                                <button class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php
endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-area">
            <div class="page-nav"><i class="fa-solid fa-chevron-left"></i></div>
            <div class="pagination-links">
                <a href="#" class="page-link active">1</a>
                <a href="#" class="page-link">2</a>
                <span class="page-link">...</span>
                <a href="#" class="page-link">3</a>
                <a href="#" class="page-link">4</a>
            </div>
            <div class="page-nav"><i class="fa-solid fa-chevron-right"></i></div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
