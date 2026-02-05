<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$docId = $_GET['id'];

try {
    // 1. Fetch Document Details
    $stmt = $pdo->prepare("SELECT * FROM DocumentLog WHERE DocID = ?");
    $stmt->execute([$docId]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        http_response_code(404);
        echo json_encode(['error' => 'Document not found']);
        exit;
    }

    // 2. Fetch Attachments
    $stmtAttach = $pdo->prepare("SELECT * FROM DocumentAttachments WHERE DocID = ?");
    $stmtAttach->execute([$docId]);
    $attachments = $stmtAttach->fetchAll(PDO::FETCH_ASSOC);

    // 3. Return Data
    echo json_encode([
        'doc' => $doc,
        'attachments' => $attachments
    ]);

}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
