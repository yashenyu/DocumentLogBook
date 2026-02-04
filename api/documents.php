<?php
header('Content-Type: application/json');
session_start();

require_once '../config/database.php';

// Check auth
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
        break;
}

function handleGet($pdo)
{
    $search = $_GET['search'] ?? '';
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM DocumentLog WHERE Subject LIKE ? OR Description LIKE ? OR Office LIKE ? ORDER BY DocDate DESC");
        $term = "%$search%";
        $stmt->execute([$term, $term, $term]);
    }
    else {
        $stmt = $pdo->query("SELECT * FROM DocumentLog ORDER BY DocDate DESC");
    }
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $logs]);
}

function handlePost($pdo)
{
    $data = json_decode(file_get_contents("php://input"), true);

    $sql = "INSERT INTO DocumentLog (DocDate, Office, Subject, Description, ReceivedBy, Status, DocImage) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            date('Y-m-d'), // Auto-set current date
            $data['Office'],
            $data['Subject'],
            $data['Description'],
            $data['ReceivedBy'],
            $data['Status'], // Status is reserved word in some contexts, but fine as column name usually.
            $data['DocImage'] ?? ''
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Document added']);
    }
    catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handlePut($pdo)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['DocID'])) {
        echo json_encode(['status' => 'error', 'message' => 'DocID required']);
        return;
    }

    $sql = "UPDATE DocumentLog SET Office=?, Subject=?, Description=?, ReceivedBy=?, Status=?, DocImage=? WHERE DocID=?";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            $data['Office'],
            $data['Subject'],
            $data['Description'],
            $data['ReceivedBy'],
            $data['Status'],
            $data['DocImage'] ?? '',
            $data['DocID']
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Document updated']);
    }
    catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function handleDelete($pdo)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['DocID'])) {
        // Fallback to query param if not in body
        $DocID = $_GET['DocID'] ?? null;
    }
    else {
        $DocID = $data['DocID'];
    }

    if (!$DocID) {
        echo json_encode(['status' => 'error', 'message' => 'DocID required']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM DocumentLog WHERE DocID = ?");
    try {
        $stmt->execute([$DocID]);
        echo json_encode(['status' => 'success', 'message' => 'Document deleted']);
    }
    catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
