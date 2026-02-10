<?php
// restore_db.php - Robust version
// Enable error reporting for debugging (will be captured by frontend if not JSON)
ini_set('display_errors', 0); // Don't output errors to screen, log them
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log to a file we can read
$logFile = __DIR__ . '/../restore_debug.log';
function debug_log($msg)
{
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

debug_log("Restore request started.");

header('Content-Type: application/json');

// Check POST size limit
$contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
$postMaxSize = ini_get('post_max_size');
$postMaxSizeBytes = return_bytes($postMaxSize);

debug_log("Content Length: $contentLength, Post Max Size: $postMaxSize ($postMaxSizeBytes bytes)");

if ($contentLength > $postMaxSizeBytes && empty($_FILES)) {
    $msg = "Upload failed: File size ($contentLength bytes) exceeds the server's post_max_size limit of $postMaxSize.";
    debug_log($msg);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// Session check
session_start();
if (($_SESSION['role'] ?? '') !== 'Admin') {
    debug_log("Unauthorized access attempt.");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../helpers/BackupHelper.php';

// Increase limits
set_time_limit(0);
ini_set('memory_limit', '-1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['backup_file'])) {
        $msg = 'No file uploaded or file too large (upload_max_filesize exceeded).';
        debug_log($msg);
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }

    $file = $_FILES['backup_file'];
    debug_log("File upload detected: " . $file['name'] . ", Error: " . $file['error'] . ", Size: " . $file['size']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = codeToMessage($file['error']);
        debug_log("Upload error: $errorMsg");
        echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $errorMsg]);
        exit;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'sql') {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a .sql file.']);
        exit;
    }

    try {
        debug_log("Starting restore process...");
        $backup = new BackupHelper();
        $backup->restore($file['tmp_name']);
        debug_log("Restore successful.");
        echo json_encode(['success' => true, 'message' => 'Database restored successfully!']);
    }
    catch (Throwable $e) {
        // Catch Throwable to catch Fatal Errors too if possible
        debug_log("Restore Exception: " . $e->getMessage());
        debug_log("Trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Restore failed: ' . $e->getMessage()]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Helpers
function return_bytes($val)
{
    if (empty($val))
        return 0;
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = (int)$val;
    switch ($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function codeToMessage($code)
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension";
        default:
            return "Unknown upload error";
    }
}
