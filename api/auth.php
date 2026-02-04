<?php
header('Content-Type: application/json');
session_start();

require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
$action = isset($data['action']) ? $data['action'] : '';

switch ($action) {
    case 'create_user':
        create_user($pdo, $data);
        break;
    case 'login':
        login($pdo, $data);
        break;
    case 'logout':
        logout();
        break;
    case 'check_session':
        check_session();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function create_user($pdo, $data)
{
    // Security Check: Only logged-in Admins can create users
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only Admins can create users']);
        return;
    }

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? 'Staff';

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password required']);
        return;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
        return;
    }

    // Validate role
    if (!in_array($role, ['Admin', 'Staff'])) {
        $role = 'Staff';
    }

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $hashed_password, $role])) {
        echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
    }
    else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create user']);
    }
}

function login($pdo, $data)
{
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password required']);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify Password Hash
    // Note: If you have LEGACY plain-text passwords, they will fail here.
    // You must use the setup_admin.php to create a fresh hashed admin.
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['UserId'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['status' => 'success', 'message' => 'Login successful', 'role' => $user['role']]);
    }
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
}

function logout()
{
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Logged out']);
}

function check_session()
{
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'logged_in', 'user' => $_SESSION['username'], 'role' => $_SESSION['role'] ?? 'Staff']);
    }
    else {
        echo json_encode(['status' => 'logged_out']);
    }
}
?>
