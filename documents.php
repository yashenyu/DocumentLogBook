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
                <div class="search-container" style="position: relative; display: flex; align-items: center;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; color: #94a3b8; font-size: 0.9rem;"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search by name, office, etc..." value="<?php echo htmlspecialchars($search); ?>" style="padding-left: 40px; width: 100%;">
                </div>
            </form>
            
            <div class="toolbar-buttons">
                <!-- Filter Button toggles Date Sort -->
                <a href="?search=<?php echo urlencode($search); ?>&sort=<?php echo $nextSort; ?>" class="btn-dark" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-filter" style="font-size: 0.9rem; color: #ffffff;"></i>
                    Sort <?php echo($sort == 'DESC') ? 'Newest' : 'Oldest'; ?>
                </a>
                
                <a href="add_document.php" class="btn-dark" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-plus" style="font-size: 0.9rem; color: #ffffff;"></i>
                    Add
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
                                <?php
        if (!empty($row['DocImage']) && file_exists($row['DocImage'])):
            $ext = strtolower(pathinfo($row['DocImage'], PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
?>
                                    <a href="<?php echo htmlspecialchars($row['DocImage']); ?>" target="_blank" class="action-btn" title="View Document">
                                        <?php if ($isImage): ?>
                                            <img src="<?php echo htmlspecialchars($row['DocImage']); ?>" alt="Preview" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        <?php
            else: ?>
                                            <i class="fa-regular fa-file-pdf" style="font-size: 1.5rem; color: #ef4444;"></i>
                                        <?php
            endif; ?>
                                    </a>
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
                                <div class="actions" style="display: flex; gap: 0.75rem; justify-content: flex-start; align-items: center;">
                                    <a href="delete_document.php?id=<?php echo $row['DocID']; ?>" class="action-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this document?');">
                                        <img src="assets/images/delete-icon.svg" alt="Delete" style="width: 18px; height: 18px; opacity: 0.6;">
                                    </a>
                                    
                                    <a href="edit_document.php?id=<?php echo $row['DocID']; ?>" class="action-btn" title="Edit">
                                        <img src="assets/images/edit-icon.svg" alt="Edit" style="width: 18px; height: 18px; opacity: 0.6;">
                                    </a>
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
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-nav">
                    <img src="assets/images/chevron-left-icon.svg" alt="Prev" style="width: 14px; height: 14px; opacity: 0.4;">
                </a>
            <?php
else: ?>
                <div class="page-nav disabled">
                    <img src="assets/images/chevron-left-icon.svg" alt="Prev" style="width: 14px; height: 14px; opacity: 0.2;">
                </div>
            <?php
endif; ?>

            <div class="pagination-links">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-link <?php echo($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php
endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>" class="page-nav">
                    <img src="assets/images/chevron-right-icon.svg" alt="Next" style="width: 14px; height: 14px; opacity: 0.4;">
                </a>
            <?php
else: ?>
                <div class="page-nav disabled">
                    <img src="assets/images/chevron-right-icon.svg" alt="Next" style="width: 14px; height: 14px; opacity: 0.2;">
                </div>
            <?php
endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
