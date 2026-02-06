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

    // Check for DocumentAttachments table and create if not exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'DocumentAttachments'");
    if ($stmt->rowCount() == 0) {
        $sql = "CREATE TABLE DocumentAttachments (
            AttachmentID INT AUTO_INCREMENT PRIMARY KEY,
            DocID INT NOT NULL,
            DocImage LONGBLOB NOT NULL,
            FileType VARCHAR(50) NOT NULL,
            UploadedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (DocID) REFERENCES DocumentLog(DocID) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
    }
    else {
        // Schema Migration for BLOB
        // 1. Add DocImage (BLOB) if not exists
        $stmt = $pdo->query("SHOW COLUMNS FROM DocumentAttachments LIKE 'DocImage'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE DocumentAttachments ADD COLUMN DocImage LONGBLOB NOT NULL AFTER DocID");
        }

        // 2. Add FileType if not exists
        $stmt = $pdo->query("SHOW COLUMNS FROM DocumentAttachments LIKE 'FileType'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE DocumentAttachments ADD COLUMN FileType VARCHAR(50) NOT NULL AFTER DocImage");
        }

        // 3. Drop FilePath if exists (CAUTION: This deletes old path data)
        $stmt = $pdo->query("SHOW COLUMNS FROM DocumentAttachments LIKE 'FilePath'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE DocumentAttachments DROP COLUMN FilePath");
        }

        // 4. Add UploadedAt if not exists
        $stmt = $pdo->query("SHOW COLUMNS FROM DocumentAttachments LIKE 'UploadedAt'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE DocumentAttachments ADD COLUMN UploadedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER FileType");
        }
    }

    // Remove DocImage column from DocumentLog if it exists (since we use Attachments now)
    $stmt = $pdo->query("SHOW COLUMNS FROM DocumentLog LIKE 'DocImage'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE DocumentLog DROP COLUMN DocImage");
    }

}
catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>