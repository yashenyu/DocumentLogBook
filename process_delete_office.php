<?php
session_start();
require_once 'config/database.php';
require_once __DIR__ . '/helpers/OfficeHelper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $office_name = $_POST['office_name'] ?? '';

    try {
        OfficeHelper::deleteOffice($office_name);

        // We DON'T delete the documents. We just remove the office from the helper list.
        // The documents will still have the office name but won't be in the dropdown/filter by default.
        // Or should we set them to 'Other' or something? 
        // Let's keep the document data intact but remove the office from the configuration.

        $_SESSION['success'] = "Office removed from list successfully.";
    }
    catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header('Location: documents.php');
exit;
