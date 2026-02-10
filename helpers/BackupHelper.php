<?php
require_once __DIR__ . '/../config/database.php';

class BackupHelper
{
    private $pdo;

    public function __construct()
    {
        $host = DB_HOST;
        $name = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Try to increase max_allowed_packet for this session (might fail on some servers)
            try {
                $this->pdo->exec("SET SESSION max_allowed_packet=67108864"); // 64MB
            }
            catch (Exception $e) {
            // Ignore if we can't set it (e.g. read-only session variable in MariaDB < 10.2)
            }
        }
        catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function backupToStream($handle)
    {
        $tables = [];
        $stmt = $this->pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        fwrite($handle, "-- Document LogBook Backup\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $table) {
            // Get Create Table Schema
            $row = $this->pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($handle, $row[1] . ";\n\n");

            // Get Column Info to identify BLOBs
            $colStmt = $this->pdo->query("SHOW COLUMNS FROM `$table`");
            $columns = $colStmt->fetchAll(PDO::FETCH_ASSOC);
            $blobCols = [];
            foreach ($columns as $col) {
                // Check against multiple blob types and binary types
                $type = strtolower($col['Type']);
                if (strpos($type, 'blob') !== false || strpos($type, 'binary') !== false) {
                    $blobCols[] = $col['Field'];
                }
            }

            // Get Data
            // We use unbuffered query for large tables to save memory
            $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $rows = $this->pdo->query("SELECT * FROM `$table`");

            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $values = [];
                foreach ($row as $colName => $val) {
                    if ($val === null) {
                        $values[] = "NULL";
                    }
                    elseif (in_array($colName, $blobCols)) {
                        // Handle BLOBs with HEX literal: 0xA1B2...
                        $values[] = "0x" . bin2hex($val);
                    }
                    else {
                        // Use PDO::quote for safe string escaping
                        $values[] = $this->pdo->quote($val);
                    }
                }
                $line = "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                fwrite($handle, $line);
            }
            $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            fwrite($handle, "\n\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    }

    public function restore($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("Backup file not found.");
        }

        // Increase packet size for restore (try)
        try {
            $this->pdo->exec("SET SESSION max_allowed_packet=67108864");
        }
        catch (Exception $e) { /* ignore */
        }

        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new Exception("Error opening backup file.");
        }

        $query = '';
        $escaped = false;

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);

            // Skip pure comment lines or empty lines
            if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
                continue;
            }

            $query .= $line;

            // Check if statement ends with semicolon
            // This relies on the fact that our backup generator escapes newlines in data,
            // so a semicolon at the end of a line STRONGLY implies end of statement.
            if (substr($trimmed, -1) == ';') {
                try {
                    $this->pdo->exec($query);
                }
                catch (PDOException $e) {
                    fclose($handle);
                    throw new Exception("SQL Error: " . $e->getMessage());
                }
                $query = '';
            }
        }

        fclose($handle);
        return true;
    }
}
