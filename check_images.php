<?php
require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Check Images</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
table { border-collapse: collapse; width: 100%; background: white; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #1db954; color: white; }
img { max-width: 100px; height: auto; }
</style></head><body>";

echo "<h1>Current Images in Database</h1>";

echo "<h2>Albums</h2>";
$albums = fetchAll("SELECT album_title, cover_image_url FROM Album");
echo "<table><tr><th>Album</th><th>Image URL</th><th>Preview</th></tr>";
foreach ($albums as $album) {
    echo "<tr>";
    echo "<td>{$album['album_title']}</td>";
    echo "<td>" . ($album['cover_image_url'] ?: 'No image') . "</td>";
    echo "<td>";
    if ($album['cover_image_url']) {
        echo "<img src='{$album['cover_image_url']}' alt='Album cover'>";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Artists</h2>";
$artists = fetchAll("SELECT artist_name, profile_image_url FROM Artist");
echo "<table><tr><th>Artist</th><th>Image URL</th><th>Preview</th></tr>";
foreach ($artists as $artist) {
    echo "<tr>";
    echo "<td>{$artist['artist_name']}</td>";
    echo "<td>" . ($artist['profile_image_url'] ?: 'No image') . "</td>";
    echo "<td>";
    if ($artist['profile_image_url']) {
        echo "<img src='{$artist['profile_image_url']}' alt='Artist photo'>";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";

echo "</body></html>";
?>
