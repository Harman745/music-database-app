<?php
/**
 * Artist Detail Page - Shows artist albums and info
 * Developers: Harman Singh & Michel
 */
require_once 'db_config.php';

$artist_id = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;

if ($artist_id <= 0) {
    header('Location: artists.php');
    exit;
}

// Fetch artist details
$query = "
    SELECT 
        artist_id,
        artist_name,
        biography,
        country,
        formed_year,
        image_url
    FROM Artist
    WHERE artist_id = ?
";
$artist = fetchOne($query, 'i', [$artist_id]);

if (!$artist) {
    header('Location: artists.php');
    exit;
}

// Fetch artist's albums
$albumsQuery = "
    SELECT 
        a.album_id,
        a.album_title,
        a.cover_image_url,
        a.release_date,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.review_id) as review_count
    FROM Album a
    LEFT JOIN Review r ON a.album_id = r.album_id
    WHERE a.artist_id = ?
    GROUP BY a.album_id, a.album_title, a.cover_image_url, a.release_date
    ORDER BY a.release_date DESC
";
$albums = fetchAll($albumsQuery, 'i', [$artist_id]);

// Check if favorited by current user
$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $favQuery = "SELECT 1 FROM User_Favorite_Artist WHERE user_id = ? AND artist_id = ?";
    $is_favorite = fetchOne($favQuery, 'ii', [$_SESSION['user_id'], $artist_id]) !== null;
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    if ($is_favorite) {
        executeQuery("DELETE FROM User_Favorite_Artist WHERE user_id = ? AND artist_id = ?", 'ii', [$_SESSION['user_id'], $artist_id]);
    } else {
        executeQuery("INSERT INTO User_Favorite_Artist (user_id, artist_id) VALUES (?, ?)", 'ii', [$_SESSION['user_id'], $artist_id]);
    }
    header("Location: artist_detail.php?artist_id=$artist_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artist['artist_name']); ?> - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="artist-detail-page">
        <div class="container">
            <div class="artist-header">
                <div class="artist-image-large">
                    <?php if (!empty($artist['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($artist['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($artist['artist_name']); ?>" 
                             onerror="this.src='/static/images/default-artist.jpg'">
                    <?php else: ?>
                        <div class="artist-placeholder-large">🎤</div>
                    <?php endif; ?>
                </div>
                <div class="artist-meta">
                    <h1><?php echo htmlspecialchars($artist['artist_name']); ?></h1>
                    <p class="artist-country"><strong>Country:</strong> <?php echo htmlspecialchars($artist['country'] ?: 'Unknown'); ?></p>
                    <?php if ($artist['formed_year']): ?>
                        <p class="artist-year"><strong>Formed:</strong> <?php echo $artist['formed_year']; ?></p>
                    <?php endif; ?>
                    
                    <div class="artist-actions">
                        <?php if (isset($_SESSION['username'])): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="toggle_favorite" value="1">
                                <button type="submit" class="btn-primary">
                                    <?php echo $is_favorite ? '❤️ Remove from Favorites' : '🤍 Add to Favorites'; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn-secondary">Login to Favorite</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($artist['biography'])): ?>
            <section class="artist-bio">
                <h3>Biography</h3>
                <p><?php echo nl2br(htmlspecialchars($artist['biography'])); ?></p>
            </section>
            <?php endif; ?>

            <section class="artist-albums">
                <h3>Albums (<?php echo count($albums); ?>)</h3>
                <div class="album-grid">
                    <?php foreach ($albums as $album): ?>
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
                                <p class="release-date"><?php echo $album['release_date']; ?></p>
                                <div class="rating">
                                    <span class="stars">
                                        <?php
                                        $rating = (float)$album['avg_rating'];
                                        for ($i = 0; $i < 5; $i++) {
                                            echo $i < $rating ? '★' : '☆';
                                        }
                                        ?>
                                    </span>
                                    <span class="rating-text">
                                        <?php echo number_format($album['avg_rating'], 1); ?>
                                        (<?php echo $album['review_count']; ?>)
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
