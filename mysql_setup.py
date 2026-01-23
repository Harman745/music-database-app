"""
MySQL Setup Script for Music Database System

This script provides the MySQL version of the database schema.
Run this after creating a MySQL database to set up all tables.

Instructions:
1. Create database: CREATE DATABASE music_database_system;
2. Run this script: python mysql_setup.py
3. Update app.py to use MySQL connection (see comments below)
"""

import pymysql

# MySQL Configuration
MYSQL_CONFIG = {
    'host': 'localhost',
    'user': 'root',  # Change to your MySQL username
    'password': '',  # Change to your MySQL password
    'database': 'music_database_system'
}

def get_mysql_connection():
    """Create MySQL connection"""
    return pymysql.connect(
        host=MYSQL_CONFIG['host'],
        user=MYSQL_CONFIG['user'],
        password=MYSQL_CONFIG['password'],
        database=MYSQL_CONFIG['database'],
        cursorclass=pymysql.cursors.DictCursor
    )

def create_tables():
    """Create all database tables"""
    conn = get_mysql_connection()
    cursor = conn.cursor()
    
    # Drop existing tables (optional - for clean setup)
    tables = ['User_Favorite_Album', 'User_Favorite_Artist', 'Playlist_Track', 
              'Track_Genre', 'Review', 'Playlist', 'Track', 'Album', 'Artist', 
              'Genre', 'User']
    
    for table in tables:
        try:
            cursor.execute(f"DROP TABLE IF EXISTS {table}")
        except:
            pass
    
    # Create Users table
    cursor.execute("""
        CREATE TABLE User (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(50),
            last_name VARCHAR(50),
            date_joined DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    # Create Artists table
    cursor.execute("""
        CREATE TABLE Artist (
            artist_id INT AUTO_INCREMENT PRIMARY KEY,
            artist_name VARCHAR(100) NOT NULL,
            biography TEXT,
            country VARCHAR(50),
            formed_year INT,
            image_url VARCHAR(255),
            INDEX idx_artist_name (artist_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    # Create Albums table
    cursor.execute("""
        CREATE TABLE Album (
            album_id INT AUTO_INCREMENT PRIMARY KEY,
            album_title VARCHAR(150) NOT NULL,
            artist_id INT NOT NULL,
            release_date DATE,
            cover_image_url VARCHAR(255),
            total_tracks INT,
            FOREIGN KEY (artist_id) REFERENCES Artist(artist_id) ON DELETE CASCADE,
            INDEX idx_artist (artist_id),
            INDEX idx_release_date (release_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    # Create Tracks table
    cursor.execute("""
        CREATE TABLE Track (
            track_id INT AUTO_INCREMENT PRIMARY KEY,
            track_title VARCHAR(150) NOT NULL,
            album_id INT NOT NULL,
            track_number INT,
            duration_seconds INT NOT NULL,
            FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE,
            INDEX idx_album (album_id),
            FULLTEXT INDEX idx_track_search (track_title)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    # Create Genres table
    cursor.execute("""
        CREATE TABLE Genre (
            genre_id INT AUTO_INCREMENT PRIMARY KEY,
            genre_name VARCHAR(50) UNIQUE NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    # Create Playlists table
    cursor.execute("""
        CREATE TABLE Playlist (
            playlist_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            playlist_name VARCHAR(100) NOT NULL,
            is_public BOOLEAN DEFAULT FALSE,
            created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    # Create Reviews table
    cursor.execute("""
        CREATE TABLE Review (
            review_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            album_id INT NOT NULL,
            rating DECIMAL(3,1) CHECK (rating >= 0 AND rating <= 5),
            review_text TEXT,
            review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
            FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE,
            INDEX idx_album_user (album_id, user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    # Create junction tables
    cursor.execute("""
        CREATE TABLE Track_Genre (
            track_id INT NOT NULL,
            genre_id INT NOT NULL,
            PRIMARY KEY (track_id, genre_id),
            FOREIGN KEY (track_id) REFERENCES Track(track_id) ON DELETE CASCADE,
            FOREIGN KEY (genre_id) REFERENCES Genre(genre_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    cursor.execute("""
        CREATE TABLE Playlist_Track (
            playlist_id INT NOT NULL,
            track_id INT NOT NULL,
            position INT NOT NULL,
            added_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (playlist_id, track_id),
            FOREIGN KEY (playlist_id) REFERENCES Playlist(playlist_id) ON DELETE CASCADE,
            FOREIGN KEY (track_id) REFERENCES Track(track_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    cursor.execute("""
        CREATE TABLE User_Favorite_Album (
            user_id INT NOT NULL,
            album_id INT NOT NULL,
            favorited_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, album_id),
            FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
            FOREIGN KEY (album_id) REFERENCES Album(album_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    cursor.execute("""
        CREATE TABLE User_Favorite_Artist (
            user_id INT NOT NULL,
            artist_id INT NOT NULL,
            favorited_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, artist_id),
            FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
            FOREIGN KEY (artist_id) REFERENCES Artist(artist_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    """)
    
    conn.commit()
    print("✅ All tables created successfully!")
    
    # Insert sample data
    insert_sample_data(conn)
    
    conn.close()

def insert_sample_data(conn):
    """Insert sample data for demonstration"""
    cursor = conn.cursor()
    
    print("Inserting sample data...")
    
    # Sample Genres
    genres = ['Rock', 'Jazz', 'Pop', 'Progressive Rock', 'Alternative Rock']
    for genre in genres:
        cursor.execute("INSERT INTO Genre (genre_name) VALUES (%s)", (genre,))
    
    # Sample Artists
    artists = [
        ('The Beatles', 'Iconic British rock band', 'United Kingdom', 1960, '/static/images/artists/beatles.jpg'),
        ('Pink Floyd', 'Progressive rock pioneers', 'United Kingdom', 1965, '/static/images/artists/pinkfloyd.jpg'),
        ('Miles Davis', 'Jazz trumpeter and composer', 'United States', 1944, '/static/images/artists/miles.jpg'),
        ('Radiohead', 'Alternative rock band', 'United Kingdom', 1985, '/static/images/artists/radiohead.jpg'),
    ]
    for artist in artists:
        cursor.execute("""
            INSERT INTO Artist (artist_name, biography, country, formed_year, image_url)
            VALUES (%s, %s, %s, %s, %s)
        """, artist)
    
    # Sample Albums
    albums = [
        ('Abbey Road', 1, '1969-09-26', '/static/images/albums/abbeyroad.jpg', 17),
        ('The Dark Side of the Moon', 2, '1973-03-01', '/static/images/albums/darkside.jpg', 10),
        ('Kind of Blue', 3, '1959-08-17', '/static/images/albums/kindofblue.jpg', 5),
        ('OK Computer', 4, '1997-06-16', '/static/images/albums/okcomputer.jpg', 12),
    ]
    for album in albums:
        cursor.execute("""
            INSERT INTO Album (album_title, artist_id, release_date, cover_image_url, total_tracks)
            VALUES (%s, %s, %s, %s, %s)
        """, album)
    
    # Sample Tracks
    tracks = [
        ('Come Together', 1, 1, 259),
        ('Something', 1, 2, 182),
        ('Here Comes the Sun', 1, 3, 185),
        ('Time', 2, 4, 413),
        ('Money', 2, 6, 382),
        ('So What', 3, 1, 544),
        ('Freddie Freeloader', 3, 2, 585),
        ('Paranoid Android', 4, 2, 383),
        ('Karma Police', 4, 6, 261),
    ]
    for track in tracks:
        cursor.execute("""
            INSERT INTO Track (track_title, album_id, track_number, duration_seconds)
            VALUES (%s, %s, %s, %s)
        """, track)
    
    # Link tracks to genres
    track_genres = [
        (1, 1), (2, 1), (3, 1),  # Beatles - Rock
        (4, 4), (5, 4),  # Pink Floyd - Progressive Rock
        (6, 2), (7, 2),  # Miles Davis - Jazz
        (8, 5), (9, 5),  # Radiohead - Alternative Rock
    ]
    for tg in track_genres:
        cursor.execute("INSERT INTO Track_Genre (track_id, genre_id) VALUES (%s, %s)", tg)
    
    conn.commit()
    print("✅ Sample data inserted successfully!")

if __name__ == '__main__':
    print("=== MySQL Database Setup ===")
    print(f"Connecting to MySQL at {MYSQL_CONFIG['host']}...")
    
    try:
        create_tables()
        print("\n✅ Database setup complete!")
        print("\nNext steps:")
        print("1. Update app.py to use MySQL (replace get_db() function)")
        print("2. Install PyMySQL: pip install pymysql")
        print("3. Run the Flask app: python app.py")
    except Exception as e:
        print(f"\n❌ Error: {e}")
        print("\nPlease check:")
        print("- MySQL server is running")
        print("- Database 'music_database_system' exists")
        print("- MySQL credentials are correct in this script")
