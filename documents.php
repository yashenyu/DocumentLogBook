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

// Sorting Params
$sortBy = $_GET['sort_by'] ?? '';
$sortOrder = $_GET['sort_order'] ?? '';
$validColumns = ['DocID', 'DocDate', 'Office', 'Subject', 'ReceivedBy', 'Status'];

// Filter Params
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$officeFilter = $_GET['office'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Build Query
$whereSQL = "1=1";
$params = [];

if (!empty($search)) {
    $whereSQL .= " AND (Subject LIKE ? OR Description LIKE ? OR Office LIKE ? OR Status LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

if (!empty($startDate)) {
    $whereSQL .= " AND DocDate >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $whereSQL .= " AND DocDate <= ?";
    $params[] = $endDate;
}

if (!empty($officeFilter)) {
    $whereSQL .= " AND Office LIKE ?";
    $params[] = "%$officeFilter%";
}

if (!empty($statusFilter)) {
    $whereSQL .= " AND Status = ?";
    $params[] = $statusFilter;
}

// Order By Logic
$orderBySQL = "ORDER BY DocDate DESC, DocID DESC"; // Default
if (in_array($sortBy, $validColumns) && in_array($sortOrder, ['ASC', 'DESC'])) {
    $orderBySQL = "ORDER BY $sortBy $sortOrder";
}

// Count Total
$countSql = "SELECT COUNT(*) FROM DocumentLog WHERE $whereSQL";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalDocs = $stmt->fetchColumn();
$totalPages = ceil($totalDocs / $limit);

// Fetch Data
$sql = "SELECT * FROM DocumentLog WHERE $whereSQL $orderBySQL LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function for Sort Links
function getSortLink($column, $label, $currentSortBy, $currentSortOrder, $search, $filters)
{
    $newOrder = 'ASC';
    $icon = '<i class="fa-solid fa-sort sort-icon" style="opacity: 0.3;"></i>';
    $isActive = ($currentSortBy === $column);

    if ($isActive) {
        if ($currentSortOrder === 'ASC') {
            $newOrder = 'DESC';
            $icon = '<i class="fa-solid fa-sort-up sort-icon"></i>';
        }
        elseif ($currentSortOrder === 'DESC') {
            $newOrder = ''; // Reset to off
            $icon = '<i class="fa-solid fa-sort-down sort-icon"></i>';
        }
    }

    // Build Query String
    $queryParams = [
        'search' => $search,
        'page' => 1, // Reset page on sort
        'start_date' => $filters['startDate'],
        'end_date' => $filters['endDate'],
        'office' => $filters['officeFilter'],
        'status' => $filters['statusFilter']
    ];

    if ($newOrder !== '') {
        $queryParams['sort_by'] = $column;
        $queryParams['sort_order'] = $newOrder;
    }

    $url = '?' . http_build_query($queryParams);

    return '<a href="' . $url . '" class="sortable-header">' . $label . ' ' . $icon . '</a>';
}

$filterParams = [
    'startDate' => $startDate,
    'endDate' => $endDate,
    'officeFilter' => $officeFilter,
    'statusFilter' => $statusFilter
];

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
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search by name, office, etc..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php
// Preserve filters in search form
foreach (['start_date', 'end_date', 'office', 'status', 'sort_by', 'sort_order'] as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($_GET[$key]) . '">';
    }
}
?>
                </div>
            </form>
            
            <div class="toolbar-buttons">
                <!-- Advanced Filters Button -->
                <button id="openFilterModal" class="btn-dark">
                    <img src="assets/images/filter-icon.svg" alt="Filter" style="width: 16px; height: 16px;"> Advanced Filters
                </button>
                
                <button id="openAddModal" class="btn-dark" style="text-decoration: none;">
                    <img src="assets/images/add-icon.svg" alt="Add" style="width: 16px; height: 16px;"> Add
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th style="width: 80px;"><?php echo getSortLink('DocID', 'ID', $sortBy, $sortOrder, $search, $filterParams); ?></th>
                        <th>Preview</th>
                        <th><?php echo getSortLink('DocDate', 'Date', $sortBy, $sortOrder, $search, $filterParams); ?></th>
                        <th><?php echo getSortLink('Office', 'Office', $sortBy, $sortOrder, $search, $filterParams); ?></th>
                        <th><?php echo getSortLink('Subject', 'Subject', $sortBy, $sortOrder, $search, $filterParams); ?></th>
                        <th><?php echo getSortLink('ReceivedBy', 'Received By', $sortBy, $sortOrder, $search, $filterParams); ?></th>
                        <th><?php echo getSortLink('Status', 'Status', $sortBy, $sortOrder, $search, $filterParams); ?></th>
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
                                <div class="actions">
                                    <button class="action-btn view-btn" data-id="<?php echo $row['DocID']; ?>" title="View Details">
                                        <i class="fa-regular fa-eye" style="font-size: 1.1rem; opacity: 0.6; color: #0f172a;"></i>
                                    </button>
                                    
                                    <a href="delete_document.php?id=<?php echo $row['DocID']; ?>" class="action-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this document?');">
                                        <img src="assets/images/delete-icon.svg" alt="Delete" style="width: 18px; height: 18px; opacity: 0.6;">
                                    </a>
                                    
                                    <button class="action-btn edit-btn" data-id="<?php echo $row['DocID']; ?>" title="Edit">
                                        <img src="assets/images/edit-icon.svg" alt="Edit" style="width: 18px; height: 18px; opacity: 0.6;">
                                    </button>
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
            <?php
$paginationQueryParams = [
    'search' => $search,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'office' => $officeFilter,
    'status' => $statusFilter,
    'sort_by' => $sortBy,
    'sort_order' => $sortOrder
];
?>
            <?php
// Create Prev Link
$paginationQueryParams['page'] = $page - 1;
$prevUrl = '?' . http_build_query($paginationQueryParams);

// Create Next Link
$paginationQueryParams['page'] = $page + 1;
$nextUrl = '?' . http_build_query($paginationQueryParams);
?>

            <?php if ($page > 1): ?>
                <a href="<?php echo htmlspecialchars($prevUrl); ?>" class="page-nav">
                    <img src="assets/images/chevron-left-icon.svg" alt="Prev" style="width: 14px; height: 14px; opacity: 0.6;">
                </a>
            <?php
else: ?>
                <div class="page-nav disabled">
                    <img src="assets/images/chevron-left-icon.svg" alt="Prev" style="width: 14px; height: 14px; opacity: 0.2;">
                </div>
            <?php
endif; ?>

            <div class="pagination-links">
                <?php for ($i = 1; $i <= $totalPages; $i++):
    $paginationQueryParams['page'] = $i;
    $url = '?' . http_build_query($paginationQueryParams);
?>
                    <a href="<?php echo htmlspecialchars($url); ?>" class="page-link <?php echo($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php
endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="<?php echo htmlspecialchars($nextUrl); ?>" class="page-nav">
                    <img src="assets/images/chevron-right-icon.svg" alt="Next" style="width: 14px; height: 14px; opacity: 0.6;">
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

    <!-- Advanced Filters Modal -->
    <div id="filterModal" class="modal-overlay">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3>Advanced Filters</h3>
                <span class="close-modal close-filter">&times;</span>
            </div>
            <form action="" method="GET" id="filterForm">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <!-- Preserve Sort params if they exist -->
                <?php if (isset($_GET['sort_by'])): ?>
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($_GET['sort_by']); ?>">
                <?php
endif; ?>
                <?php if (isset($_GET['sort_order'])): ?>
                    <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($_GET['sort_order']); ?>">
                <?php
endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Date Range</label>
                        <div class="date-inputs">
                            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
                            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Office</label>
                        <input type="text" name="office" class="form-control" placeholder="e.g. Dean's Office" value="<?php echo htmlspecialchars($_GET['office'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo(isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Received" <?php echo(isset($_GET['status']) && $_GET['status'] == 'Received') ? 'selected' : ''; ?>>Received</option>
                            <option value="Released" <?php echo(isset($_GET['status']) && $_GET['status'] == 'Released') ? 'selected' : ''; ?>>Released</option>
                            <option value="Completed" <?php echo(isset($_GET['status']) && $_GET['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                </div>

                <div class="modal-actions">
                    <a href="documents.php" class="btn-secondary" style="text-decoration: none; text-align: center;">Reset</a>
                    <button type="submit" class="btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Document Modal -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3>Add New Document</h3>
                <span class="close-modal close-add">&times;</span>
            </div>
            <form action="process_add_document.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid-2" style="align-items: stretch;">
                    <!-- Left Column -->
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; height: 100%;">
                        <div class="form-group">
                            <label for="doc_name">Document Name</label>
                            <input type="text" id="doc_name" name="doc_name" class="form-control" required placeholder="e.g. Invoice #12345">
                        </div>

                        <div class="form-group">
                            <label for="office">Office / Department</label>
                            <input type="text" id="office" name="office" class="form-control" required placeholder="e.g. Finance, HR">
                        </div>

                        <div class="form-group" style="flex: 1; display: flex; flex-direction: column;">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" style="flex: 1; resize: none; min-height: 150px;" placeholder="Brief details about the document..."></textarea>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; height: 100%;">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control" onchange="toggleReceivedBy()">
                                <option value="Incoming" selected>Incoming</option>
                                <option value="Outgoing">Outgoing</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="received_by">Received By <span id="received_req" style="color: red; display: none;">*</span></label>
                            <input type="text" id="received_by" name="received_by" class="form-control" placeholder="Name of receiver">
                        </div>

                        <div class="form-group" style="flex: 1; display: flex; flex-direction: column;">
                            <label>Upload Files (Image/PDF)</label>
                            <!-- Custom Upload UI -->
                            <div class="custom-upload-container">
                                <!-- Trigger Box -->
                                <div class="upload-trigger" id="uploadTrigger">
                                    <i class="fa-solid fa-arrow-up-from-bracket" style="font-size: 2rem; color: #64748b; margin-bottom: 0.5rem;"></i>
                                    <span style="color: #64748b; font-size: 0.9rem;">Click to Upload</span>
                                </div>
                                
                                <!-- Preview Sidebar -->
                                <div class="preview-sidebar" id="previewSidebar">
                                    <!-- Previews will be injected here via JS -->
                                </div>
                            </div>
                            <!-- Hidden Input -->
                            <input type="file" id="doc_image" name="doc_image[]" style="display: none;" accept=".jpg,.jpeg,.png,.gif,.pdf" multiple>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary close-add-btn">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Document</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3>Edit Document</h3>
                <span class="close-modal close-edit">&times;</span>
            </div>
            <form action="process_edit_document.php" method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-grid-2" style="align-items: stretch;">
                    <!-- Left Column -->
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; height: 100%;">
                        <div class="form-group">
                            <label for="edit_doc_name">Document Name</label>
                            <input type="text" id="edit_doc_name" name="doc_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_office">Office / Department</label>
                            <input type="text" id="edit_office" name="office" class="form-control" required>
                        </div>

                        <div class="form-group" style="flex: 1; display: flex; flex-direction: column;">
                            <label for="edit_description">Description</label>
                            <textarea id="edit_description" name="description" class="form-control" style="flex: 1; resize: none; min-height: 150px;"></textarea>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; height: 100%;">
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status" class="form-control">
                                <option value="Incoming">Incoming</option>
                                <option value="Outgoing">Outgoing</option>
                                <option value="Pending">Pending</option>
                                <option value="Received">Received</option>
                                <option value="Released">Released</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_received_by">Received By</label>
                            <input type="text" id="edit_received_by" name="received_by" class="form-control">
                        </div>

                        <!-- Existing Files Section -->
                        <div class="form-group">
                            <label>Existing Files</label>
                            <div id="existingFilesContainer" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 190px; overflow-y: auto; padding-right: 5px; border: 1px solid #f1f5f9; padding: 10px; border-radius: 6px;">
                                <!-- Injected via JS -->
                            </div>
                        </div>

                        <!-- New Upload Section -->
                        <div class="form-group" style="flex: 1; display: flex; flex-direction: column;">
                            <label>Add New Files</label>
                            <div class="custom-upload-container">
                                <div class="upload-trigger" id="editUploadTrigger">
                                    <i class="fa-solid fa-arrow-up-from-bracket" style="font-size: 2rem; color: #64748b; margin-bottom: 0.5rem;"></i>
                                    <span style="color: #64748b; font-size: 0.9rem;">Click to Upload</span>
                                </div>
                                
                                <div class="preview-sidebar" id="editPreviewSidebar">
                                    <!-- Previews injected here -->
                                </div>
                            </div>
                            <input type="file" id="edit_doc_image" name="doc_image[]" style="display: none;" accept=".jpg,.jpeg,.png,.gif,.pdf" multiple>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary close-edit-btn">Cancel</button>
                    <button type="submit" class="btn-primary">Update Document</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Document Modal -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal-content modal-lg" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; width: 95%; max-width: 1000px;">
            <div class="modal-header">
                <h3>Document Details</h3>
                <span class="close-modal close-view">&times;</span>
            </div>
            <div class="modal-body" style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                <!-- Header Info -->
                <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                    <h2 id="view_subject" style="margin: 0 0 0.5rem 0; color: #1e293b; word-break: break-word; overflow-wrap: break-word;"></h2>
                    <span id="view_id_badge" style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"></span>
                </div>

                <!-- 2x2 Metadata Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Office / Department</label>
                        <div id="view_office" style="font-weight: 500; color: #334155; font-size: 1rem; word-break: break-word; overflow-wrap: break-word;"></div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Date Logged</label>
                        <div id="view_date" style="font-weight: 500; color: #334155; font-size: 1rem;"></div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                        <div id="view_status"></div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Received By</label>
                        <div id="view_received_by" style="font-weight: 500; color: #334155; font-size: 1rem; word-break: break-word; overflow-wrap: break-word;"></div>
                    </div>
                </div>

                <!-- Description -->
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem;">Description</label>
                    <div id="view_description" style="background: #fff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; min-height: 250px; white-space: pre-wrap; word-break: break-word; overflow-wrap: break-word; color: #334155; line-height: 1.7; font-size: 1rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);"></div>
                </div>

                <!-- Attachments Gallery -->
                <div>
                    <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem;">Attachments Gallery</label>
                    <div id="view_attachments" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; padding: 4px;">
                        <!-- Injected via JS -->
                    </div>
                </div>
            </div>
            <div class="modal-actions" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: #fcfcfc;">
                <button type="button" class="btn-secondary close-view-btn">Close</button>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        function toggleReceivedBy() {
            const status = document.getElementById('status').value;
            const receivedInput = document.getElementById('received_by');
            const receivedReq = document.getElementById('received_req');
            
            if (status === 'Outgoing') {
                receivedInput.required = true;
                receivedReq.style.display = 'inline';
            } else {
                receivedInput.required = false;
                receivedReq.style.display = 'none';
            }
        }
    </script>
</body>
</html>
```
