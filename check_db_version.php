<?php
require_once 'db_config.php';
$result = $conn->query("SELECT VERSION()");
$version = $result->fetch_row()[0];
echo "Database: MariaDB/MySQL<br>";
echo "Version: $version";
?>
