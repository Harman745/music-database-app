<?php
/**
 * Home Page - Music Database System
 * Done by Yuvraj Singh Bahia 24129212
 */

require_once 'db_config.php';

// Fetch featured albums with artist names and average ratings
$query = "
    SELECT 
        a.album_id,
        a.album_title,
        a.cover_image_url,
        ar.artist_name,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.review_id) as review_count
    FROM Album a
    INNER JOIN Artist ar ON a.artist_id = ar.artist_id
    LEFT JOIN Review r ON a.album_id = r.album_id
    GROUP BY a.album_id, a.album_title, a.cover_image_url, ar.artist_name
    ORDER BY avg_rating DESC, review_count DESC
    LIMIT 8
";

$albums = fetchAll($query);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>Discover Amazing Music</h1>
            <p>Explore millions of songs, albums, and artists. Create playlists, write reviews, and manage your music library.</p>
            <div class="hero-actions">
                <?php if (!$isLoggedIn): ?>
                    <a href="register.php" class="btn-large btn-primary">Get Started</a>
                    <a href="albums.php" class="btn-large btn-secondary">Browse Albums</a>
                <?php else: ?>
                    <a href="albums.php" class="btn-large btn-primary">Browse Albums</a>
                    <a href="library.php" class="btn-large btn-secondary">My Library</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Featured Albums Section -->
    <section class="featured-section">
        <div class="container">
            <h2>Featured Albums</h2>
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
                            <p class="artist-name"><?php echo htmlspecialchars($album['artist_name']); ?></p>
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
                                    (<?php echo $album['review_count']; ?> reviews)
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2>Why Choose Our Music Database?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🎵</div>
                    <h3>Vast Collection</h3>
                    <p>Explore thousands of albums, artists, and tracks across all genres.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3>Reviews & Ratings</h3>
                    <p>Read and write reviews, rate albums, and discover what others love.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>Personal Library</h3>
                    <p>Build your own music library with favorites and custom playlists.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Advanced Search</h3>
                    <p>Find exactly what you're looking for with powerful search filters.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
