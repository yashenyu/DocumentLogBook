<?php
session_start();
require_once '../config/database.php';

// Access Control: Only Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? '';

    if (!$userId) {
        echo json_encode(['error' => 'User ID is required.']);
        exit;
    }

    try {
        if ($action === 'change_password') {
            $newPassword = $_POST['new_password'] ?? '';
            if (empty($newPassword)) {
                echo json_encode(['error' => 'New password cannot be empty.']);
                exit;
            }
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE UserId = ?");
            if ($stmt->execute([$hashed, $userId])) {
                echo json_encode(['success' => 'Password updated successfully.']);
            }
            else {
                echo json_encode(['error' => 'Failed to update password.']);
            }
        }
        elseif ($action === 'delete_user') {
            // Prevent self-deletion
            if ($userId == $_SESSION['user_id']) {
                echo json_encode(['error' => 'You cannot delete your own account.']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE UserId = ?");
            if ($stmt->execute([$userId])) {
                echo json_encode(['success' => 'User deleted successfully.']);
            }
            else {
                echo json_encode(['error' => 'Failed to delete user.']);
            }
        }
        else {
            echo json_encode(['error' => 'Invalid action.']);
        }
    }
    catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
