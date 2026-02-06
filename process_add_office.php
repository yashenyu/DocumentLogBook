<?php
session_start();

// Access Control: Only Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header('Location: documents.php');
    exit;
}

require_once __DIR__ . '/helpers/OfficeHelper.php';

$officeName = $_POST['office_name'] ?? '';

try {
    OfficeHelper::addOffice($officeName);
    $_SESSION['success'] = "Office added successfully.";
}
catch (Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: documents.php');
exit;

