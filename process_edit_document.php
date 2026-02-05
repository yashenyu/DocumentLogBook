<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $office = $_POST['office'] ?? '';
    $subject = $_POST['doc_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $receivedBy = $_POST['received_by'] ?? '';
    $status = $_POST['status'] ?? 'Pending';

    if (!$id || empty($subject) || empty($office)) {
        $_SESSION['error'] = "Valid ID, Name and Office are required.";
        header("Location: edit_document.php?id=$id");
        exit;
    }

    // Conditional Validation for Outgoing
    if ($status === 'Outgoing' && empty($receivedBy)) {
        $_SESSION['error'] = "Received By is required for Outgoing documents.";
        header("Location: edit_document.php?id=$id");
        exit;
    }

    // Handle File Upload if new file provided
    $docImage = null;
    $updateImage = false;

    if (isset($_FILES['doc_image']) && $_FILES['doc_image']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['doc_image']['name']);
        $targetPath = $uploadDir . time() . '_' . $fileName;

        $fileType = pathinfo($targetPath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');
        if (in_array(strtolower($fileType), $allowTypes)) {
            if (move_uploaded_file($_FILES['doc_image']['tmp_name'], $targetPath)) {
                $docImage = $targetPath;
                $updateImage = true;
            }
        }
    }

    try {
        if ($updateImage) {
            $sql = "UPDATE DocumentLog SET Office = ?, Subject = ?, Description = ?, ReceivedBy = ?, Status = ?, DocImage = ? WHERE DocID = ?";
            $params = [$office, $subject, $description, $receivedBy, $status, $docImage, $id];
        }
        else {
            $sql = "UPDATE DocumentLog SET Office = ?, Subject = ?, Description = ?, ReceivedBy = ?, Status = ? WHERE DocID = ?";
            $params = [$office, $subject, $description, $receivedBy, $status, $id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Document updated successfully!";
        header('Location: documents.php');
        exit;

    }
    catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: edit_document.php?id=$id");
        exit;
    }
}
else {
    header('Location: documents.php');
    exit;
}
