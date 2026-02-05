<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$type = $_GET['type'] ?? ''; // 'office' or 'received_by'
$query = $_GET['query'] ?? '';

if (empty($type) || empty($query)) {
    echo json_encode([]);
    exit;
}

$column = ($type === 'office') ? 'Office' : 'ReceivedBy';
$sql = "SELECT DISTINCT $column FROM DocumentLog WHERE $column LIKE ? LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$query%"]);
$results = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($results);
?>
