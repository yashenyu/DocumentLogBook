<?php
require_once 'config/database.php';
echo "--- DocumentLog ---\n";
$stmt = $pdo->query("DESCRIBE DocumentLog");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\n--- DocumentAttachments ---\n";
$stmt = $pdo->query("DESCRIBE DocumentAttachments");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
