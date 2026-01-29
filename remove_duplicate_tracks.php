<?php
/**
 * Remove Duplicate Tracks
 * Keeps only one instance of each track per album
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Remove Duplicate Tracks</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2 { color: #333; }
.btn { display: inline-block; padding: 10px 20px; background: #1db954; color: white; 
       text-decoration: none; border-radius: 4px; margin-top: 20px; }
</style></head><body>";

echo "<h1>🧹 Removing Duplicate Tracks...</h1>";

try {
    // Find and delete duplicate tracks
    $query = "
        DELETE t1 FROM Track t1
        INNER JOIN Track t2 
        WHERE t1.track_id > t2.track_id 
        AND t1.album_id = t2.album_id 
        AND t1.track_number = t2.track_number
    ";
    
    $conn->query($query);
    $deletedCount = $conn->affected_rows;
    
    echo "<h2 style='color: green;'>✓ Removed $deletedCount duplicate tracks!</h2>";
    echo "<p>Each track now appears only once.</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>";
}

echo "<p><a href='albums.php' class='btn'>🎵 View Albums</a></p>";
echo "</body></html>";
?>
