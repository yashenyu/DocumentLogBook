<?php
session_start();
require_once 'config/database.php';
require_once 'helpers/UploadHelper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $office = $_POST['office'] ?? $_POST['email'] ?? 'General';
        $subject = $_POST['doc_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $receivedBy = $_POST['received_by'] ?? '';
        $status = $_POST['status'] ?? 'Incoming';
        $docDate = date('Y-m-d');

        // Basic Validation
        if (empty($subject) || empty($office)) {
            $_SESSION['error'] = "Document Name and Office are required.";
            header('Location: documents.php');
            exit;
        }

        // Conditional Validation for Outgoing
        if ($status === 'Outgoing' && empty($receivedBy)) {
            $_SESSION['error'] = "Received By is required for Outgoing documents.";
            header('Location: documents.php');
            exit;
        }

        // Handle File Upload via Helper
        $bucket = [];
        if (isset($_FILES['doc_image']) && !empty($_FILES['doc_image']['name'][0])) {
            try {
                $bucket = UploadHelper::handleUploads($_FILES['doc_image']);
            }
            catch (Exception $e) {
                $_SESSION['error'] = "Upload failed: " . $e->getMessage();
                header('Location: documents.php');
                exit;
            }
        }

        $primaryImagePath = (count($bucket) > 0) ? $bucket[0] : null;

        // Insert Document Log
        $sql = "INSERT INTO DocumentLog (DocDate, Office, Subject, Description, ReceivedBy, Status, DocImage) 
            VALUES (:docDate, :office, :subject, :description, :receivedBy, :status, :docImage)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':docDate' => $docDate,
            ':office' => $office,
            ':subject' => $subject,
            ':description' => $description,
            ':receivedBy' => $receivedBy,
            ':status' => $status,
            ':docImage' => $primaryImagePath
        ]);

        $newDocID = $pdo->lastInsertId();

        // Insert Attributes/Attachments
        if ($newDocID && count($bucket) > 0) {
            $sqlAttach = "INSERT INTO DocumentAttachments (DocID, FilePath) VALUES (?, ?)";
            $stmtAttach = $pdo->prepare($sqlAttach);
            foreach ($bucket as $path) {
                $stmtAttach->execute([$newDocID, $path]);
            }
        }

        $_SESSION['success'] = "Document added successfully!";
        header('Location: documents.php');
        exit;
    }
    catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header('Location: documents.php');
        exit;
    }
}
else {
    header('Location: documents.php');
    exit;
}
