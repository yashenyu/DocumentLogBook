<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

// Params
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sortBy = $_GET['sort_by'] ?? '';
$sortOrder = $_GET['sort_order'] ?? '';

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$officeFilter = $_GET['office'] ?? '';
$receivedFilter = $_GET['received_by'] ?? '';
$statusFilter = $_GET['status'] ?? '';

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

// Count Total
$countSql = "SELECT COUNT(*) FROM DocumentLog WHERE $whereSQL";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalDocs = $stmt->fetchColumn();
$totalPages = ceil($totalDocs / $limit);

// Sorting validation
$validColumns = ['DocID', 'DocDate', 'Office', 'Subject', 'ReceivedBy', 'Status'];
$orderBySQL = "ORDER BY DocDate DESC, DocID DESC"; // Default
if (in_array($sortBy, $validColumns) && in_array($sortOrder, ['ASC', 'DESC'])) {
    $orderBySQL = "ORDER BY $sortBy $sortOrder";
}

// Fetch Data
$sql = "SELECT * FROM DocumentLog WHERE $whereSQL $orderBySQL LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include the partial to render the HTML
include dirname(__DIR__) . '/table_data_partial.php';
?>
