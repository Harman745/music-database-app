<?php
/**
 * Albums Page - Browse all albums
 * Developer: Harman Singh
 */
require_once 'db_config.php';

// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query based on search
if (!empty($search)) {
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
        WHERE a.album_title LIKE ? OR ar.artist_name LIKE ?
        GROUP BY a.album_id, a.album_title, a.cover_image_url, ar.artist_name
        ORDER BY a.album_title ASC
    ";
    $searchParam = "%$search%";
    $albums = fetchAll($query, 'ss', [$searchParam, $searchParam]);
} else {
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
        ORDER BY a.album_title ASC
    ";
    $albums = fetchAll($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>All Albums</h1>
            <p>Browse our complete collection of albums</p>
        </div>
    </div>

    <div class="container">
        <div class="filter-section">
            <form method="GET" action="albums.php" class="filter-form">
                <input type="text" name="search" placeholder="Search albums or artists..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-primary">Search</button>
            </form>
        </div>

        <div class="album-grid">
            <?php if (!empty($albums)): ?>
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
                                    (<?php echo $album['review_count']; ?>)
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-results">No albums found. Try adjusting your search.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
