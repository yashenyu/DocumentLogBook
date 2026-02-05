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

        // 1. Handle Deletions
        if (!empty($deleteAttachments)) {
            foreach ($deleteAttachments as $attachId) {
                // Get path to delete file
                $stmt = $pdo->prepare("SELECT FilePath FROM DocumentAttachments WHERE AttachmentID = ? AND DocID = ?");
                $stmt->execute([$attachId, $id]);
                $file = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($file) {
                    if (file_exists($file['FilePath'])) {
                        unlink($file['FilePath']);
                    }
                    // Delete from DB
                    $delStmt = $pdo->prepare("DELETE FROM DocumentAttachments WHERE AttachmentID = ?");
                    $delStmt->execute([$attachId]);
                }
            }
        }

        // 2. Handle New Uploads (Multiple)
        $newAttachments = [];
        if (isset($_FILES['doc_image']) && !empty($_FILES['doc_image']['name'][0])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $count = count($_FILES['doc_image']['name']);
            for ($i = 0; $i < $count; $i++) {
                $fileName = basename($_FILES['doc_image']['name'][$i]);
                $targetPath = $uploadDir . time() . '_' . uniqid() . '_' . $fileName;
                $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
                $allowTypes = ['jpg', 'png', 'jpeg', 'gif', 'pdf'];

                if (in_array($fileType, $allowTypes)) {
                    if (move_uploaded_file($_FILES['doc_image']['tmp_name'][$i], $targetPath)) {
                        // Add to DocumentAttachments
                        $stmt = $pdo->prepare("INSERT INTO DocumentAttachments (DocID, FilePath) VALUES (?, ?)");
                        $stmt->execute([$id, $targetPath]);
                        $newAttachments[] = $targetPath;
                    }
                }
            }
        }

        // 3. Update Primary DocImage Logic
        // We need to check if the current 'DocImage' (primary) is valid or if it was deleted.
        // Simplified Logic: 
        // - Fetch one existing attachment (ORDER BY AttachmentID ASC LIMIT 1)
        // - Set it as DocImage. If no attachments, DocImage = NULL.

        // This ensures DocImage is always consistent with the Attachments table.

        $stmtFirst = $pdo->prepare("SELECT FilePath FROM DocumentAttachments WHERE DocID = ? ORDER BY AttachmentID ASC LIMIT 1");
        $stmtFirst->execute([$id]);
        $firstAttach = $stmtFirst->fetchColumn();

        $primaryImage = $firstAttach ? $firstAttach : null;


        // 4. Update Document Log
        $sql = "UPDATE DocumentLog SET Office = ?, Subject = ?, Description = ?, ReceivedBy = ?, Status = ?, DocImage = ? WHERE DocID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$office, $subject, $description, $receivedBy, $status, $primaryImage, $id]);

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
