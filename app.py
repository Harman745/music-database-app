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
            memorable_word TEXT,
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
    
    # Artist Rating - Users can rate artists
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Artist_Rating (
            rating_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            artist_id INTEGER NOT NULL,
            rating REAL CHECK(rating >= 0 AND rating <= 5),
            rating_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, artist_id),
            FOREIGN KEY (user_id) REFERENCES User(user_id),
            FOREIGN KEY (artist_id) REFERENCES Artist(artist_id)
        )
    ''')
    
    # Artist Influences - Track how artists influenced each other
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Artist_Influence (
            influencer_id INTEGER,
            influenced_id INTEGER,
            influence_description TEXT,
            PRIMARY KEY (influencer_id, influenced_id),
            FOREIGN KEY (influencer_id) REFERENCES Artist(artist_id),
            FOREIGN KEY (influenced_id) REFERENCES Artist(artist_id)
        )
    ''')
    
    # Album Credits - Liner notes and musician credits
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Album_Credit (
            credit_id INTEGER PRIMARY KEY AUTOINCREMENT,
            album_id INTEGER NOT NULL,
            musician_name TEXT NOT NULL,
            role TEXT NOT NULL,
            instrument TEXT,
            FOREIGN KEY (album_id) REFERENCES Album(album_id)
        )
    ''')
    
    # Listening Activity - Track user listening history
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Listening_Activity (
            activity_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            album_id INTEGER NOT NULL,
            listened_date DATE DEFAULT CURRENT_DATE,
            completed INTEGER DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES User(user_id),
            FOREIGN KEY (album_id) REFERENCES Album(album_id)
        )
    ''')
    
    # User Achievements
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Achievement (
            achievement_id INTEGER PRIMARY KEY AUTOINCREMENT,
            achievement_name TEXT UNIQUE NOT NULL,
            description TEXT,
            icon TEXT,
            requirement_type TEXT,
            requirement_value INTEGER
        )
    ''')
    
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS User_Achievement (
            user_id INTEGER,
            achievement_id INTEGER,
            earned_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, achievement_id),
            FOREIGN KEY (user_id) REFERENCES User(user_id),
            FOREIGN KEY (achievement_id) REFERENCES Achievement(achievement_id)
        )
    ''')
    
    # Mood Tracking for Reviews
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS Review_Mood (
            review_id INTEGER PRIMARY KEY,
            mood TEXT,
            energy_level INTEGER CHECK(energy_level >= 1 AND energy_level <= 5),
            FOREIGN KEY (review_id) REFERENCES Review(review_id)
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
        ('The Beatles', 'The Beatles were an English rock band formed in Liverpool in 1960. With a line-up comprising John Lennon, Paul McCartney, George Harrison and Ringo Starr, they are regarded as the most influential band of all time. They were integral to the development of 1960s counterculture and popular music\'s recognition as an art form.', 'United Kingdom', 1960, '/static/images/Thebeatles.jpg'),
        ('Pink Floyd', 'Pink Floyd are an English rock band formed in London in 1965. Gaining an early following as one of the first British psychedelic groups, they were distinguished by their philosophical lyrics, sonic experimentation, extended compositions, and elaborate live shows. They became a leading band of the progressive rock genre, cited by some as the greatest progressive rock band of all time.', 'United Kingdom', 1965, '/static/images/Pink_Floyd.jpg'),
        ('Miles Davis', 'Miles Dewey Davis III was an American jazz trumpeter, bandleader, and composer. He is among the most influential and acclaimed figures in the history of jazz and 20th-century music. Davis adopted a variety of musical directions in a five-decade career that kept him at the forefront of many major stylistic developments in jazz.', 'United States', 1944, '/static/images/milesdavis.avif'),
        ('Radiohead', 'Radiohead are an English rock band formed in Abingdon, Oxfordshire, in 1985. The band consists of Thom Yorke, brothers Jonny Greenwood and Colin Greenwood, Ed O\'Brien and Philip Selway. They have worked with producer Nigel Godrich and cover artist Stanley Donwood since 1994. Radiohead\'s experimental approach is credited with advancing the sound of alternative rock.', 'United Kingdom', 1985, '/static/images/Radiahead.jpg'),
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
    
    # Artist Influences - Show how artists influenced each other
    influences = [
        (1, 4, 'The Beatles\' experimental approach heavily influenced Radiohead\'s sonic exploration'),
        (2, 4, 'Pink Floyd\'s atmospheric soundscapes inspired Radiohead\'s production style'),
        (3, 1, 'Miles Davis\' innovation influenced The Beatles\' later experimental works'),
    ]
    for influence in influences:
        cursor.execute("""
            INSERT INTO Artist_Influence (influencer_id, influenced_id, influence_description)
            VALUES (?, ?, ?)
        """, influence)
    
    # Album Credits - Liner notes and musician credits
    credits = [
        (1, 'John Lennon', 'Vocals, Rhythm Guitar', 'Guitar'),
        (1, 'Paul McCartney', 'Vocals, Bass Guitar', 'Bass'),
        (1, 'George Harrison', 'Lead Guitar, Vocals', 'Guitar'),
        (1, 'Ringo Starr', 'Drums, Percussion', 'Drums'),
        (1, 'George Martin', 'Producer', None),
        (2, 'David Gilmour', 'Guitar, Vocals', 'Guitar'),
        (2, 'Roger Waters', 'Bass, Vocals', 'Bass'),
        (2, 'Richard Wright', 'Keyboards, Vocals', 'Keyboards'),
        (2, 'Nick Mason', 'Drums', 'Drums'),
        (2, 'Alan Parsons', 'Engineer', None),
        (3, 'Miles Davis', 'Trumpet', 'Trumpet'),
        (3, 'John Coltrane', 'Tenor Saxophone', 'Saxophone'),
        (3, 'Bill Evans', 'Piano', 'Piano'),
        (3, 'Paul Chambers', 'Bass', 'Bass'),
        (3, 'Jimmy Cobb', 'Drums', 'Drums'),
        (4, 'Thom Yorke', 'Vocals, Guitar, Piano', 'Guitar'),
        (4, 'Jonny Greenwood', 'Lead Guitar, Keyboards', 'Guitar'),
        (4, 'Ed O\'Brien', 'Guitar, Backing Vocals', 'Guitar'),
        (4, 'Colin Greenwood', 'Bass Guitar', 'Bass'),
        (4, 'Phil Selway', 'Drums, Percussion', 'Drums'),
        (4, 'Nigel Godrich', 'Producer', None),
    ]
    for credit in credits:
        cursor.execute("""
            INSERT INTO Album_Credit (album_id, musician_name, role, instrument)
            VALUES (?, ?, ?, ?)
        """, credit)
    
    # Achievements
    achievements = [
        ('Genre Explorer', 'Listen to albums from 5 different genres', '🎭', 'genres', 5),
        ('Time Traveler', 'Listen to albums from 3 different decades', '⏰', 'decades', 3),
        ('Album Completionist', 'Complete listening to 10 full albums', '💿', 'albums', 10),
        ('Jazz Aficionado', 'Listen to 5 Jazz albums', '🎺', 'jazz_albums', 5),
        ('Rock Legend', 'Listen to 10 Rock albums', '🎸', 'rock_albums', 10),
        ('Prolific Reviewer', 'Write 20 album reviews', '✍️', 'reviews', 20),
        ('Vintage Collector', 'Listen to 5 albums from before 1970', '📻', 'vintage_albums', 5),
        ('Modern Music Fan', 'Listen to 5 albums from after 2000', '🎧', 'modern_albums', 5),
        ('Mood Tracker', 'Track your mood for 15 reviews', '😊', 'mood_reviews', 15),
        ('Influence Scholar', 'Explore 3 artist influence connections', '🔗', 'influences', 3),
    ]
    for achievement in achievements:
        cursor.execute("""
            INSERT INTO Achievement (achievement_name, description, icon, requirement_type, requirement_value)
            VALUES (?, ?, ?, ?, ?)
        """, achievement)
    
    conn.commit()

def migrate_db():
    """Add memorable_word column to existing User table if it doesn't exist"""
    conn = get_db()
    cursor = conn.cursor()
    
    # Check if memorable_word column exists
    cursor.execute("PRAGMA table_info(User)")
    columns = [column[1] for column in cursor.fetchall()]
    
    if 'memorable_word' not in columns:
        try:
            cursor.execute("ALTER TABLE User ADD COLUMN memorable_word TEXT")
            conn.commit()
            print("Added memorable_word column to User table")
        except sqlite3.OperationalError as e:
            print(f"Migration error: {e}")
    
    conn.close()

# Helper function to hash passwords
def hash_password(password):
    return hashlib.sha256(password.encode()).hexdigest()

# Routes
# Developer: Yuvraj Singh Bahia
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

# Developer: Ihsaan, Harman Singh
@app.route('/register', methods=['GET', 'POST'])
def register():
    """User registration"""
    if request.method == 'POST':
        username = request.form['username']
        email = request.form['email']
        password = request.form['password']
        first_name = request.form.get('first_name', '')
        last_name = request.form.get('last_name', '')
        memorable_word = request.form.get('memorable_word', '')
        
        conn = get_db()
        cursor = conn.cursor()
        
        try:
            cursor.execute("""
                INSERT INTO User (username, email, password_hash, first_name, last_name, memorable_word)
                VALUES (?, ?, ?, ?, ?, ?)
            """, (username, email, hash_password(password), first_name, last_name, memorable_word.lower()))
            conn.commit()
            flash('Registration successful! Please login.', 'success')
            return redirect(url_for('login'))
        except sqlite3.IntegrityError:
            flash('Username or email already exists.', 'error')
        finally:
            conn.close()
    
    return render_template('register.html')

# Developer: Ihsaan
@app.route('/login', methods=['GET', 'POST'])
def login():
    """User login"""
    if request.method == 'POST':
        username_or_email = request.form['username']
        password = request.form['password']
        
        conn = get_db()
        cursor = conn.cursor()
        # Check if the input matches either username or email
        cursor.execute("""
            SELECT * FROM User WHERE (username = ? OR email = ?) AND password_hash = ?
        """, (username_or_email, username_or_email, hash_password(password)))
        user = cursor.fetchone()
        conn.close()
        
        if user:
            session['user_id'] = user['user_id']
            session['username'] = user['username']
            flash('Login successful!', 'success')
            return redirect(url_for('index'))
        else:
            flash('Invalid username/email or password.', 'error')
    
    return render_template('login.html')

@app.route('/logout')
def logout():
    """User logout"""
    session.clear()
    flash('Logged out successfully.', 'success')
    return redirect(url_for('index'))

# Developer: Harman Singh
@app.route('/forgot-password', methods=['GET', 'POST'])
def forgot_password():
    """Forgot password - Request password reset"""
    if request.method == 'POST':
        email = request.form['email']
        memorable_word = request.form['memorable_word']
        
        conn = get_db()
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM User WHERE email = ?", (email,))
        user = cursor.fetchone()
        conn.close()
        
        if user:
            # Verify memorable word
            if user['memorable_word'] and user['memorable_word'].lower() == memorable_word.lower():
                # Store the user_id in session for password reset
                session['reset_user_id'] = user['user_id']
                session['reset_email'] = email
                flash('Security verified! Please enter your new password.', 'success')
                return redirect(url_for('reset_password'))
            else:
                flash('Incorrect memorable word. Please try again.', 'error')
        else:
            flash('No account found with that email address.', 'error')
    
    return render_template('forgot_password.html')

# Developer: Harman Singh
@app.route('/reset-password', methods=['GET', 'POST'])
def reset_password():
    """Reset password page"""
    # Check if user has gone through forgot password flow
    if 'reset_user_id' not in session:
        flash('Please use the forgot password page first.', 'error')
        return redirect(url_for('forgot_password'))
    
    if request.method == 'POST':
        new_password = request.form['password']
        confirm_password = request.form['confirm_password']
        
        if new_password != confirm_password:
            flash('Passwords do not match.', 'error')
            return render_template('reset_password.html')
        
        conn = get_db()
        cursor = conn.cursor()
        cursor.execute("""
            UPDATE User SET password_hash = ? WHERE user_id = ?
        """, (hash_password(new_password), session['reset_user_id']))
        conn.commit()
        conn.close()
        
        # Clear reset session data
        session.pop('reset_user_id', None)
        session.pop('reset_email', None)
        
        flash('Password reset successful! Please login with your new password.', 'success')
        return redirect(url_for('login'))
    
    return render_template('reset_password.html', email=session.get('reset_email'))

# Developer: Harman Singh
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

# Developer: Michel
@app.route('/artist/<int:artist_id>')
def artist_detail(artist_id):
    """Artist detail page"""
    conn = get_db()
    cursor = conn.cursor()
    
    # Get artist info with average rating
    cursor.execute("""
        SELECT a.*, 
               COALESCE(AVG(ar.rating), 0) as avg_rating,
               COUNT(DISTINCT ar.rating_id) as rating_count
        FROM Artist a
        LEFT JOIN Artist_Rating ar ON a.artist_id = ar.artist_id
        WHERE a.artist_id = ?
        GROUP BY a.artist_id
    """, (artist_id,))
    artist = cursor.fetchone()
    
    # Get active listeners count (users who listened to artist's albums in last 30 days)
    cursor.execute("""
        SELECT COUNT(DISTINCT la.user_id) as active_listeners
        FROM Listening_Activity la
        JOIN Album alb ON la.album_id = alb.album_id
        WHERE alb.artist_id = ? 
        AND la.listened_date >= date('now', '-30 days')
    """, (artist_id,))
    listeners_result = cursor.fetchone()
    active_listeners = listeners_result['active_listeners'] if listeners_result else 0
    
    # Check if current user has rated this artist
    user_rating = None
    if 'user_id' in session:
        cursor.execute("""
            SELECT rating FROM Artist_Rating
            WHERE user_id = ? AND artist_id = ?
        """, (session['user_id'], artist_id))
        rating_result = cursor.fetchone()
        user_rating = rating_result['rating'] if rating_result else None
    
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
    return render_template('artist_detail.html', artist=artist, albums=albums, 
                         active_listeners=active_listeners, user_rating=user_rating)

@app.route('/artist/<int:artist_id>/rate', methods=['POST'])
def rate_artist(artist_id):
    """Rate an artist"""
    if 'user_id' not in session:
        flash('Please login to rate artists.', 'error')
        return redirect(url_for('login'))
    
    rating = float(request.form['rating'])
    
    conn = get_db()
    cursor = conn.cursor()
    
    # Check if user already rated this artist
    cursor.execute("""
        SELECT rating_id FROM Artist_Rating 
        WHERE user_id = ? AND artist_id = ?
    """, (session['user_id'], artist_id))
    
    existing_rating = cursor.fetchone()
    
    if existing_rating:
        # Update existing rating
        cursor.execute("""
            UPDATE Artist_Rating 
            SET rating = ?, rating_date = CURRENT_TIMESTAMP
            WHERE user_id = ? AND artist_id = ?
        """, (rating, session['user_id'], artist_id))
        flash('Your rating has been updated!', 'success')
    else:
        # Insert new rating
        cursor.execute("""
            INSERT INTO Artist_Rating (user_id, artist_id, rating)
            VALUES (?, ?, ?)
        """, (session['user_id'], artist_id, rating))
        flash('Thank you for rating this artist!', 'success')
    
    conn.commit()
    conn.close()
    
    return redirect(url_for('artist_detail', artist_id=artist_id))

# Developer: Harman Singh
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
    
    # Get album credits (liner notes)
    cursor.execute("""
        SELECT musician_name, role, instrument
        FROM Album_Credit
        WHERE album_id = ?
        ORDER BY credit_id
    """, (album_id,))
    credits = cursor.fetchall()
    
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
                          reviews=reviews, is_favorite=is_favorite, credits=credits)

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

@app.route('/achievements')
def achievements():
    """Achievements page"""
    conn = get_db()
    cursor = conn.cursor()
    
    # Get all achievements
    cursor.execute("SELECT * FROM Achievement ORDER BY achievement_id")
    all_achievements = cursor.fetchall()
    
    # If user is logged in, get their earned achievements
    user_achievements = []
    if 'user_id' in session:
        cursor.execute("""
            SELECT a.*, ua.earned_date
            FROM Achievement a
            JOIN User_Achievement ua ON a.achievement_id = ua.achievement_id
            WHERE ua.user_id = ?
        """, (session['user_id'],))
        user_achievements = cursor.fetchall()
    
    conn.close()
    return render_template('achievements.html', 
                         all_achievements=all_achievements,
                         user_achievements=user_achievements)

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

# Developer: Hasham
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

@app.route('/playlist/create', methods=['POST'])
def create_playlist():
    if 'user_id' not in session:
        flash('Please login to create playlists.', 'error')
        return redirect(url_for('login'))

    playlist_name = request.form['playlist_name']

    conn = get_db()
    cursor = conn.cursor()
    cursor.execute("""
        INSERT INTO Playlist (user_id, playlist_name)
        VALUES (?, ?)
    """, (session['user_id'], playlist_name))
    conn.commit()
    conn.close()

    flash('Playlist created successfully!', 'success')
    return redirect(url_for('library'))

@app.route('/playlist/<int:playlist_id>')
def view_playlist(playlist_id):
    if 'user_id' not in session:
        flash('Please login to view playlists.', 'error')
        return redirect(url_for('login'))

    conn = get_db()
    cursor = conn.cursor()

    cursor.execute("""
        SELECT * FROM Playlist
        WHERE playlist_id = ? AND user_id = ?
    """, (playlist_id, session['user_id']))
    playlist = cursor.fetchone()

    if not playlist:
        flash('Playlist not found.', 'error')
        return redirect(url_for('library'))

    cursor.execute("""
        SELECT t.track_id, t.track_title, t.duration_seconds,
               a.album_title, ar.artist_name
        FROM Playlist_Track pt
        JOIN Track t ON pt.track_id = t.track_id
        JOIN Album a ON t.album_id = a.album_id
        JOIN Artist ar ON a.artist_id = ar.artist_id
        WHERE pt.playlist_id = ?
        ORDER BY t.track_number
    """, (playlist_id,))
    tracks = cursor.fetchall()

    cursor.execute("""
        SELECT t.track_id, t.track_title, ar.artist_name
        FROM Track t
        JOIN Album a ON t.album_id = a.album_id
        JOIN Artist ar ON a.artist_id = ar.artist_id
        ORDER BY ar.artist_name, t.track_title
    """)
    all_tracks = cursor.fetchall()

    total_duration = sum(t['duration_seconds'] for t in tracks)
    track_count = len(tracks)

    conn.close()

    return render_template(
        'playlist_view.html',
        playlist=playlist,
        tracks=tracks,
        all_tracks=all_tracks,
        total_duration=total_duration,
        track_count=track_count
    )

@app.route('/playlist/<int:playlist_id>/remove/<int:track_id>', methods=['POST'])
def remove_track(playlist_id, track_id):
    if 'user_id' not in session:
        flash('Please login.', 'error')
        return redirect(url_for('login'))

    conn = get_db()
    cursor = conn.cursor()

    cursor.execute("""
        DELETE FROM Playlist_Track
        WHERE playlist_id = ? AND track_id = ?
    """, (playlist_id, track_id))

    conn.commit()
    conn.close()

    flash('Track removed from playlist.', 'info')
    return redirect(url_for('view_playlist', playlist_id=playlist_id))

@app.route('/playlist/<int:playlist_id>/delete', methods=['POST'])
def delete_playlist(playlist_id):
    if 'user_id' not in session:
        flash('Please login.', 'error')
        return redirect(url_for('login'))

    conn = get_db()
    cursor = conn.cursor()

    # Delete all tracks inside the playlist
    cursor.execute("""
        DELETE FROM Playlist_Track
        WHERE playlist_id = ?
    """, (playlist_id,))

    # Delete the playlist itself
    cursor.execute("""
        DELETE FROM Playlist
        WHERE playlist_id = ? AND user_id = ?
    """, (playlist_id, session['user_id']))

    conn.commit()
    conn.close()

    flash('Playlist deleted successfully.', 'info')
    return redirect(url_for('library'))

@app.route('/playlist/<int:playlist_id>/rename', methods=['POST'])
def rename_playlist(playlist_id):
    if 'user_id' not in session:
        flash('Please login.', 'error')
        return redirect(url_for('login'))

    new_name = request.form['new_name']

    conn = get_db()
    cursor = conn.cursor()

    cursor.execute("""
        UPDATE Playlist
        SET playlist_name = ?
        WHERE playlist_id = ? AND user_id = ?
    """, (new_name, playlist_id, session['user_id']))

    conn.commit()
    conn.close()

    flash('Playlist renamed successfully.', 'success')
    return redirect(url_for('view_playlist', playlist_id=playlist_id))


@app.route('/playlist/<int:playlist_id>/add', methods=['POST'])
def add_track_to_playlist(playlist_id):
    if 'user_id' not in session:
        flash('Please login.', 'error')
        return redirect(url_for('login'))

    track_id = request.form['track_id']

    conn = get_db()
    cursor = conn.cursor()

    cursor.execute("""
        SELECT * FROM Playlist_Track
        WHERE playlist_id = ? AND track_id = ?
    """, (playlist_id, track_id))

    if cursor.fetchone():
        flash('Track already in playlist.', 'info')
    else:
        cursor.execute("""
            SELECT COALESCE(MAX(position), 0) + 1
            FROM Playlist_Track
            WHERE playlist_id = ?
        """, (playlist_id,))
        next_position = cursor.fetchone()[0]

        cursor.execute("""
            INSERT INTO Playlist_Track (playlist_id, track_id, position)
            VALUES (?, ?, ?)
        """, (playlist_id, track_id, next_position))

        flash('Track added!', 'success')

    conn.commit()
    conn.close()

    return redirect(url_for('view_playlist', playlist_id=playlist_id))


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
    # Run database migration to add new columns
    migrate_db()
    port = int(os.environ.get('PORT', 5000))
    app.run(debug=False, host='0.0.0.0', port=port)

