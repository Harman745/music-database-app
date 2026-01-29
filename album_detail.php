<?php
/**
 * Album Detail Page - Shows tracks and reviews
 * Developer: Harman Singh
 */
require_once 'db_config.php';

$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;

if ($album_id <= 0) {
    header('Location: albums.php');
    exit;
}

// Fetch album details
$query = "
    SELECT 
        a.album_id,
        a.album_title,
        a.cover_image_url,
        a.release_date,
        a.artist_id,
        ar.artist_name,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.review_id) as review_count
    FROM Album a
    INNER JOIN Artist ar ON a.artist_id = ar.artist_id
    LEFT JOIN Review r ON a.album_id = r.album_id
    WHERE a.album_id = ?
    GROUP BY a.album_id, a.album_title, a.cover_image_url, a.release_date, a.artist_id, ar.artist_name
";
$album = fetchOne($query, 'i', [$album_id]);

if (!$album) {
    header('Location: albums.php');
    exit;
}

// Fetch tracks
$tracksQuery = "
    SELECT 
        t.track_id,
        t.track_title,
        t.track_number,
        t.duration_seconds,
        t.audio_url,
        GROUP_CONCAT(g.genre_name SEPARATOR ', ') as genres
    FROM Track t
    LEFT JOIN Track_Genre tg ON t.track_id = tg.track_id
    LEFT JOIN Genre g ON tg.genre_id = g.genre_id
    WHERE t.album_id = ?
    GROUP BY t.track_id, t.track_title, t.track_number, t.duration_seconds, t.audio_url
    ORDER BY t.track_number ASC
";
$tracks = fetchAll($tracksQuery, 'i', [$album_id]);

// Fetch credits
$creditsQuery = "
    SELECT musician_name, role, instrument
    FROM Album_Credit
    WHERE album_id = ?
    ORDER BY musician_name ASC
";
$credits = fetchAll($creditsQuery, 'i', [$album_id]);

// Check if favorited by current user
$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $favQuery = "SELECT 1 FROM User_Favorite_Album WHERE user_id = ? AND album_id = ?";
    $is_favorite = fetchOne($favQuery, 'ii', [$_SESSION['user_id'], $album_id]) !== null;
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    if ($is_favorite) {
        executeQuery("DELETE FROM User_Favorite_Album WHERE user_id = ? AND album_id = ?", 'ii', [$_SESSION['user_id'], $album_id]);
    } else {
        executeQuery("INSERT INTO User_Favorite_Album (user_id, album_id) VALUES (?, ?)", 'ii', [$_SESSION['user_id'], $album_id]);
    }
    header("Location: album_detail.php?album_id=$album_id");
    exit;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $rating = (float)$_POST['rating'];
    $review_text = trim($_POST['review_text']);
    
    if ($rating >= 0 && $rating <= 5) {
        // Check if user already reviewed this album
        $existingReview = fetchOne("SELECT review_id FROM Review WHERE user_id = ? AND album_id = ?", 'ii', [$_SESSION['user_id'], $album_id]);
        
        if ($existingReview) {
            // Update existing review
            executeQuery("UPDATE Review SET rating = ?, review_text = ?, review_date = NOW() WHERE review_id = ?", 'dsi', [$rating, $review_text, $existingReview['review_id']]);
        } else {
            // Insert new review
            executeQuery("INSERT INTO Review (user_id, album_id, rating, review_text) VALUES (?, ?, ?, ?)", 'iids', [$_SESSION['user_id'], $album_id, $rating, $review_text]);
        }
        header("Location: album_detail.php?album_id=$album_id");
        exit;
    }
}

// Fetch reviews
$reviewsQuery = "
    SELECT 
        r.review_id,
        r.rating,
        r.review_text,
        r.review_date,
        u.username
    FROM Review r
    INNER JOIN User u ON r.user_id = u.user_id
    WHERE r.album_id = ?
    ORDER BY r.review_date DESC
";
$reviews = fetchAll($reviewsQuery, 'i', [$album_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($album['album_title']); ?> - Music Database</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="album-detail-page">
        <div class="container">
            <div class="album-header">
                <div class="album-cover-large">
                    <?php if (!empty($album['cover_image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($album['cover_image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($album['album_title']); ?>" 
                             onerror="this.src='/static/images/default-album.jpg'">
                    <?php else: ?>
                        <div class="album-placeholder-large">🎵</div>
                    <?php endif; ?>
                </div>
                <div class="album-meta">
                    <h1><?php echo htmlspecialchars($album['album_title']); ?></h1>
                    <h2>
                        <a href="artist_detail.php?artist_id=<?php echo $album['artist_id']; ?>">
                            <?php echo htmlspecialchars($album['artist_name']); ?>
                        </a>
                    </h2>
                    <p class="release-date">Released: <?php echo htmlspecialchars($album['release_date']); ?></p>
                    <div class="album-rating">
                        <span class="stars-large">
                            <?php
                            $rating = (float)$album['avg_rating'];
                            for ($i = 0; $i < 5; $i++) {
                                echo $i < $rating ? '★' : '☆';
                            }
                            ?>
                        </span>
                        <span class="rating-text-large">
                            <?php echo number_format($album['avg_rating'], 1); ?> / 5.0
                            (<?php echo $album['review_count']; ?> reviews)
                        </span>
                    </div>
                    <div class="album-actions">
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

            <section class="liner-notes-section">
                <h3>📝 Liner Notes & Credits</h3>
                <?php if (!empty($credits)): ?>
                <div class="credits-grid">
                    <?php foreach ($credits as $credit): ?>
                    <div class="credit-item">
                        <strong><?php echo htmlspecialchars($credit['musician_name']); ?></strong>
                        <p><?php echo htmlspecialchars($credit['role']); ?></p>
                        <?php if (!empty($credit['instrument'])): ?>
                            <p class="instrument"><?php echo htmlspecialchars($credit['instrument']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="no-credits">Credits information not available for this album.</p>
                <?php endif; ?>
            </section>

            <section class="tracks-section">
                <h3>Track Listing</h3>
                <div class="track-list">
                    <?php foreach ($tracks as $track): ?>
                    <div class="track-item">
                        <span class="track-number"><?php echo $track['track_number']; ?></span>
                        <div class="track-info">
                            <span class="track-title"><?php echo htmlspecialchars($track['track_title']); ?></span>
                            <?php if (!empty($track['genres'])): ?>
                                <span class="track-genres"><?php echo htmlspecialchars($track['genres']); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="track-duration">
                            <?php echo floor($track['duration_seconds'] / 60); ?>:<?php echo str_pad($track['duration_seconds'] % 60, 2, '0', STR_PAD_LEFT); ?>
                        </span>
                        <?php if (!empty($track['audio_url'])): ?>
                        <button class="play-btn" onclick="playTrack('<?php echo htmlspecialchars($track['audio_url']); ?>', '<?php echo htmlspecialchars($track['track_title']); ?>')">▶️ Play</button>
                        <?php else: ?>
                        <button class="play-btn disabled" disabled>🔇 No Audio</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="reviews-section">
                <h3>Reviews (<?php echo count($reviews); ?>)</h3>
                
                <!-- Write Review Form -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    // Check if user already reviewed
                    $userReview = null;
                    foreach ($reviews as $review) {
                        if ($review['username'] === $_SESSION['username']) {
                            $userReview = $review;
                            break;
                        }
                    }
                    ?>
                    <div class="review-form-container">
                        <h4><?php echo $userReview ? '✏️ Edit Your Review' : '✍️ Write a Review'; ?></h4>
                        <form method="POST" class="review-form">
                            <input type="hidden" name="submit_review" value="1">
                            <div class="form-group">
                                <label for="rating">Your Rating:</label>
                                <div class="star-rating-input">
                                    <input type="radio" name="rating" id="star5" value="5" <?php echo $userReview && $userReview['rating'] == 5 ? 'checked' : ''; ?> required>
                                    <label for="star5">★</label>
                                    <input type="radio" name="rating" id="star4" value="4" <?php echo $userReview && $userReview['rating'] == 4 ? 'checked' : ''; ?>>
                                    <label for="star4">★</label>
                                    <input type="radio" name="rating" id="star3" value="3" <?php echo $userReview && $userReview['rating'] == 3 ? 'checked' : ''; ?>>
                                    <label for="star3">★</label>
                                    <input type="radio" name="rating" id="star2" value="2" <?php echo $userReview && $userReview['rating'] == 2 ? 'checked' : ''; ?>>
                                    <label for="star2">★</label>
                                    <input type="radio" name="rating" id="star1" value="1" <?php echo $userReview && $userReview['rating'] == 1 ? 'checked' : ''; ?>>
                                    <label for="star1">★</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="review_text">Your Review:</label>
                                <textarea name="review_text" id="review_text" rows="5" placeholder="Share your thoughts about this album..."><?php echo $userReview ? htmlspecialchars($userReview['review_text']) : ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn-primary"><?php echo $userReview ? 'Update Review' : 'Submit Review'; ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>📝 <a href="login.php">Login</a> to write a review!</p>
                    </div>
                <?php endif; ?>
                
                <!-- All Reviews -->
                <div class="all-reviews">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                                <span class="review-rating">
                                    <?php for ($i = 0; $i < 5; $i++) echo $i < $review['rating'] ? '★' : '☆'; ?>
                                </span>
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['review_date'])); ?></span>
                            </div>
                            <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-reviews">No reviews yet. Be the first to review this album!</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Audio Player -->
    <div id="audio-player" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #1a1a1a; padding: 15px; border-top: 2px solid #1db954;">
        <div class="container">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span id="current-track" style="color: white; flex: 1;">Now Playing: </span>
                <audio id="audio-element" controls style="flex: 2;">
                    <source id="audio-source" src="" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <button onclick="closePlayer()" style="background: #ff4444; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">✕ Close</button>
            </div>
        </div>
    </div>

    <style>
        .review-form-container {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .review-form .form-group {
            margin-bottom: 15px;
        }
        .review-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        .star-rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
        }
        .star-rating-input input[type="radio"] {
            display: none;
        }
        .star-rating-input label {
            font-size: 2em;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating-input input[type="radio"]:checked ~ label,
        .star-rating-input label:hover,
        .star-rating-input label:hover ~ label {
            color: #ffc107;
        }
        .all-reviews {
            margin-top: 20px;
        }
        .login-prompt {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 20px;
        }
        .login-prompt a {
            color: #1db954;
            font-weight: bold;
        }
    </style>
    
    <script>
        function playTrack(audioUrl, trackTitle) {
            const player = document.getElementById('audio-player');
            const audio = document.getElementById('audio-element');
            const source = document.getElementById('audio-source');
            const currentTrack = document.getElementById('current-track');
            
            source.src = audioUrl;
            currentTrack.textContent = 'Now Playing: ' + trackTitle;
            audio.load();
            audio.play();
            player.style.display = 'block';
        }
        
        function closePlayer() {
            const player = document.getElementById('audio-player');
            const audio = document.getElementById('audio-element');
            audio.pause();
            player.style.display = 'none';
        }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
