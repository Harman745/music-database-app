<?php
/**
 * Sample Data Insertion Script
 * Run this file to populate your database with sample music data
 * 
 * URL: http://localhost/music-database-app-main/insert_sample_data.php
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Insert Sample Data</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2, h3 { color: #333; }
.success { color: green; }
.error { color: red; }
.btn { display: inline-block; padding: 10px 20px; background: #1db954; color: white; 
       text-decoration: none; border-radius: 4px; margin-top: 20px; }
</style></head><body>";

echo "<h1>🎵 Inserting Sample Data...</h1>";

try {
    // Insert sample genres
    echo "<h3>Inserting Genres...</h3>";
    $genres = ['Jazz', 'Rock', 'Blues', 'Classical', 'Hip Hop', 'R&B', 
               'Electronic', 'Pop', 'Country', 'Metal', 'Funk', 'Soul'];
    
    foreach ($genres as $genre) {
        $stmt = $conn->prepare("INSERT IGNORE INTO Genre (genre_name) VALUES (?)");
        $stmt->bind_param('s', $genre);
        $stmt->execute();
        echo "✓ Genre: $genre<br>";
        $stmt->close();
    }
    
    // Insert sample artists
    echo "<h3>Inserting Artists...</h3>";
    $artists = [
        ['Miles Davis', 'Miles Dewey Davis III was an American trumpeter, bandleader, and composer. He is among the most influential and acclaimed figures in the history of jazz and 20th-century music.', 'United States', 1926, 'static/images/milesdavis.avif'],
        ['The Beatles', 'The Beatles were an English rock band formed in Liverpool in 1960. The group consisted of John Lennon, Paul McCartney, George Harrison and Ringo Starr.', 'United Kingdom', 1960, ''],
        ['Pink Floyd', 'Pink Floyd are an English rock band formed in London in 1965. Gaining an early following as one of the first British psychedelic groups.', 'United Kingdom', 1965, ''],
        ['Led Zeppelin', 'Led Zeppelin were an English rock band formed in London in 1968. The group consisted of vocalist Robert Plant, guitarist Jimmy Page, bassist/keyboardist John Paul Jones, and drummer John Bonham.', 'United Kingdom', 1968, ''],
        ['John Coltrane', 'John William Coltrane was an American jazz saxophonist and composer. Working in the bebop and hard bop idioms early in his career.', 'United States', 1926, ''],
        ['Radiohead', 'Radiohead are an English rock band formed in Abingdon, Oxfordshire, in 1985.', 'United Kingdom', 1985, ''],
        ['Nirvana', 'Nirvana was an American rock band formed in Aberdeen, Washington, in 1987.', 'United States', 1987, ''],
        ['Bob Dylan', 'Bob Dylan is an American singer-songwriter, author and visual artist. Widely regarded as one of the greatest songwriters of all time.', 'United States', 1941, '']
    ];
    
    foreach ($artists as $artist) {
        // Check if artist already exists
        $checkStmt = $conn->prepare("SELECT artist_id FROM Artist WHERE artist_name = ?");
        $checkStmt->bind_param('s', $artist[0]);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO Artist (artist_name, biography, country, formed_year, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssis', $artist[0], $artist[1], $artist[2], $artist[3], $artist[4]);
            $stmt->execute();
            echo "✓ Artist: {$artist[0]}<br>";
            $stmt->close();
        } else {
            echo "⚠ Artist already exists: {$artist[0]}<br>";
        }
        $checkStmt->close();
    }
    
    // Get artist IDs
    $artistIds = [];
    $artistNames = ['Miles Davis', 'The Beatles', 'Pink Floyd', 'Led Zeppelin', 'John Coltrane', 'Radiohead', 'Nirvana', 'Bob Dylan'];
    foreach ($artistNames as $name) {
        $result = $conn->query("SELECT artist_id FROM Artist WHERE artist_name = '$name'");
        $row = $result->fetch_assoc();
        $artistIds[$name] = $row['artist_id'];
    }
    
    // Insert sample albums
    echo "<h3>Inserting Albums...</h3>";
    $albums = [
        ['Kind of Blue', $artistIds['Miles Davis'], '1959-08-17', 'https://upload.wikimedia.org/wikipedia/en/9/9c/MilesDavisKindofBlue.jpg', 5],
        ['Abbey Road', $artistIds['The Beatles'], '1969-09-26', 'https://upload.wikimedia.org/wikipedia/en/4/42/Beatles_-_Abbey_Road.jpg', 17],
        ['The Dark Side of the Moon', $artistIds['Pink Floyd'], '1973-03-01', 'https://upload.wikimedia.org/wikipedia/en/3/3b/Dark_Side_of_the_Moon.png', 10],
        ['Led Zeppelin IV', $artistIds['Led Zeppelin'], '1971-11-08', 'https://upload.wikimedia.org/wikipedia/en/2/26/Led_Zeppelin_-_Led_Zeppelin_IV.jpg', 8],
        ['A Love Supreme', $artistIds['John Coltrane'], '1965-01-12', 'https://upload.wikimedia.org/wikipedia/en/4/4a/A_Love_Supreme.jpg', 4],
        ['OK Computer', $artistIds['Radiohead'], '1997-05-21', 'https://upload.wikimedia.org/wikipedia/en/b/ba/Radioheadokcomputer.png', 12],
        ['Nevermind', $artistIds['Nirvana'], '1991-09-24', 'https://upload.wikimedia.org/wikipedia/en/b/b7/NirvanaNevermindalbumcover.jpg', 12],
        ['Highway 61 Revisited', $artistIds['Bob Dylan'], '1965-08-30', 'https://upload.wikimedia.org/wikipedia/en/9/92/Bob_Dylan_-_Highway_61_Revisited.jpg', 9],
        ['Sgt. Pepper\'s Lonely Hearts Club Band', $artistIds['The Beatles'], '1967-05-26', 'https://upload.wikimedia.org/wikipedia/en/5/50/Sgt._Pepper%27s_Lonely_Hearts_Club_Band.jpg', 13],
        ['The Wall', $artistIds['Pink Floyd'], '1979-11-30', 'https://upload.wikimedia.org/wikipedia/en/1/13/PinkFloydWallCoverOriginalNoText.jpg', 26]
    ];
    
    foreach ($albums as $album) {
        // Check if album already exists
        $checkStmt = $conn->prepare("SELECT album_id FROM Album WHERE album_title = ? AND artist_id = ?");
        $checkStmt->bind_param('si', $album[0], $album[1]);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO Album (album_title, artist_id, release_date, cover_image_url, total_tracks) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sissi', $album[0], $album[1], $album[2], $album[3], $album[4]);
            $stmt->execute();
            echo "✓ Album: {$album[0]}<br>";
            $stmt->close();
        } else {
            echo "⚠ Album already exists: {$album[0]}<br>";
        }
        $checkStmt->close();
    }
    
    // Get album IDs
    $albumIds = [];
    $albumTitles = ['Kind of Blue', 'Abbey Road', 'The Dark Side of the Moon'];
    foreach ($albumTitles as $title) {
        $result = $conn->query("SELECT album_id FROM Album WHERE album_title = '$title'");
        $row = $result->fetch_assoc();
        $albumIds[$title] = $row['album_id'];
    }
    
    // Insert sample tracks
    echo "<h3>Inserting Tracks...</h3>";
    $tracks = [
        ['So What', $albumIds['Kind of Blue'], 1, 562],
        ['Freddie Freeloader', $albumIds['Kind of Blue'], 2, 577],
        ['Blue in Green', $albumIds['Kind of Blue'], 3, 337],
        ['All Blues', $albumIds['Kind of Blue'], 4, 691],
        ['Flamenco Sketches', $albumIds['Kind of Blue'], 5, 564],
        
        ['Come Together', $albumIds['Abbey Road'], 1, 259],
        ['Something', $albumIds['Abbey Road'], 2, 182],
        ['Maxwell\'s Silver Hammer', $albumIds['Abbey Road'], 3, 207],
        ['Here Comes the Sun', $albumIds['Abbey Road'], 4, 185],
        
        ['Speak to Me', $albumIds['The Dark Side of the Moon'], 1, 68],
        ['Breathe', $albumIds['The Dark Side of the Moon'], 2, 163],
        ['Time', $albumIds['The Dark Side of the Moon'], 3, 413],
        ['Money', $albumIds['The Dark Side of the Moon'], 4, 382],
    ];
    
    foreach ($tracks as $track) {
        $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('siii', $track[0], $track[1], $track[2], $track[3]);
        $stmt->execute();
        echo "✓ Track: {$track[0]}<br>";
        $stmt->close();
    }
    
    // Insert album credits
    echo "<h3>Inserting Album Credits...</h3>";
    $credits = [
        [$albumIds['Kind of Blue'], 'Miles Davis', 'Trumpet', 'Trumpet'],
        [$albumIds['Kind of Blue'], 'John Coltrane', 'Saxophone', 'Tenor Saxophone'],
        [$albumIds['Kind of Blue'], 'Bill Evans', 'Piano', 'Piano'],
        [$albumIds['Kind of Blue'], 'Paul Chambers', 'Bass', 'Double Bass'],
        [$albumIds['Kind of Blue'], 'Jimmy Cobb', 'Drums', 'Drums'],
    ];
    
    foreach ($credits as $credit) {
        $stmt = $conn->prepare("INSERT INTO Album_Credit (album_id, musician_name, role, instrument) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $credit[0], $credit[1], $credit[2], $credit[3]);
        $stmt->execute();
        echo "✓ Credit: {$credit[1]}<br>";
        $stmt->close();
    }
    
    // Insert achievements
    echo "<h3>Inserting Achievements...</h3>";
    $achievements = [
        ['First Steps', 'Create your account', '🎯', 10],
        ['Music Lover', 'Add 10 albums to favorites', '❤️', 50],
        ['Critic', 'Write 5 album reviews', '✍️', 75],
        ['Explorer', 'Listen to albums from 10 different genres', '🌍', 100],
        ['Playlist Master', 'Create 5 playlists', '📝', 60],
        ['Super Fan', 'Add 5 artists to favorites', '⭐', 40],
        ['Marathon Listener', 'Listen to 100 albums', '🎧', 200],
        ['Trendsetter', 'Be first to review a new album', '🔥', 150],
    ];
    
    foreach ($achievements as $ach) {
        $stmt = $conn->prepare("INSERT INTO Achievement (achievement_name, description, badge_icon, points) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $ach[0], $ach[1], $ach[2], $ach[3]);
        $stmt->execute();
        echo "✓ Achievement: {$ach[0]}<br>";
        $stmt->close();
    }
    
    // Insert sample reviews (if users exist)
    $result = $conn->query("SELECT user_id FROM User LIMIT 1");
    $userCheck = $result->fetch_assoc();
    if ($userCheck) {
        echo "<h3>Inserting Sample Reviews...</h3>";
        $userId = $userCheck['user_id'];
        
        $reviews = [
            [$userId, $albumIds['Kind of Blue'], 5.0, 'An absolute masterpiece! This album defined modal jazz and remains timeless.'],
            [$userId, $albumIds['Abbey Road'], 4.8, 'The Beatles at their finest. Every track is incredible.'],
            [$userId, $albumIds['The Dark Side of the Moon'], 5.0, 'A sonic journey like no other. Pink Floyd created something truly special.'],
        ];
        
        foreach ($reviews as $review) {
            $stmt = $conn->prepare("INSERT INTO Review (user_id, album_id, rating, review_text) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('iids', $review[0], $review[1], $review[2], $review[3]);
            $stmt->execute();
            echo "✓ Review added<br>";
            $stmt->close();
        }
    } else {
        echo "<p style='color: orange;'>⚠ No users found. Register a user first to add sample reviews.</p>";
    }
    
    echo "<h2 class='success'>✓ Sample data inserted successfully!</h2>";
    echo "<p><strong>Your database now has:</strong></p>";
    echo "<ul>";
    echo "<li>" . count($genres) . " genres</li>";
    echo "<li>" . count($artists) . " artists</li>";
    echo "<li>" . count($albums) . " albums</li>";
    echo "<li>" . count($tracks) . " tracks</li>";
    echo "<li>" . count($credits) . " album credits</li>";
    echo "<li>" . count($achievements) . " achievements</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<p>MySQL Error: " . $conn->error . "</p>";
}

echo "<p><a href='index.php' class='btn'>🏠 Go to Home Page</a></p>";
echo "</body></html>";
?>
