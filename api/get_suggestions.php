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

$results = [];

if ($type === 'office') {
    require_once '../helpers/OfficeHelper.php';
    $allOffices = OfficeHelper::getOffices();

    // Filter locally since data is in JSON/Array
    $results = array_filter($allOffices, function ($office) use ($query) {
        return stripos($office, $query) !== false;
    });

    // Slice to limit results
    $results = array_slice(array_values($results), 0, 10);

}
elseif ($type === 'received_by') {
    $sql = "SELECT DISTINCT ReceivedBy FROM DocumentLog WHERE ReceivedBy LIKE ? LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

echo json_encode($results);
?>
