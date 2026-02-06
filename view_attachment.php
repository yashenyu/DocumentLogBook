<?php
/**
 * Serves attachment images/files from the database BLOB storage.
 * Usage: view_attachment.php?id=<AttachmentID>
 */

require_once 'config/database.php';

$attachmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

    // Set headers for the file type
    header("Content-Type: " . $attachment['FileType']);
    header("Content-Length: " . strlen($attachment['DocImage']));

    // For inline display (images will show, PDFs will open in browser)
    header("Content-Disposition: inline");

    // Output the binary data
    echo $attachment['DocImage'];
    exit;

}
catch (PDOException $e) {
    http_response_code(500);
    die('Database error.');
}
