<?php
session_start();
require_once 'config/database.php';
require_once __DIR__ . '/helpers/OfficeHelper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_name = $_POST['old_name'] ?? '';
    $new_name = $_POST['new_name'] ?? '';

    try {
        OfficeHelper::updateOffice($old_name, $new_name);

        // Optional: Update existing documents to reflect the new name?
        // The user asked to show how many items will be affected, which implies they MIGHT want them updated.
        // If we don't update them, they'll become "orphaned" from the filter list but still exist in DB.
        // Usually, when editing an office name, we want to update the records too.

        $stmt = $pdo->prepare("UPDATE DocumentLog SET Office = ? WHERE Office = ?");
        $stmt->execute([$new_name, $old_name]);

        $_SESSION['success'] = "Office updated successfully. " . $stmt->rowCount() . " documents updated.";
    }
    catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header('Location: documents.php');
exit;
