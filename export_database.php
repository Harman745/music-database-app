<?php
/**
 * Export Database to SQL File
 * Creates a backup .sql file of your database
 */

require_once 'db_config.php';

$output = '';
$timestamp = date('Y-m-d_H-i-s');
$filename = "music_database_backup_{$timestamp}.sql";

// Add header
$output .= "-- Music Database System SQL Backup\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
$output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$output .= "SET time_zone = \"+00:00\";\n\n";
$output .= "CREATE DATABASE IF NOT EXISTS `music_database_system`;\n";
$output .= "USE `music_database_system`;\n\n";

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Export each table
foreach ($tables as $table) {
    // Get CREATE TABLE statement
    $result = $conn->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_row();
    $output .= "\n-- Table: $table\n";
    $output .= "DROP TABLE IF EXISTS `$table`;\n";
    $output .= $row[1] . ";\n\n";
    
    // Get table data
    $result = $conn->query("SELECT * FROM `$table`");
    if ($result->num_rows > 0) {
        $output .= "-- Data for table: $table\n";
        while ($row = $result->fetch_assoc()) {
            $output .= "INSERT INTO `$table` VALUES (";
            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $conn->real_escape_string($value) . "'";
                }
            }
            $output .= implode(', ', $values) . ");\n";
        }
        $output .= "\n";
    }
}

// Save to file
file_put_contents($filename, $output);

echo "<!DOCTYPE html><html><head><title>Database Export</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1 { color: #1db954; }
.success { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
a { color: #1db954; text-decoration: none; font-weight: bold; }
</style></head><body>";

echo "<h1>✓ Database Exported Successfully!</h1>";
echo "<div class='success'>";
echo "<p><strong>File:</strong> $filename</p>";
echo "<p><strong>Size:</strong> " . round(filesize($filename) / 1024, 2) . " KB</p>";
echo "<p><a href='$filename' download>Download SQL File</a></p>";
echo "</div>";
echo "</body></html>";
?>
