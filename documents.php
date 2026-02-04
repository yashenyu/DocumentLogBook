<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Params
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$sort = $_GET['sort'] ?? 'DESC';

$nextSort = ($sort === 'DESC') ? 'ASC' : 'DESC';

// Build Query
$whereSQL = "1=1";
$params = [];

if (!empty($search)) {
    $whereSQL .= " AND (Subject LIKE ? OR Description LIKE ? OR Office LIKE ? OR Status LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term, $term];
}

// Count Total
$countSql = "SELECT COUNT(*) FROM DocumentLog WHERE $whereSQL";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalDocs = $stmt->fetchColumn();
$totalPages = ceil($totalDocs / $limit);

// Fetch Data
$sql = "SELECT * FROM DocumentLog WHERE $whereSQL ORDER BY DocDate $sort, DocID $sort LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
                <div class="nav-iso-box-container">
                    <div class="nav-iso-box nav-iso-1"></div>
                    <div class="nav-iso-box nav-iso-2"></div>
                </div>
            </div>
            <div class="nav-divider"></div>
            <div class="nav-subtitle">Simple Document Logbook | <?php echo htmlspecialchars($_SESSION['role'] ?? 'Staff'); ?></div>
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
        
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: rgba(46, 204, 113, 0.2); border: 1px solid #2ecc71; color: #a2f2c2; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['success'];
    unset($_SESSION['success']); ?>
            </div>
        <?php
endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #ff8a80; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['error'];
    unset($_SESSION['error']); ?>
            </div>
        <?php
endif; ?>

        <!-- Toolbar -->
        <div class="doc-toolbar">
            <form action="" method="GET" style="flex: 1; max-width: 400px;">
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search by name, office, etc..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </form>
            
            <div class="toolbar-buttons">
                <!-- Filter Button toggles Date Sort -->
                <a href="?search=<?php echo urlencode($search); ?>&sort=<?php echo $nextSort; ?>" class="btn-dark" style="text-decoration: none;">
                    <i class="fa-solid fa-filter"></i> Sort <?php echo($sort == 'DESC') ? 'Newest' : 'Oldest'; ?>
                </a>
                
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
                        <th>ID</th>
                        <th>Preview</th>
                        <th>Date</th>
                        <th>Office</th>
                        <th>Subject</th>
                        <th>Received By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($documents) > 0): ?>
                        <?php foreach ($documents as $row): ?>
                        <tr>
                            <td>#<?php echo $row['DocID']; ?></td>
                            <td>
                                <?php if (!empty($row['DocImage']) && file_exists($row['DocImage'])): ?>
                                    <i class="fa-regular fa-file-image" title="Has Attachment"></i>
                                <?php
        else: ?>
                                    <span style="opacity: 0.3;">-</span>
                                <?php
        endif; ?>
                            </td>
                            <td><?php echo date('m/d/Y', strtotime($row['DocDate'])); ?></td>
                            <td><?php echo htmlspecialchars($row['Office']); ?></td>
                            <td><?php echo htmlspecialchars($row['Subject']); ?></td>
                            <td><?php echo htmlspecialchars($row['ReceivedBy']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['Status']); ?>">
                                    <?php echo htmlspecialchars($row['Status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if (!empty($row['DocImage'])): ?>
                                        <a href="download_document.php?id=<?php echo $row['DocID']; ?>" class="action-btn" title="Download"><i class="fa-solid fa-download"></i></a>
                                    <?php
        else: ?>
                                        <button class="action-btn disabled" style="opacity: 0.5; cursor: not-allowed;"><i class="fa-solid fa-download"></i></button>
                                    <?php
        endif; ?>
                                    
                                    <a href="delete_document.php?id=<?php echo $row['DocID']; ?>" class="action-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this document?');"><i class="fa-regular fa-trash-can"></i></a>
                                    
                                    <a href="edit_document.php?id=<?php echo $row['DocID']; ?>" class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php
    endforeach; ?>
                    <?php
else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">No documents found.</td>
                        </tr>
                    <?php
endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-area">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-nav"><i class="fa-solid fa-chevron-left"></i></a>
            <?php
else: ?>
                <div class="page-nav disabled"><i class="fa-solid fa-chevron-left"></i></div>
            <?php
endif; ?>

            <div class="pagination-links">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-link <?php echo($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php
endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-nav"><i class="fa-solid fa-chevron-right"></i></a>
            <?php
else: ?>
                <div class="page-nav disabled"><i class="fa-solid fa-chevron-right"></i></div>
            <?php
endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
