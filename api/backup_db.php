<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'Admin') {
    http_response_code(403);
    die('Unauthorized');
}

require_once __DIR__ . '/../helpers/BackupHelper.php';

// Get custom filename or default
$userFilename = $_GET['filename'] ?? 'db_backup';
// Sanitize: allow alphanumeric, underscores, hyphens
$userFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $userFilename);
if (empty($userFilename)) {
    $userFilename = 'db_backup';
}

$finalFilename = $userFilename . '_' . date('Y-m-d_H-i-s') . '.sql';

// Set headers for download
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $finalFilename . '"');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $backup = new BackupHelper();
    $out = fopen('php://output', 'w');
    $backup->backupToStream($out);
    fclose($out);
}
catch (Exception $e) {
    // If output has started, we can't change headers, but we can append error
    // Ideally log error
    error_log("Backup failed: " . $e->getMessage());
    echo "\n-- BACKUP FAILED: " . $e->getMessage();
}
exit;
