<?php
/**
 * Artists Page - Browse all artists
 * Developers: Harman Singh & Michel
 */
require_once 'db_config.php';

// Fetch all artists with album count
$query = "
    SELECT 
        ar.artist_id,
        ar.artist_name,
        ar.country,
        ar.profile_image_url as image_url,
        COUNT(a.album_id) as album_count
    FROM Artist ar
    LEFT JOIN Album a ON ar.artist_id = a.artist_id
    GROUP BY ar.artist_id, ar.artist_name, ar.country, ar.profile_image_url
    ORDER BY ar.artist_name ASC
";

$artists = fetchAll($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artists - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>All Artists</h1>
            <p>Discover artists from around the world</p>
        </div>
    </div>

    <div class="container">
        <div class="artists-grid">
            <?php foreach ($artists as $artist): ?>
            <div class="artist-card">
                <a href="artist_detail.php?artist_id=<?php echo $artist['artist_id']; ?>">
                    <div class="artist-image">
                        <?php if (!empty($artist['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($artist['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($artist['artist_name']); ?>" 
                                 onerror="this.src='/static/images/default-artist.jpg'">
                        <?php else: ?>
                            <div class="artist-placeholder">🎤</div>
                        <?php endif; ?>
                    </div>
                    <div class="artist-info">
                        <h3><?php echo htmlspecialchars($artist['artist_name']); ?></h3>
                        <p class="artist-country"><?php echo htmlspecialchars($artist['country'] ?: 'Unknown'); ?></p>
                        <p class="artist-albums"><?php echo $artist['album_count']; ?> album(s)</p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
