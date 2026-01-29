<?php
/**
 * Database Setup Script for MySQL (XAMPP)
 * Run this file once to create all necessary database tables
 * 
 * INSTRUCTIONS:
 * 1. Start XAMPP (Apache and MySQL)
 * 2. Open phpMyAdmin (http://localhost/phpmyadmin)
 * 3. Create a database named 'music_database_system'
 * 4. Run this file in your browser: http://localhost/music-database-app-main/setup_database.php
 */

require_once 'db_config.php';

echo "<h1>Music Database Setup</h1>";
echo "<p>Creating database tables...</p>";

// Create Users table
$sql = "CREATE TABLE IF NOT EXISTS User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    memorable_word VARCHAR(100),
    date_joined DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Users table created<br>";

// Create Artists table
$sql = "CREATE TABLE IF NOT EXISTS Artist (
    artist_id INT AUTO_INCREMENT PRIMARY KEY,
    artist_name VARCHAR(100) NOT NULL,
    biography TEXT,
    country VARCHAR(50),
    formed_year INT,
    image_url VARCHAR(255),
    INDEX idx_artist_name (artist_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Artists table created<br>";

// Create Albums table
$sql = "CREATE TABLE IF NOT EXISTS Album (
    album_id INT AUTO_INCREMENT PRIMARY KEY,
    album_title VARCHAR(150) NOT NULL,
    artist_id INT NOT NULL,
    release_date DATE,
    cover_image_url VARCHAR(255),
    total_tracks INT,
    FOREIGN KEY (artist_id) REFERENCES Artist(artist_id) ON DELETE CASCADE,
    INDEX idx_artist (artist_id),
    INDEX idx_release_date (release_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Albums table created<br>";

// Create Tracks table
$sql = "CREATE TABLE IF NOT EXISTS Track (
    track_id INT AUTO_INCREMENT PRIMARY KEY,
    track_title VARCHAR(150) NOT NULL,
    album_id INT NOT NULL,
    track_number INT,
    duration_seconds INT NOT NULL,
    audio_url VARCHAR(255),
    FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE,
    INDEX idx_album (album_id),
    FULLTEXT INDEX idx_track_search (track_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Tracks table created<br>";

// Create Genres table
$sql = "CREATE TABLE IF NOT EXISTS Genre (
    genre_id INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Genres table created<br>";

// Create Playlists table
$sql = "CREATE TABLE IF NOT EXISTS Playlist (
    playlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    playlist_name VARCHAR(100) NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Playlists table created<br>";

// Create Reviews table
$sql = "CREATE TABLE IF NOT EXISTS Review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    album_id INT NOT NULL,
    rating DECIMAL(3,1) CHECK (rating >= 0 AND rating <= 5),
    review_text TEXT,
    review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE,
    INDEX idx_album_user (album_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Reviews table created<br>";

// Create Track_Genre junction table
$sql = "CREATE TABLE IF NOT EXISTS Track_Genre (
    track_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (track_id, genre_id),
    FOREIGN KEY (track_id) REFERENCES Track(track_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES Genre(genre_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Track_Genre table created<br>";

// Create Playlist_Track junction table
$sql = "CREATE TABLE IF NOT EXISTS Playlist_Track (
    playlist_id INT NOT NULL,
    track_id INT NOT NULL,
    position INT NOT NULL,
    added_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (playlist_id, track_id),
    FOREIGN KEY (playlist_id) REFERENCES Playlist(playlist_id) ON DELETE CASCADE,
    FOREIGN KEY (track_id) REFERENCES Track(track_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Playlist_Track table created<br>";

// Create User_Favorite_Album table
$sql = "CREATE TABLE IF NOT EXISTS User_Favorite_Album (
    user_id INT NOT NULL,
    album_id INT NOT NULL,
    favorited_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, album_id),
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ User_Favorite_Album table created<br>";

// Create User_Favorite_Artist table
$sql = "CREATE TABLE IF NOT EXISTS User_Favorite_Artist (
    user_id INT NOT NULL,
    artist_id INT NOT NULL,
    favorited_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, artist_id),
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES Artist(artist_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ User_Favorite_Artist table created<br>";

// Create Artist_Rating table
$sql = "CREATE TABLE IF NOT EXISTS Artist_Rating (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    artist_id INT NOT NULL,
    rating DECIMAL(3,1) CHECK (rating >= 0 AND rating <= 5),
    rating_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_artist (user_id, artist_id),
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES Artist(artist_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Artist_Rating table created<br>";

// Create Artist_Influence table
$sql = "CREATE TABLE IF NOT EXISTS Artist_Influence (
    influencer_id INT NOT NULL,
    influenced_id INT NOT NULL,
    influence_description TEXT,
    PRIMARY KEY (influencer_id, influenced_id),
    FOREIGN KEY (influencer_id) REFERENCES Artist(artist_id) ON DELETE CASCADE,
    FOREIGN KEY (influenced_id) REFERENCES Artist(artist_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Artist_Influence table created<br>";

// Create Album_Credit table
$sql = "CREATE TABLE IF NOT EXISTS Album_Credit (
    credit_id INT AUTO_INCREMENT PRIMARY KEY,
    album_id INT NOT NULL,
    musician_name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    instrument VARCHAR(100),
    FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Album_Credit table created<br>";

// Create Listening_Activity table
$sql = "CREATE TABLE IF NOT EXISTS Listening_Activity (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    album_id INT NOT NULL,
    listened_date DATE DEFAULT (CURRENT_DATE),
    completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Listening_Activity table created<br>";

// Create Achievement table
$sql = "CREATE TABLE IF NOT EXISTS Achievement (
    achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    achievement_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    badge_icon VARCHAR(50),
    points INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ Achievement table created<br>";

// Create User_Achievement table
$sql = "CREATE TABLE IF NOT EXISTS User_Achievement (
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    earned_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, achievement_id),
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES Achievement(achievement_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($sql);
echo "✓ User_Achievement table created<br>";

echo "<h2 style='color: green;'>✓ Database setup completed successfully!</h2>";
echo "<p>All tables have been created. You can now use the application.</p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";
?>
