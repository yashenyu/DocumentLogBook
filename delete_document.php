<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM DocumentLog WHERE DocID = ?");
        $stmt->execute([$id]);

        // Optional: Delete associated image file if exists

        $_SESSION['success'] = "Document deleted successfully.";
    }
    catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting document: " . $e->getMessage();
    }
}

header('Location: documents.php');
exit;
