<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Collect and sanitize input
        // The form currently has 'email', 'doc_name', 'description', 'received_by'
        // We map 'email' to 'Office' if that's the intention, or we should have updated the form.
        // Assuming we will update the form to send 'office' instead of 'email'.
        // If 'office' is not set, we might fallback to 'email' or empty.

        $office = $_POST['office'] ?? $_POST['email'] ?? 'General';
        $subject = $_POST['doc_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $receivedBy = $_POST['received_by'] ?? '';
        $status = $_POST['status'] ?? 'Incoming';
        $docDate = date('Y-m-d'); // Current date

        // Basic Validation
        if (empty($subject) || empty($office)) {
            $_SESSION['error'] = "Document Name and Office are required.";
            header('Location: add_document.php');
            exit;
        }

        // Conditional Validation for Outgoing
        if ($status === 'Outgoing' && empty($receivedBy)) {
            $_SESSION['error'] = "Received By is required for Outgoing documents.";
            header('Location: add_document.php');
            exit;
        }

        // If Incoming and empty receivedBy, maybe we want to store NULL in DB?
        // For now, empty string is fine as column is VARCHAR.
        // The previous code defaulted to session username. Let's keep that default if it was auto-filled, 
        // but if the user CLEARED it, we allow it to be empty for Incoming.
        // The previous lines: $receivedBy = $_POST['received_by'] ?? $_SESSION['username'];
        // My new line: $receivedBy = $_POST['received_by'] ?? ''; 
        // This allows empty string.


        // Handle File Upload
        // Handle File Upload
        $primaryImagePath = null;

        // Check if files are uploaded
        if (isset($_FILES['doc_image']) && !empty($_FILES['doc_image']['name'][0])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $bucket = [];
            $fileCount = count($_FILES['doc_image']['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = basename($_FILES["doc_image"]["name"][$i]);
                // Generate unique name
                $targetPath = $uploadDir . time() . "_" . uniqid() . "_" . $fileName;
                $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf');
                if (in_array($fileType, $allowTypes)) {
                    if (move_uploaded_file($_FILES["doc_image"]["tmp_name"][$i], $targetPath)) {
                        $bucket[] = $targetPath;
                    }
                }
            }

            // Use first image as primary
            if (count($bucket) > 0) {
                $primaryImagePath = $bucket[0];
            }

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

        }
        else {
            // No files
            $sql = "INSERT INTO DocumentLog (DocDate, Office, Subject, Description, ReceivedBy, Status, DocImage) 
                VALUES (:docDate, :office, :subject, :description, :receivedBy, :status, NULL)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':docDate' => $docDate,
                ':office' => $office,
                ':subject' => $subject,
                ':description' => $description,
                ':receivedBy' => $receivedBy,
                ':status' => $status
            ]);
        }

        $_SESSION['success'] = "Document added successfully!";
        header('Location: documents.php');
        exit;
    }
    catch (PDOException $e) {
        die("DB ERROR: " . $e->getMessage());    }


}
else {
    header('Location: add_document.php');
    exit;
}
