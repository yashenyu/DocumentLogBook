<?php
/**
 * Downloads attachment(s) from the database.
 * Usage: 
 *   Single: download_attachment.php?id=<AttachmentID>
 *   Multiple (ZIP): download_attachment.php?doc_id=<DocID>
 */

session_start();
require_once 'config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized');
}

// Single attachment download
if (isset($_GET['id'])) {
    $attachmentId = (int)$_GET['id'];

    if ($attachmentId <= 0) {
        http_response_code(400);
        die('Invalid attachment ID.');
    }

    try {
        $stmt = $pdo->prepare("SELECT DocImage, FileType FROM DocumentAttachments WHERE AttachmentID = ?");
        $stmt->execute([$attachmentId]);
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attachment) {
            http_response_code(404);
            die('Attachment not found.');
        }

        // Determine file extension from MIME type
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf'
        ];
        $ext = $extensions[$attachment['FileType']] ?? 'bin';
        $filename = "attachment_{$attachmentId}.{$ext}";

        // Set headers for download
        header("Content-Type: " . $attachment['FileType']);
        header("Content-Length: " . strlen($attachment['DocImage']));
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Cache-Control: no-cache, must-revalidate");

        echo $attachment['DocImage'];
        exit;

    }
    catch (PDOException $e) {
        http_response_code(500);
        die('Database error.');
    }
}

// Multiple attachments download (ZIP)
if (isset($_GET['doc_id'])) {
    $docId = (int)$_GET['doc_id'];

    if ($docId <= 0) {
        http_response_code(400);
        die('Invalid document ID.');
    }

    try {
        // Get document subject for filename
        $stmtDoc = $pdo->prepare("SELECT Subject FROM DocumentLog WHERE DocID = ?");
        $stmtDoc->execute([$docId]);
        $doc = $stmtDoc->fetch(PDO::FETCH_ASSOC);

        if (!$doc) {
            http_response_code(404);
            die('Document not found.');
        }

        // Get all attachments
        $stmt = $pdo->prepare("SELECT AttachmentID, DocImage, FileType FROM DocumentAttachments WHERE DocID = ?");
        $stmt->execute([$docId]);
        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($attachments)) {
            http_response_code(404);
            die('No attachments found.');
        }

        // If only one attachment, download it directly
        if (count($attachments) === 1) {
            $att = $attachments[0];
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'application/pdf' => 'pdf'
            ];
            $ext = $extensions[$att['FileType']] ?? 'bin';
            $filename = "attachment_{$att['AttachmentID']}.{$ext}";

            header("Content-Type: " . $att['FileType']);
            header("Content-Length: " . strlen($att['DocImage']));
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Cache-Control: no-cache, must-revalidate");

            echo $att['DocImage'];
            exit;
        }

        // Multiple attachments - create ZIP
        $zipFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $doc['Subject']);
        $zipFilename = substr($zipFilename, 0, 50) . "_attachments.zip";

        // Create temp file for ZIP
        $tempFile = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            die('Failed to create ZIP file.');
        }

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf'
        ];

        foreach ($attachments as $index => $att) {
            $ext = $extensions[$att['FileType']] ?? 'bin';
            $entryName = "attachment_" . ($index + 1) . ".{$ext}";
            $zip->addFromString($entryName, $att['DocImage']);
        }

        $zip->close();

        // Send ZIP file
        header("Content-Type: application/zip");
        header("Content-Length: " . filesize($tempFile));
        header("Content-Disposition: attachment; filename=\"{$zipFilename}\"");
        header("Cache-Control: no-cache, must-revalidate");

        readfile($tempFile);
        unlink($tempFile); // Clean up
        exit;

    }
    catch (PDOException $e) {
        http_response_code(500);
        die('Database error.');
    }
}

http_response_code(400);
die('Missing required parameter.');
