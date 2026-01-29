<?php
/**
 * Clean Database - Remove all data and reset
 * Use this if you have duplicate data
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Clean Database</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2 { color: #333; }
.warning { color: orange; font-weight: bold; }
.success { color: green; }
.btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; 
       color: white; text-decoration: none; border-radius: 4px; }
.btn-danger { background: #ff4444; }
.btn-primary { background: #1db954; }
</style></head><body>";

echo "<h1>🧹 Clean Database</h1>";

if (!isset($_POST['confirm'])) {
    echo "<p class='warning'>⚠️ This will DELETE ALL DATA from your database!</p>";
    echo "<p>This includes:</p>";
    echo "<ul>";
    echo "<li>All artists and albums</li>";
    echo "<li>All tracks and genres</li>";
    echo "<li>All reviews and favorites</li>";
    echo "<li>All achievements</li>";
    echo "<li><strong>User accounts will NOT be deleted</strong></li>";
    echo "</ul>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<button type='submit' class='btn btn-danger'>Yes, Delete All Data</button>";
    echo "<a href='index.php' class='btn btn-primary'>Cancel - Go Back</a>";
    echo "</form>";
} else {
    echo "<h2>Deleting all data...</h2>";
    
    try {
        // Delete in correct order (respecting foreign keys)
        $tables = [
            'User_Achievement',
            'Achievement',
            'Listening_Activity',
            'Album_Credit',
            'Artist_Influence',
            'Artist_Rating',
            'User_Favorite_Artist',
            'User_Favorite_Album',
            'Playlist_Track',
            'Review',
            'Track_Genre',
            'Track',
            'Album',
            'Artist',
            'Genre',
            'Playlist'
        ];
        
        foreach ($tables as $table) {
            $conn->query("DELETE FROM $table");
            echo "✓ Cleared table: $table<br>";
        }
        
        echo "<h2 class='success'>✓ All data deleted successfully!</h2>";
        echo "<p>Your database is now clean. What would you like to do?</p>";
        echo "<a href='insert_sample_data.php' class='btn btn-primary'>Add Sample Data</a>";
        echo "<a href='index.php' class='btn btn-primary'>Go to Home Page</a>";
        
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>";
    }
}

echo "</body></html>";
?>
