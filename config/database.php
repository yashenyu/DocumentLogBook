<?php
// Database configuration
// TODO: Update these values for your setup

define('DB_HOST', 'localhost');
define('DB_NAME', 'document_logger');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create PDO connection
// Team can modify this approach if needed
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>