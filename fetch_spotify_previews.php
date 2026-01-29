<?php
/**
 * Fetch Spotify Preview URLs (30-second clips - LEGAL)
 * Perfect for university projects - no API key required for search
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Fetch Spotify Previews</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2, h3 { color: #333; }
.success { color: green; }
.warning { color: orange; }
.btn { display: inline-block; padding: 10px 20px; background: #1db954; color: white; 
       text-decoration: none; border-radius: 4px; margin-top: 20px; }
</style></head><body>";

echo "<h1>🎵 Fetching Spotify Preview URLs...</h1>";
echo "<p><strong>Note:</strong> These are legal 30-second preview clips from Spotify</p>";

// Get all tracks from database
$tracks = fetchAll("SELECT t.track_id, t.track_title, a.artist_name, al.album_title 
                    FROM Track t 
                    JOIN Album al ON t.album_id = al.album_id 
                    JOIN Artist a ON al.artist_id = a.artist_id
                    WHERE t.audio_url IS NULL OR t.audio_url = ''
                    LIMIT 50");

$updated = 0;
$notFound = 0;

foreach ($tracks as $track) {
    // Search Spotify (using their public search - no auth needed for basic queries)
    $searchQuery = urlencode($track['track_title'] . ' ' . $track['artist_name']);
    $spotifyUrl = "https://api.spotify.com/v1/search?q={$searchQuery}&type=track&limit=1";
    
    // Note: For production, you'd need OAuth token. For demo/testing, try iTunes instead
    echo "🔍 Searching: {$track['track_title']} by {$track['artist_name']}<br>";
    
    // Use iTunes API instead (no authentication required!)
    $itunesUrl = "https://itunes.apple.com/search?term={$searchQuery}&media=music&entity=song&limit=1";
    
    $response = @file_get_contents($itunesUrl);
    if ($response) {
        $data = json_decode($response, true);
        
        if (!empty($data['results'][0]['previewUrl'])) {
            $previewUrl = $data['results'][0]['previewUrl'];
            
            $stmt = $conn->prepare("UPDATE Track SET audio_url = ? WHERE track_id = ?");
            $stmt->bind_param('si', $previewUrl, $track['track_id']);
            $stmt->execute();
            $stmt->close();
            
            echo "<span class='success'>✓ Found preview for: {$track['track_title']}</span><br>";
            $updated++;
        } else {
            echo "<span class='warning'>⚠ No preview found for: {$track['track_title']}</span><br>";
            $notFound++;
        }
        
        // Respect API rate limits
        usleep(200000); // 0.2 second delay
    }
}

echo "<h2>Results:</h2>";
echo "<p class='success'>✓ Updated: $updated tracks</p>";
echo "<p class='warning'>⚠ Not found: $notFound tracks</p>";

echo "<p><a href='albums.php' class='btn'>🎵 View Albums</a></p>";
echo "</body></html>";
?>
