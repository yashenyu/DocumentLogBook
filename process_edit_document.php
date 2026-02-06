<?php
session_start();
require_once 'config/database.php';
require_once 'helpers/UploadHelper.php';

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

    // Arrays for handling files
    $deleteAttachments = $_POST['delete_attachments'] ?? [];

    if (!$id || empty($subject) || empty($office)) {
        $_SESSION['error'] = "Valid ID, Name and Office are required.";
        header("Location: documents.php");
        exit;
    }

    // Conditional Validation for Outgoing
    if ($status === 'Outgoing' && empty($receivedBy)) {
        $_SESSION['error'] = "Received By is required for Outgoing documents.";
        header("Location: documents.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Handle Deletions (just remove from DB, no file system needed)
        if (!empty($deleteAttachments)) {
            foreach ($deleteAttachments as $attachId) {
                $delStmt = $pdo->prepare("DELETE FROM DocumentAttachments WHERE AttachmentID = ? AND DocID = ?");
                $delStmt->execute([$attachId, $id]);
            }
        }

        // 2. Handle New Uploads (Multiple) via Helper - now returns binary data
        if (isset($_FILES['doc_image']) && !empty($_FILES['doc_image']['name'][0])) {
            try {
                $newFiles = UploadHelper::handleUploads($_FILES['doc_image']);
                foreach ($newFiles as $file) {
                    $stmt = $pdo->prepare("INSERT INTO DocumentAttachments (DocID, DocImage, FileType) VALUES (?, ?, ?)");
                    $stmt->execute([$id, $file['data'], $file['type']]);
                }
            }
            catch (Exception $e) {
                $_SESSION['error'] = "Upload failed: " . $e->getMessage();
                $pdo->rollBack();
                header("Location: documents.php");
                exit;
            }
        }

        // 3. Update Document Log (no DocImage field anymore)
        $sql = "UPDATE DocumentLog SET Office = ?, Subject = ?, Description = ?, ReceivedBy = ?, Status = ? WHERE DocID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$office, $subject, $description, $receivedBy, $status, $id]);

        $pdo->commit();
        $_SESSION['success'] = "Document updated successfully!";
        header('Location: documents.php');
        exit;

    }
    catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: documents.php");
        exit;
    }
}
else {
    header('Location: documents.php');
    exit;
}
