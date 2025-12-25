from flask import Flask, render_template, request, redirect, url_for, session, flash
import sqlite3
import hashlib
from datetime import datetime
import os

app = Flask(__name__)
app.secret_key = 'your-secret-key-here-change-in-production'

# Database configuration - Using SQLite for demo (easily changeable to MySQL)
DATABASE = 'music_database.db'

def get_db():
    """Get database connection"""
    conn = sqlite3.connect(DATABASE)
    conn.row_factory = sqlite3.Row
    return conn

def init_db():
    """Initialize database with tables"""
    conn = get_db()
    cursor = conn.cursor()
    
    # Create Users table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS User (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            first_name TEXT,
            last_name TEXT,
            date_joined DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    # Create Artists table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Artist (
            artist_id INTEGER PRIMARY KEY AUTOINCREMENT,
            artist_name TEXT NOT NULL,
            biography TEXT,
            country TEXT,
            formed_year INTEGER,
            image_url TEXT
        )
    ''')
    
    # Create Albums table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Album (
            album_id INTEGER PRIMARY KEY AUTOINCREMENT,
            album_title TEXT NOT NULL,
            artist_id INTEGER NOT NULL,
            release_date DATE,
            cover_image_url TEXT,
            total_tracks INTEGER,
            FOREIGN KEY (artist_id) REFERENCES Artist(artist_id)
        )
    ''')
    
    # Create Tracks table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Track (
            track_id INTEGER PRIMARY KEY AUTOINCREMENT,
            track_title TEXT NOT NULL,
            album_id INTEGER NOT NULL,
            track_number INTEGER,
            duration_seconds INTEGER NOT NULL,
            FOREIGN KEY (album_id) REFERENCES Album(album_id)
        )
    ''')
    
    # Create Genres table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Genre (
            genre_id INTEGER PRIMARY KEY AUTOINCREMENT,
            genre_name TEXT UNIQUE NOT NULL
        )
    ''')
    
    # Create Playlists table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Playlist (
            playlist_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            playlist_name TEXT NOT NULL,
            is_public INTEGER DEFAULT 0,
            created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES User(user_id)
        )
    ''')
    
    # Create Reviews table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Review (
            review_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            album_id INTEGER NOT NULL,
            rating REAL CHECK(rating >= 0 AND rating <= 5),
            review_text TEXT,
            review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES User(user_id),
            FOREIGN KEY (album_id) REFERENCES Album(album_id)
        )
    ''')
    
    # Create junction tables
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Track_Genre (
            track_id INTEGER,
            genre_id INTEGER,
            PRIMARY KEY (track_id, genre_id),
            FOREIGN KEY (track_id) REFERENCES Track(track_id),
            FOREIGN KEY (genre_id) REFERENCES Genre(genre_id)
        )
    ''')
    
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Playlist_Track (
            playlist_id INTEGER,
            track_id INTEGER,
            position INTEGER NOT NULL,
            added_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (playlist_id, track_id),
            FOREIGN KEY (playlist_id) REFERENCES Playlist(playlist_id),
            FOREIGN KEY (track_id) REFERENCES Track(track_id)
        )
    ''')
    
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS User_Favorite_Album (
            user_id INTEGER,
            album_id INTEGER,
            favorited_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, album_id),
            FOREIGN KEY (user_id) REFERENCES User(user_id),
            FOREIGN KEY (album_id) REFERENCES Album(album_id)
        )
    ''')
    
    conn.commit()
    
    # Insert sample data if tables are empty
    cursor.execute("SELECT COUNT(*) FROM Artist")
    if cursor.fetchone()[0] == 0:
        insert_sample_data(conn)
    
    conn.close()

def insert_sample_data(conn):
    """Insert sample data for demonstration"""
    cursor = conn.cursor()
    
    # Sample Genres
    genres = ['Rock', 'Jazz', 'Pop', 'Progressive Rock', 'Alternative Rock']
    for genre in genres:
        cursor.execute("INSERT INTO Genre (genre_name) VALUES (?)", (genre,))
    
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
            VALUES (?, ?, ?, ?, ?)
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
            VALUES (?, ?, ?, ?, ?)
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
            VALUES (?, ?, ?, ?)
        """, track)
    
    # Link tracks to genres
    track_genres = [
        (1, 1), (2, 1), (3, 1),  # Beatles - Rock
        (4, 4), (5, 4),  # Pink Floyd - Progressive Rock
        (6, 2), (7, 2),  # Miles Davis - Jazz
        (8, 5), (9, 5),  # Radiohead - Alternative Rock
    ]
    for tg in track_genres:
        cursor.execute("INSERT INTO Track_Genre (track_id, genre_id) VALUES (?, ?)", tg)
    
    conn.commit()

# Helper function to hash passwords
def hash_password(password):
    return hashlib.sha256(password.encode()).hexdigest()

# Routes
@app.route('/')
def index():
    """Homepage"""
    conn = get_db()
    cursor = conn.cursor()
    
    # Get featured albums
    cursor.execute("""
        SELECT a.*, ar.artist_name, 
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.review_id) as review_count
        FROM Album a
        JOIN Artist ar ON a.artist_id = ar.artist_id
        LEFT JOIN Review r ON a.album_id = r.album_id
        GROUP BY a.album_id
        ORDER BY a.release_date DESC
        LIMIT 8
    """)
    albums = cursor.fetchall()
    
    conn.close()
    return render_template('index.html', albums=albums)

@app.route('/register', methods=['GET', 'POST'])
def register():
    """User registration"""
    if request.method == 'POST':
        username = request.form['username']
        email = request.form['email']
        password = request.form['password']
        first_name = request.form.get('first_name', '')
        last_name = request.form.get('last_name', '')
        
        conn = get_db()
        cursor = conn.cursor()
        
        try:
            cursor.execute("""
                INSERT INTO User (username, email, password_hash, first_name, last_name)
                VALUES (?, ?, ?, ?, ?)
            """, (username, email, hash_password(password), first_name, last_name))
            conn.commit()
            flash('Registration successful! Please login.', 'success')
            return redirect(url_for('login'))
        except sqlite3.IntegrityError:
            flash('Username or email already exists.', 'error')
        finally:
            conn.close()
    
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    """User login"""
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        conn = get_db()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT * FROM User WHERE username = ? AND password_hash = ?
        """, (username, hash_password(password)))
        user = cursor.fetchone()
        conn.close()
        
        if user:
            session['user_id'] = user['user_id']
            session['username'] = user['username']
            flash('Login successful!', 'success')
            return redirect(url_for('index'))
        else:
            flash('Invalid username or password.', 'error')
    
    return render_template('login.html')

@app.route('/logout')
def logout():
    """User logout"""
    session.clear()
    flash('Logged out successfully.', 'success')
    return redirect(url_for('index'))

@app.route('/artists')
def artists():
    """List all artists"""
    conn = get_db()
    cursor = conn.cursor()
    cursor.execute("""
        SELECT a.*, COUNT(DISTINCT al.album_id) as album_count
        FROM Artist a
        LEFT JOIN Album al ON a.artist_id = al.artist_id
        GROUP BY a.artist_id
        ORDER BY a.artist_name
    """)
    artists = cursor.fetchall()
    conn.close()
    return render_template('artists.html', artists=artists)

@app.route('/artist/<int:artist_id>')
def artist_detail(artist_id):
    """Artist detail page"""
    conn = get_db()
    cursor = conn.cursor()
    
    # Get artist info
    cursor.execute("SELECT * FROM Artist WHERE artist_id = ?", (artist_id,))
    artist = cursor.fetchone()
    
    # Get artist's albums
    cursor.execute("""
        SELECT a.*, 
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.review_id) as review_count
        FROM Album a
        LEFT JOIN Review r ON a.album_id = r.album_id
        WHERE a.artist_id = ?
        GROUP BY a.album_id
        ORDER BY a.release_date DESC
    """, (artist_id,))
    albums = cursor.fetchall()
    
    conn.close()
    return render_template('artist_detail.html', artist=artist, albums=albums)

@app.route('/albums')
def albums():
    """List all albums"""
    conn = get_db()
    cursor = conn.cursor()
    
    search = request.args.get('search', '')
    genre = request.args.get('genre', '')
    
    query = """
        SELECT a.*, ar.artist_name,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.review_id) as review_count
        FROM Album a
        JOIN Artist ar ON a.artist_id = ar.artist_id
        LEFT JOIN Review r ON a.album_id = r.album_id
    """
    
    params = []
    conditions = []
    
    if search:
        conditions.append("(a.album_title LIKE ? OR ar.artist_name LIKE ?)")
        params.extend([f'%{search}%', f'%{search}%'])
    
    if conditions:
        query += " WHERE " + " AND ".join(conditions)
    
    query += " GROUP BY a.album_id ORDER BY a.release_date DESC"
    
    cursor.execute(query, params)
    albums = cursor.fetchall()
    
    # Get genres for filter
    cursor.execute("SELECT * FROM Genre ORDER BY genre_name")
    genres = cursor.fetchall()
    
    conn.close()
    return render_template('albums.html', albums=albums, genres=genres)

@app.route('/album/<int:album_id>')
def album_detail(album_id):
    """Album detail page"""
    conn = get_db()
    cursor = conn.cursor()
    
    # Get album info
    cursor.execute("""
        SELECT a.*, ar.artist_name, ar.artist_id,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.review_id) as review_count
        FROM Album a
        JOIN Artist ar ON a.artist_id = ar.artist_id
        LEFT JOIN Review r ON a.album_id = r.album_id
        WHERE a.album_id = ?
        GROUP BY a.album_id
    """, (album_id,))
    album = cursor.fetchone()
    
    # Get tracks
    cursor.execute("""
        SELECT t.*, GROUP_CONCAT(g.genre_name, ', ') as genres
        FROM Track t
        LEFT JOIN Track_Genre tg ON t.track_id = tg.track_id
        LEFT JOIN Genre g ON tg.genre_id = g.genre_id
        WHERE t.album_id = ?
        GROUP BY t.track_id
        ORDER BY t.track_number
    """, (album_id,))
    tracks = cursor.fetchall()
    
    # Get reviews
    cursor.execute("""
        SELECT r.*, u.username
        FROM Review r
        JOIN User u ON r.user_id = u.user_id
        WHERE r.album_id = ?
        ORDER BY r.review_date DESC
    """, (album_id,))
    reviews = cursor.fetchall()
    
    # Check if user has favorited
    is_favorite = False
    if 'user_id' in session:
        cursor.execute("""
            SELECT * FROM User_Favorite_Album
            WHERE user_id = ? AND album_id = ?
        """, (session['user_id'], album_id))
        is_favorite = cursor.fetchone() is not None
    
    conn.close()
    return render_template('album_detail.html', album=album, tracks=tracks, 
                          reviews=reviews, is_favorite=is_favorite)

@app.route('/album/<int:album_id>/review', methods=['POST'])
def add_review(album_id):
    """Add a review to an album"""
    if 'user_id' not in session:
        flash('Please login to add a review.', 'error')
        return redirect(url_for('login'))
    
    rating = float(request.form['rating'])
    review_text = request.form['review_text']
    
    conn = get_db()
    cursor = conn.cursor()
    cursor.execute("""
        INSERT INTO Review (user_id, album_id, rating, review_text)
        VALUES (?, ?, ?, ?)
    """, (session['user_id'], album_id, rating, review_text))
    conn.commit()
    conn.close()
    
    flash('Review added successfully!', 'success')
    return redirect(url_for('album_detail', album_id=album_id))

@app.route('/album/<int:album_id>/favorite', methods=['POST'])
def toggle_favorite(album_id):
    """Toggle album favorite status"""
    if 'user_id' not in session:
        flash('Please login to favorite albums.', 'error')
        return redirect(url_for('login'))
    
    conn = get_db()
    cursor = conn.cursor()
    
    # Check if already favorited
    cursor.execute("""
        SELECT * FROM User_Favorite_Album
        WHERE user_id = ? AND album_id = ?
    """, (session['user_id'], album_id))
    
    if cursor.fetchone():
        # Remove favorite
        cursor.execute("""
            DELETE FROM User_Favorite_Album
            WHERE user_id = ? AND album_id = ?
        """, (session['user_id'], album_id))
        flash('Removed from favorites.', 'info')
    else:
        # Add favorite
        cursor.execute("""
            INSERT INTO User_Favorite_Album (user_id, album_id)
            VALUES (?, ?)
        """, (session['user_id'], album_id))
        flash('Added to favorites!', 'success')
    
    conn.commit()
    conn.close()
    return redirect(url_for('album_detail', album_id=album_id))

@app.route('/library')
def library():
    """User's library"""
    if 'user_id' not in session:
        flash('Please login to view your library.', 'error')
        return redirect(url_for('login'))
    
    conn = get_db()
    cursor = conn.cursor()
    
    # Get favorite albums
    cursor.execute("""
        SELECT a.*, ar.artist_name,
               COALESCE(AVG(r.rating), 0) as avg_rating
        FROM User_Favorite_Album ufa
        JOIN Album a ON ufa.album_id = a.album_id
        JOIN Artist ar ON a.artist_id = ar.artist_id
        LEFT JOIN Review r ON a.album_id = r.album_id
        WHERE ufa.user_id = ?
        GROUP BY a.album_id
        ORDER BY ufa.favorited_date DESC
    """, (session['user_id'],))
    favorite_albums = cursor.fetchall()
    
    # Get user's playlists
    cursor.execute("""
        SELECT p.*, COUNT(pt.track_id) as track_count
        FROM Playlist p
        LEFT JOIN Playlist_Track pt ON p.playlist_id = pt.playlist_id
        WHERE p.user_id = ?
        GROUP BY p.playlist_id
        ORDER BY p.created_date DESC
    """, (session['user_id'],))
    playlists = cursor.fetchall()
    
    conn.close()
    return render_template('library.html', favorite_albums=favorite_albums, playlists=playlists)

@app.route('/search')
def search():
    """Search page"""
    query = request.args.get('q', '')
    
    if not query:
        return render_template('search.html', results=None)
    
    conn = get_db()
    cursor = conn.cursor()
    
    # Search albums
    cursor.execute("""
        SELECT a.*, ar.artist_name, 'album' as type
        FROM Album a
        JOIN Artist ar ON a.artist_id = ar.artist_id
        WHERE a.album_title LIKE ?
    """, (f'%{query}%',))
    albums = cursor.fetchall()
    
    # Search artists
    cursor.execute("""
        SELECT *, 'artist' as type FROM Artist
        WHERE artist_name LIKE ?
    """, (f'%{query}%',))
    artists = cursor.fetchall()
    
    # Search tracks
    cursor.execute("""
        SELECT t.*, a.album_title, ar.artist_name, 'track' as type
        FROM Track t
        JOIN Album a ON t.album_id = a.album_id
        JOIN Artist ar ON a.artist_id = ar.artist_id
        WHERE t.track_title LIKE ?
    """, (f'%{query}%',))
    tracks = cursor.fetchall()
    
    conn.close()
    
    results = {
        'albums': albums,
        'artists': artists,
        'tracks': tracks,
        'query': query
    }
    
    return render_template('search.html', results=results)

# Initialize database on first run
if not os.path.exists(DATABASE):
    init_db()

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
