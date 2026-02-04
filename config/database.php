<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'document_logger');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // 1. Connect to MySQL Server (no DB selected yet)
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create Database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);

    // 3. Connect to the specific database
    $pdo->exec("USE " . DB_NAME);

    // 4. Check if tables exist, if not run schema
    // Simple check: see if 'users' table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        $schemaPath = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            // Execute schema. Splitting by ';' to handle multiple statements if PDO doesn't support multi-query in one go nicely everywhere, 
            // though standard PDO can sometimes fail on multiple statements depending on config. 
            // Safer to split.
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    $pdo->exec($stmt);
                }
            }
        }
    }
    else {
        // Auto-migration: Check if 'role' column exists in users table, if not add it
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('Admin', 'Staff') NOT NULL DEFAULT 'Staff'");
        }
    }

}
catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>