<?php
session_start();
require_once 'config/database.php';
require_once __DIR__ . '/helpers/OfficeHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Params
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Sorting Params
$sortBy = $_GET['sort_by'] ?? '';
$sortOrder = $_GET['sort_order'] ?? '';
$validColumns = ['DocID', 'DocDate', 'Office', 'Subject', 'ReceivedBy', 'Status'];

// Filter Params
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$officeFilter = $_GET['office'] ?? '';
$receivedFilter = $_GET['received_by'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Office dropdown options (stored in file; no DB dependency)
$officeOptions = OfficeHelper::getOffices();
$selectedOfficeForDropdown = '';
if (!empty($officeFilter)) {
    $selectedOfficeForDropdown = trim(explode(',', $officeFilter)[0] ?? '');
}

// Build Query
$whereSQL = "1=1";
$params = [];

if (!empty($search)) {
    $whereSQL .= " AND (Subject LIKE ? OR Description LIKE ? OR Office LIKE ? OR Status LIKE ? OR ReceivedBy LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term, $term]);
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
    $offices = explode(',', $officeFilter);
    if (!empty($offices)) {
        $officeClauses = [];
        foreach ($offices as $o) {
            $officeClauses[] = "Office LIKE ?";
            $params[] = "%" . trim($o) . "%";
        }
        $whereSQL .= " AND (" . implode(" OR ", $officeClauses) . ")";
    }
}

if (!empty($receivedFilter)) {
    $receivedNames = explode(',', $receivedFilter);
    if (!empty($receivedNames)) {
        $receivedClauses = [];
        foreach ($receivedNames as $n) {
            $receivedClauses[] = "ReceivedBy LIKE ?";
            $params[] = "%" . trim($n) . "%";
        }
        $whereSQL .= " AND (" . implode(" OR ", $receivedClauses) . ")";
    }
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
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo-area" <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>id="changeLogoTrigger" style="cursor: pointer;" title="Click to change logo"<?php
endif; ?>>
                <span class="logo-text">LogBook</span>
                <div class="nav-iso-box-container" style="display: flex; align-items: center; justify-content: center;">
                    <img id="navbarLogo" src="assets/images/HAU.png" alt="Logo" style="width: 150%; height: 150%; object-fit: contain;">
                </div>
            </div>
            <div class="nav-divider"></div>
            <div class="nav-subtitle" style="display: flex; align-items: center; gap: 0.6rem;">
                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                    <span style="background-color: rgba(255, 184, 28, 0.05); color: var(--accent-color); padding: 2px 12px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; border: 1px solid rgba(255, 184, 28, 0.4); letter-spacing: 0.5px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px;">
                        <span style="width: 6px; height: 6px; background-color: var(--accent-color); border-radius: 50%;"></span>
                        Admin
                    </span>
                <?php
else: ?>
                    <span style="background-color: rgba(255, 255, 255, 0.15); color: #ffffff; padding: 2px 12px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; border: 1px solid rgba(255, 255, 255, 0.5); letter-spacing: 0.5px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px;">
                        <span style="width: 6px; height: 6px; background-color: #ffffff; border-radius: 50%;"></span>
                        Staff
                    </span>
                <?php
endif; ?>
            </div>
        </div>

        <div class="nav-right">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <button id="openStaffModal" class="btn-outline btn-staff" style="text-decoration: none; font-size: 0.85rem; padding: 0.5rem 1.2rem; cursor: pointer;">
                    <i class="fa-solid fa-users-gear"></i> Manage Staff
                </button>
            <?php
endif; ?>
            <a href="logout.php" class="btn-outline btn-logout" style="text-decoration: none; font-size: 0.85rem; padding: 0.5rem 1.2rem;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- (Static alerts removed in favor of toasts) -->

        <!-- Toolbar -->
        <div class="doc-toolbar">
            <form action="" method="GET" class="search-form">
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php
// Preserve filters in search form
foreach (['start_date', 'end_date', 'office', 'status', 'sort_by', 'sort_order', 'limit'] as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($_GET[$key]) . '">';
    }
}
?>
                </div>
            </form>
            
            <div class="toolbar-buttons">
                <!-- Items Per Page Selector -->
                <div class="limit-selector" style="display: flex; align-items: center; gap: 0.5rem; margin-right: 1rem; border: 1px solid #e2e8f0; padding: 0.2rem 0.8rem; border-radius: 99px; background: #fff;">
                    <label style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Show:</label>
                    <select id="limitSelector" class="form-control-sm" style="border: none; background: transparent; font-size: 0.8rem; font-weight: 600; color: #1e293b; cursor: pointer; outline: none; padding: 4px 0;">
                        <option value="10" <?php echo($limit == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo($limit == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo($limit == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo($limit == 100) ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>

                <!-- Advanced Filters Button -->
                <button id="openFilterModal" class="btn btn-dark">
                    <img src="assets/images/filter-icon.svg" alt="Filter" style="width: 16px; height: 16px;"> Filters
                </button>
                
                <button id="openAddModal" class="btn btn-dark" style="text-decoration: none;">
                    <img src="assets/images/add-icon.svg" alt="Add" style="width: 16px; height: 16px;"> Add
                </button>
            </div>
        </div>

        <!-- Toast Notification Container -->
        <div id="toast-container" class="toast-container"></div>

        <?php if (isset($_SESSION['success'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast("<?php echo $_SESSION['success']; ?>", "success");
                });
            </script>
            <?php unset($_SESSION['success']); ?>
        <?php
endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast("<?php echo $_SESSION['error']; ?>", "error");
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php
endif; ?>

        <script>
            function showToast(message, type) {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast toast-${type} ${type === 'error' ? 'error' : ''}`;
                
                const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
                
                toast.innerHTML = `
                    <div class="toast-icon"><i class="fa-solid ${icon}"></i></div>
                    <div class="toast-message">${message}</div>
                `;
                
                container.appendChild(toast);
                
                // Auto remove after 4 seconds
                setTimeout(() => {
                    toast.classList.add('toast-fade-out');
                    setTimeout(() => {
                        toast.remove();
                    }, 400);
                }, 4000);
            }
        </script>

        <!-- Table & Pagination Area -->
        <div id="tableArea">
            <?php include 'table_data_partial.php'; ?>
        </div>

        <!-- Dashboard Footer -->
        <footer class="dashboard-footer">
            <div>
                &copy; <?php echo date('Y'); ?> Document LogBook System
            </div>
            <div class="footer-links">
                <a href="about.php" class="about-dev-link">About Developers</a>
            </div>
        </footer>
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
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Incoming" <?php echo($statusFilter == 'Incoming') ? 'selected' : ''; ?>>Incoming</option>
                            <option value="Outgoing" <?php echo($statusFilter == 'Outgoing') ? 'selected' : ''; ?>>Outgoing</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Office</label>
                        <select name="office" class="form-control" id="officeFilterSelect">
                            <option value="">All Offices</option>
                            <?php foreach ($officeOptions as $officeOpt): ?>
                                <option value="<?php echo htmlspecialchars($officeOpt); ?>" <?php echo ($selectedOfficeForDropdown === $officeOpt) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($officeOpt); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Received By</label>
                        <div class="multi-select-container" data-type="received_by" id="receivedContainer">
                            <div class="tag-container" id="receivedTags"></div>
                            <input type="text" class="autocomplete-input" placeholder="Type to add name...">
                            <div class="autocomplete-dropdown shadow-sm"></div>
                            <input type="hidden" name="received_by" value="<?php echo htmlspecialchars($receivedFilter); ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <a href="documents.php" class="btn-secondary" style="text-decoration: none; text-align: center;">Reset</a>
                    <button type="submit" class="btn-primary" id="applyFiltersBtn">Apply Filters</button>
                </div>
            </form>

            <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                    <form action="process_add_office.php" method="POST">
                        <div class="form-grid-2" style="align-items: end; grid-template-columns: 1fr auto;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="new_office_name">Add Office</label>
                                <input
                                    type="text"
                                    id="new_office_name"
                                    name="office_name"
                                    class="form-control"
                                    placeholder="e.g. Graduate School"
                                    maxlength="60"
                                    required
                                >
                            </div>
                            <div class="modal-actions" style="margin-top: 0; padding-top: 0; border-top: 0;">
                                <button type="submit" class="btn-primary">Add Office</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Document Modal -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-content modal-fixed-90">
            <div class="modal-header">
                <h3>Add New Document</h3>
                <span class="close-modal close-add">&times;</span>
            </div>
            <form action="process_add_document.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-grid-2">
                        <!-- Left Column -->
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1; min-height: 0;">
                            <div class="form-group">
                                <label for="doc_name">Document Name</label>
                                <input type="text" id="doc_name" name="doc_name" class="form-control" required placeholder="e.g. Invoice #12345" maxlength="255">
                            </div>

                            <div class="form-group">
                                <label for="office">Office / Department</label>
                                <input type="text" id="office" name="office" class="form-control" required placeholder="e.g. Finance, HR" maxlength="100">
                            </div>

                            <div class="form-group" style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                                <label for="description">Description (Up to 255 chars)</label>
                                <textarea id="description" name="description" class="form-control" required style="flex: 1; resize: none;" placeholder="Brief details about the document..." maxlength="255"></textarea>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1; min-height: 0;">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" onchange="toggleReceivedBy()">
                                    <option value="Incoming" selected>Incoming</option>
                                    <option value="Outgoing">Outgoing</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="received_by">Received By <span id="received_req" style="color: red; display: none;">*</span></label>
                                <input type="text" id="received_by" name="received_by" class="form-control" placeholder="Name of receiver" maxlength="100">
                            </div>

                            <div class="form-group" style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                                <label>Upload Files (Image/PDF)</label>
                                <!-- Custom Upload UI -->
                                <div class="custom-upload-container">
                                    <div class="upload-trigger" id="uploadTrigger">
                                        <i class="fa-solid fa-arrow-up-from-bracket" style="font-size: 2rem; color: #64748b; margin-bottom: 0.5rem;"></i>
                                        <span style="color: #64748b; font-size: 0.9rem;">Click to Upload</span>
                                    </div>
                                    <div class="preview-sidebar" id="previewSidebar"></div>
                                </div>
                                <input type="file" id="doc_image" name="doc_image[]" style="display: none;" accept=".jpg,.jpeg,.png,.gif,.pdf" multiple>
                            </div>
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
        <div class="modal-content modal-fixed-90">
            <div class="modal-header">
                <h3>Edit Document</h3>
                <span class="close-modal close-edit">&times;</span>
            </div>
            <form action="process_edit_document.php" method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body modal-body-scaled">
                    <div class="form-grid-2">
                        <!-- Left Column -->
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1; min-height: 0;">
                            <div class="form-group">
                                <label for="edit_doc_name">Document Name</label>
                                <input type="text" id="edit_doc_name" name="doc_name" class="form-control" required maxlength="255">
                            </div>

                            <div class="form-group">
                                <label for="edit_office">Office / Department</label>
                                <input type="text" id="edit_office" name="office" class="form-control" required maxlength="100">
                            </div>

                            <div class="form-group" style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                                <label for="edit_description">Description (Up to 255 chars)</label>
                                <textarea id="edit_description" name="description" class="form-control" style="flex: 1; resize: none;" maxlength="255"></textarea>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1; min-height: 0;">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select id="edit_status" name="status" class="form-control">
                                    <option value="Incoming">Incoming</option>
                                    <option value="Outgoing">Outgoing</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="edit_received_by">Received By</label>
                                <input type="text" id="edit_received_by" name="received_by" class="form-control" maxlength="100">
                            </div>

                            <!-- Existing Files Section -->
                            <div class="form-group" style="flex: 1; display: flex; flex-direction: column; min-height: 0; margin-bottom: 0.5rem;">
                                <label>Existing Files</label>
                                <div id="existingFilesContainer" style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1; min-height: 60px; overflow-y: auto; padding-right: 5px; border: 1px solid #f1f5f9; padding: 10px; border-radius: 6px;">
                                    <!-- Injected via JS -->
                                </div>
                            </div>

                            <!-- New Upload Section -->
                            <div class="form-group" style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                                <label>Add New Files</label>
                                <div class="custom-upload-container">
                                    <div class="upload-trigger" id="editUploadTrigger">
                                        <i class="fa-solid fa-arrow-up-from-bracket" style="font-size: 2rem; color: #64748b; margin-bottom: 0.5rem;"></i>
                                        <span style="color: #64748b; font-size: 0.9rem;">Click to Upload</span>
                                    </div>
                                    <div class="preview-sidebar" id="editPreviewSidebar"></div>
                                </div>
                                <input type="file" id="edit_doc_image" name="doc_image[]" style="display: none;" accept=".jpg,.jpeg,.png,.gif,.pdf" multiple>
                            </div>
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
        <div class="modal-content modal-lg" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; width: 95%; max-width: 1200px;">
            <div class="modal-header">
                <h3>Document Details</h3>
                <span class="close-modal close-view">&times;</span>
            </div>
            <div class="modal-body" style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                <div class="view-modal-grid" style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem;">
                    <!-- Left Column: Information -->
                    <div class="view-info-column">
                        <!-- Header Info -->
                        <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                            <h2 id="view_subject" style="margin: 0 0 0.5rem 0; color: #1e293b; word-break: break-word; overflow-wrap: break-word;"></h2>
                            <span id="view_id_badge" style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"></span>
                        </div>

                        <!-- Metadata Grid -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Office / Department</label>
                                <div id="view_office" style="font-weight: 500; color: #334155; font-size: 0.95rem; word-break: break-word; overflow-wrap: break-word;"></div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Date Logged</label>
                                <div id="view_date" style="font-weight: 500; color: #334155; font-size: 0.95rem;"></div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                                <div id="view_status"></div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Received By</label>
                                <div id="view_received_by" style="font-weight: 500; color: #334155; font-size: 0.95rem; word-break: break-word; overflow-wrap: break-word;"></div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem;">Description</label>
                            <div id="view_description" style="background: #fff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; min-height: 150px; white-space: pre-wrap; word-break: break-word; overflow-wrap: break-word; color: #334155; line-height: 1.6; font-size: 0.95rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);"></div>
                        </div>
                    </div>

                    <!-- Right Column: Attachments Sidebar -->
                    <div class="view-attachments-column" style="border-left: 1px solid #e2e8f0; padding-left: 1.5rem;">
                        <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-paperclip" style="color: #64748b;"></i> Attachments
                        </label>
                        <div id="view_attachments" style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <!-- Injected via JS -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: #fcfcfc;">
                <button type="button" class="btn-secondary close-view-btn">Close</button>
            </div>
        </div>
    </div>
    <!-- Manage Staff Modal -->
    <div id="staffModal" class="modal-overlay">
        <div class="modal-content modal-xl">
            <div class="modal-header">
                <h3>Staff Management</h3>
                <span class="close-modal close-staff">&times;</span>
            </div>
            
            <div class="modal-body" style="padding: 1.5rem;">
                <!-- Modal Tabs -->
                <div class="modal-tabs">
                    <button type="button" class="tab-btn active" data-tab="tab-add-staff">Add New Staff</button>
                    <button type="button" class="tab-btn" data-tab="tab-manage-accounts" id="triggerManageAccounts">Manage Accounts</button>
                </div>

                <!-- Tab 1: Add New Staff -->
                <div id="tab-add-staff" class="tab-content active">
                    <form action="process_register_staff.php" method="POST" style="max-width: 500px; margin: 0 auto; background: #f8fafc; padding: 2rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <h4 style="margin-bottom: 1.5rem; color: var(--primary-color);">Register New Account</h4>
                        <div class="form-group">
                            <label for="reg_username">Username</label>
                            <input type="text" id="reg_username" name="username" class="form-control" required placeholder="Enter Username" maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_password">Initial Password</label>
                            <input type="password" id="reg_password" name="password" class="form-control" required placeholder="Set Password">
                        </div>

                        <div class="form-group">
                            <label for="reg_role">Role</label>
                            <select id="reg_role" name="role" class="form-control">
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>

                        <div class="modal-actions" style="margin-top: 1.5rem; border: none; padding: 0;">
                            <button type="button" class="btn-secondary close-staff-btn">Cancel</button>
                            <button type="submit" class="btn-primary">Create Account</button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Manage Accounts -->
                <div id="tab-manage-accounts" class="tab-content">
                    <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div style="padding: 1.25rem 1.5rem; background: #fdfdfd; border-bottom: 2px solid #edf2f7; display: flex; justify-content: space-between; align-items: baseline;">
                            <h4 style="margin: 0; color: #2d3748; font-size: 1.1rem; font-weight: 700;">System Accounts</h4>
                            <div style="font-size: 0.85rem; color: #718096; font-weight: 500;">Manage credentials for all registered users</div>
                        </div>
                        <div style="overflow-x: auto; max-height: 50vh;">
                            <table class="user-management-table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="userListContainer">
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 2rem; color: #64748b;">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Loading users...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Logo Selection Modal -->
    <div id="logoModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Change Institution Logo</h3>
                <span class="close-modal close-logo">&times;</span>
            </div>
            <div class="logo-options" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem; padding: 1rem; max-height: 400px; overflow-y: auto;">
                <!-- HAU Logo -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/HAU.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/HAU.png" alt="HAU" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">HAU Institutional</div>
                </div>

                <!-- SOC -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/SOC.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/SOC.png" alt="SOC" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">SOC</div>
                </div>

                <!-- SEA -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/SEA.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/SEA.png" alt="SEA" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">SEA</div>
                </div>

                <!-- SBA -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/SBA.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/SBA.png" alt="SBA" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">SBA</div>
                </div>

                <!-- SAS -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/SAS.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/SAS.png" alt="SAS" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">SAS</div>
                </div>

                <!-- SHTM -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/SHTM.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/SHTM.png" alt="SHTM" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">SHTM</div>
                </div>

                <!-- SNAMS -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/SNAMS.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/SNAMS.png" alt="SNAMS" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">SNAMS</div>
                </div>

                <!-- CCJEF -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/CCJEF.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/CCJEF.png" alt="CCJEF" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">CCJEF</div>
                </div>

                <!-- Bed -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/Bed.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/Bed.png" alt="Bed" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">Basic Education</div>
                </div>

                <!-- Logbook Logo -->
                <div class="logo-option-card" onclick="updateAppLogo('assets/images/Logbook Logo.png')" style="cursor: pointer; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem; text-align: center; transition: all 0.2s;">
                    <img src="assets/images/Logbook Logo.png" alt="Logbook Logo" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.8rem; color: #1e293b;">Simple Logbook</div>
                </div>
            </div>
            <div class="modal-actions" style="border-top: none; margin-top: 0;">
                <button type="button" class="btn-secondary close-logo-btn" style="width: 100%;">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Lightbox Gallery Modal -->
    <div id="galleryModal">

        <div class="lightbox-container">
            <div class="lightbox-header">
                <span id="galleryTitle" style="font-weight: 600; font-size: 1rem; color: #ffffff;">Document Attachments</span>
                <span class="lightbox-close close-gallery">&times;</span>
            </div>
            <div class="lightbox-body">
                <!-- Navigation Arrow Left -->
                <button class="lightbox-nav lightbox-prev" id="galleryPrev">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                
                <!-- Main Image Display -->
                <div class="lightbox-main" id="galleryMain">
                    <img id="galleryImage" src="" alt="Attachment" style="display: none;">
                    <iframe id="galleryPdf" src="" style="display: none; width: 100%; height: 100%; border: none;"></iframe>
                    <div id="galleryLoading" style="color: #ffffff; font-size: 1.5rem;">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
                
                <!-- Navigation Arrow Right -->
                <button class="lightbox-nav lightbox-next" id="galleryNext">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="lightbox-footer">
                <div class="lightbox-actions">
                    <button class="lightbox-btn lightbox-btn-accent" id="galleryDownloadOne" title="Download current attachment">
                        <i class="fa-solid fa-download"></i> Download
                    </button>
                </div>

                <div class="lightbox-thumbnails" id="galleryThumbnails">
                    <!-- Thumbnails injected via JS -->
                </div>
                <div class="lightbox-counter" id="galleryCounter">1 / 1</div>
            </div>
        </div>
    </div>


    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script>
        // Inline logic for persistent logo
        function updateAppLogo(path) {
            localStorage.setItem('app_logo_path', path);
            const logo = document.getElementById('navbarLogo');
            if(logo) logo.src = path;
            
            const modal = document.getElementById('logoModal');
            if(modal) modal.classList.remove('active');
            
            if(typeof showToast === 'function') {
                showToast("Logo updated successfully!", "success");
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedLogo = localStorage.getItem('app_logo_path');
            if(savedLogo) {
                const logo = document.getElementById('navbarLogo');
                if(logo) logo.src = savedLogo;
            }
        });
        
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
