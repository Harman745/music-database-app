<?php
/**
 * Library Page - User's favorite albums and artists
 * Developer: Hasham
 */
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch favorite albums
$favAlbumsQuery = "
    SELECT 
        a.album_id,
        a.album_title,
        a.cover_image_url,
        ar.artist_name,
        ufa.favorited_date
    FROM User_Favorite_Album ufa
    INNER JOIN Album a ON ufa.album_id = a.album_id
    INNER JOIN Artist ar ON a.artist_id = ar.artist_id
    WHERE ufa.user_id = ?
    ORDER BY ufa.favorited_date DESC
";
$favoriteAlbums = fetchAll($favAlbumsQuery, 'i', [$user_id]);

// Fetch favorite artists
$favArtistsQuery = "
    SELECT 
        ar.artist_id,
        ar.artist_name,
        ar.image_url,
        ar.country,
        ufa.favorited_date
    FROM User_Favorite_Artist ufa
    INNER JOIN Artist ar ON ufa.artist_id = ar.artist_id
    WHERE ufa.user_id = ?
    ORDER BY ufa.favorited_date DESC
";
$favoriteArtists = fetchAll($favArtistsQuery, 'i', [$user_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>My Library</h1>
            <p>Your favorite albums and artists</p>
        </div>
    </div>

    <div class="container">
        <!-- Favorite Albums -->
        <section class="library-section">
            <h2>Favorite Albums (<?php echo count($favoriteAlbums); ?>)</h2>
            <?php if (!empty($favoriteAlbums)): ?>
                <div class="album-grid">
                    <?php foreach ($favoriteAlbums as $album): ?>
                    <div class="album-card">
                        <a href="album_detail.php?album_id=<?php echo $album['album_id']; ?>">
                            <div class="album-cover">
                                <?php if (!empty($album['cover_image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($album['cover_image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($album['album_title']); ?>" 
                                         onerror="this.src='/static/images/default-album.jpg'">
                                <?php else: ?>
                                    <div class="album-placeholder">🎵</div>
                                <?php endif; ?>
                            </div>
                            <div class="album-info">
                                <h3><?php echo htmlspecialchars($album['album_title']); ?></h3>
                                <p class="artist-name"><?php echo htmlspecialchars($album['artist_name']); ?></p>
                                <p class="favorited-date">Added: <?php echo date('M d, Y', strtotime($album['favorited_date'])); ?></p>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-favorites">You haven't added any favorite albums yet. <a href="albums.php">Browse albums</a></p>
            <?php endif; ?>
        </section>

        <!-- Favorite Artists -->
        <section class="library-section">
            <h2>Favorite Artists (<?php echo count($favoriteArtists); ?>)</h2>
            <?php if (!empty($favoriteArtists)): ?>
                <div class="artists-grid">
                    <?php foreach ($favoriteArtists as $artist): ?>
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
                                <p class="favorited-date">Added: <?php echo date('M d, Y', strtotime($artist['favorited_date'])); ?></p>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-favorites">You haven't added any favorite artists yet. <a href="artists.php">Browse artists</a></p>
            <?php endif; ?>
        </section>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
