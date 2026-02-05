<?php
session_start();
require_once 'config/database.php';

// Access Control: Only Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header('Location: documents.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'Staff';

    if (!empty($username) && !empty($password)) {
        try {
            // Check availability
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Username '$username' already exists.";
            }
            else {
                // Hash and Insert
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");

                if ($stmt->execute([$username, $hashed, $role])) {
                    $_SESSION['success'] = "New $role account '$username' created successfully!";
                }
                else {
                    $_SESSION['error'] = "Failed to create user account.";
                }
            }
        }
        catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
    else {
        $_SESSION['error'] = "All fields are required.";
    }
}

header('Location: documents.php');
exit;
