<?php
require_once 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS DocumentAttachments (
        AttachmentID INT AUTO_INCREMENT PRIMARY KEY,
        DocID INT NOT NULL,
        DocImage LONGBLOB NOT NULL,
        FileType VARCHAR(50) NOT NULL,
        UploadedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (DocID) REFERENCES DocumentLog(DocID) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "Table 'DocumentAttachments' created successfully.";
}
catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
