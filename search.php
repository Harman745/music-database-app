<?php
/**
 * Search Page
 * Developer: Michel
 */
require_once 'db_config.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = ['albums' => [], 'artists' => [], 'tracks' => []];

if (!empty($query)) {
    $searchParam = "%$query%";
    
    // Search albums
    $albumsQuery = "
        SELECT 
            a.album_id,
            a.album_title,
            a.cover_image_url,
            ar.artist_name
        FROM Album a
        INNER JOIN Artist ar ON a.artist_id = ar.artist_id
        WHERE a.album_title LIKE ?
        LIMIT 20
    ";
    $results['albums'] = fetchAll($albumsQuery, 's', [$searchParam]);
    
    // Search artists
    $artistsQuery = "
        SELECT 
            artist_id,
            artist_name,
            image_url,
            country
        FROM Artist
        WHERE artist_name LIKE ?
        LIMIT 20
    ";
    $results['artists'] = fetchAll($artistsQuery, 's', [$searchParam]);
    
    // Search tracks
    $tracksQuery = "
        SELECT 
            t.track_id,
            t.track_title,
            t.duration_seconds,
            a.album_title,
            ar.artist_name
        FROM Track t
        INNER JOIN Album a ON t.album_id = a.album_id
        INNER JOIN Artist ar ON a.artist_id = ar.artist_id
        WHERE t.track_title LIKE ?
        LIMIT 20
    ";
    $results['tracks'] = fetchAll($tracksQuery, 's', [$searchParam]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Search Results</h1>
            <?php if (!empty($query)): ?>
                <p>Results for "<?php echo htmlspecialchars($query); ?>"</p>
            <?php else: ?>
                <p>Enter a search term to find music</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <!-- Search Form -->
        <div class="search-form-container">
            <form method="GET" action="search.php" class="search-form">
                <input type="text" name="q" placeholder="Search for albums, artists, or tracks..." 
                       value="<?php echo htmlspecialchars($query); ?>" autofocus>
                <button type="submit" class="btn-primary">Search</button>
            </form>
        </div>

        <?php if (!empty($query)): ?>
            <!-- Albums Results -->
            <?php if (!empty($results['albums'])): ?>
            <section class="search-section">
                <h2>Albums (<?php echo count($results['albums']); ?>)</h2>
                <div class="album-grid">
                    <?php foreach ($results['albums'] as $album): ?>
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
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Artists Results -->
            <?php if (!empty($results['artists'])): ?>
            <section class="search-section">
                <h2>Artists (<?php echo count($results['artists']); ?>)</h2>
                <div class="artists-grid">
                    <?php foreach ($results['artists'] as $artist): ?>
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
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Tracks Results -->
            <?php if (!empty($results['tracks'])): ?>
            <section class="search-section">
                <h2>Tracks (<?php echo count($results['tracks']); ?>)</h2>
                <div class="tracks-list">
                    <?php foreach ($results['tracks'] as $track): ?>
                    <div class="track-result">
                        <div class="track-info">
                            <h3><?php echo htmlspecialchars($track['track_title']); ?></h3>
                            <p><?php echo htmlspecialchars($track['artist_name']); ?> - <?php echo htmlspecialchars($track['album_title']); ?></p>
                        </div>
                        <span class="track-duration">
                            <?php echo floor($track['duration_seconds'] / 60); ?>:<?php echo str_pad($track['duration_seconds'] % 60, 2, '0', STR_PAD_LEFT); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- No Results -->
            <?php if (empty($results['albums']) && empty($results['artists']) && empty($results['tracks'])): ?>
            <div class="no-results-container">
                <p class="no-results">No results found for "<?php echo htmlspecialchars($query); ?>"</p>
                <p>Try different keywords or browse our collection:</p>
                <div class="search-suggestions">
                    <a href="albums.php" class="btn-secondary">Browse Albums</a>
                    <a href="artists.php" class="btn-secondary">Browse Artists</a>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
