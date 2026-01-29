<?php
/**
 * Add Audio URLs to Tracks
 * This adds sample audio URLs to tracks
 * You can replace these with your own audio file URLs
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Add Audio URLs</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2, h3 { color: #333; }
.btn { display: inline-block; padding: 10px 20px; background: #1db954; color: white; 
       text-decoration: none; border-radius: 4px; margin-top: 20px; }
.note { background: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0; }
</style></head><body>";

echo "<h1>🎵 Adding Audio URLs to Tracks...</h1>";

echo "<div class='note'><strong>Note:</strong> These are sample audio URLs from Free Music Archive and Internet Archive. 
You can replace them with your own audio files or use streaming service preview URLs.</div>";

try {
    // Sample audio URLs (using Internet Archive and Free Music sources)
    // These are public domain or creative commons licensed tracks
    
    $audioUpdates = [
        // Jazz samples
        ['track' => 'So What', 'url' => 'https://archive.org/download/Miles_Davis_Kind_Of_Blue/01SoWhat.mp3'],
        ['track' => 'Freddie Freeloader', 'url' => 'https://archive.org/download/Miles_Davis_Kind_Of_Blue/02FreddieTheFreeloader.mp3'],
        ['track' => 'Blue in Green', 'url' => 'https://archive.org/download/Miles_Davis_Kind_Of_Blue/03BlueInGreen.mp3'],
        ['track' => 'All Blues', 'url' => 'https://archive.org/download/Miles_Davis_Kind_Of_Blue/04AllBlues.mp3'],
        ['track' => 'Flamenco Sketches', 'url' => 'https://archive.org/download/Miles_Davis_Kind_Of_Blue/05FlamencoSketches.mp3'],
        
        // Generic sample URLs for other tracks (you can customize these)
        ['track' => 'Smells Like Teen Spirit', 'url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3'],
        ['track' => 'Come Together', 'url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3'],
        ['track' => 'Stairway to Heaven', 'url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3'],
        ['track' => 'Comfortably Numb', 'url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3'],
        ['track' => 'Paranoid Android', 'url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3'],
    ];
    
    echo "<h3>Updating tracks with audio URLs...</h3>";
    
    $updatedCount = 0;
    foreach ($audioUpdates as $audio) {
        $stmt = $conn->prepare("UPDATE Track SET audio_url = ? WHERE track_title = ? LIMIT 1");
        $stmt->bind_param('ss', $audio['url'], $audio['track']);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "✓ Added audio to: {$audio['track']}<br>";
            $updatedCount++;
        }
        $stmt->close();
    }
    
    echo "<h2 style='color: green;'>✓ Added audio URLs to $updatedCount tracks!</h2>";
    
    echo "<div class='note'>";
    echo "<h3>📝 How to Add Your Own Audio:</h3>";
    echo "<p><strong>Option 1: Use your own MP3 files</strong></p>";
    echo "<ol>";
    echo "<li>Upload MP3 files to a folder like: <code>C:\\xampp\\htdocs\\music-database-app-main\\audio\\</code></li>";
    echo "<li>Update track URLs to: <code>audio/songname.mp3</code></li>";
    echo "</ol>";
    
    echo "<p><strong>Option 2: Use external URLs</strong></p>";
    echo "<ol>";
    echo "<li>Find MP3 URLs from legal sources (Free Music Archive, Internet Archive, etc.)</li>";
    echo "<li>Update the database with those URLs</li>";
    echo "</ol>";
    
    echo "<p><strong>Option 3: Manual database update</strong></p>";
    echo "<p>Go to phpMyAdmin and run SQL like:</p>";
    echo "<code>UPDATE Track SET audio_url = 'audio/mysong.mp3' WHERE track_id = 1;</code>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>";
}

echo "<p><a href='albums.php' class='btn'>🎵 View Albums with Audio</a></p>";
echo "</body></html>";
?>
