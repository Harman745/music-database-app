<?php
/**
 * Add Images to Albums and Artists
 * This adds cover images and artist photos
 * Developed by: Yuvraj Singh Bahia
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Add Images</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2, h3 { color: #333; }
.success { color: green; }
.btn { display: inline-block; padding: 10px 20px; background: #1db954; color: white; 
       text-decoration: none; border-radius: 4px; margin-top: 20px; }
</style></head><body>";

echo "<h1>🎨 Adding Images to Albums and Artists...</h1>";

// Album cover images (using placeholder images from picsum.photos)
$albumImages = [
    'Kind of Blue' => 'static/images/albums/kindofblue.jpg',
    'Abbey Road' => 'static/images/albums/abbeyroad.jpg',
    'The Dark Side of the Moon' => 'static/images/albums/darkside.jpg',
    'Led Zeppelin IV' => 'static/images/ledzeppelin4.jpg',
    'Nevermind' => 'static/images/nevermind.jpg',
    'OK Computer' => 'static/images/albums/okcomputer.jpg',
    'Highway 61 Revisited' => 'static/images/highway61.jpg',
    'Sgt. Pepper\'s Lonely Hearts Club Band' => 'static/images/sgtpepper.jpg',
    'The Wall' => 'static/images/thewall.jpg',
    'A Love Supreme' => 'static/images/alovesupreme.jpg'
];

// Artist profile images
$artistImages = [
    'Miles Davis' => 'static/images/milesdavis.avif',
    'The Beatles' => 'static/images/Thebeatles.jpg',
    'Pink Floyd' => 'static/images/Pink_Floyd.jpg',
    'Led Zeppelin' => 'static/images/ledzeppelin.jpg',
    'Nirvana' => 'static/images/nirvana.jpg',
    'Radiohead' => 'static/images/Radiohead.jpg',
    'Bob Dylan' => 'static/images/bobdylan.jpg',
    'John Coltrane' => 'static/images/johncoltrane.jpg'
];

echo "<h2>Updating Album Covers...</h2>";
$albumCount = 0;
foreach ($albumImages as $title => $imageUrl) {
    $stmt = $conn->prepare("UPDATE Album SET cover_image_url = ? WHERE album_title = ?");
    $stmt->bind_param('ss', $imageUrl, $title);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "<p class='success'>✓ Updated cover for: $title</p>";
        $albumCount++;
    }
    $stmt->close();
}

echo "<h2>Updating Artist Photos...</h2>";
$artistCount = 0;
foreach ($artistImages as $name => $imageUrl) {
    $stmt = $conn->prepare("UPDATE Artist SET profile_image_url = ? WHERE artist_name = ?");
    $stmt->bind_param('ss', $imageUrl, $name);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "<p class='success'>✓ Updated photo for: $name</p>";
        $artistCount++;
    }
    $stmt->close();
}

echo "<h2 style='color: green;'>✓ Complete!</h2>";
echo "<p>Updated $albumCount album covers and $artistCount artist photos.</p>";

echo "<p><a href='albums.php' class='btn'>View Albums</a> <a href='artists.php' class='btn'>View Artists</a></p>";
echo "</body></html>";
?>
