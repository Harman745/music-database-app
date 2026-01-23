# Online Music Database System

A comprehensive web application for discovering, managing, and reviewing music. Built with Python Flask, MySQL/SQLite, HTML, and CSS.

## Features

✅ **User Authentication**
- User registration and login
- Secure password hashing
- Session management

✅ **Music Browsing**
- Browse albums and artists
- Search functionality
- Filter by genre and ratings
- Album details with track listings

✅ **User Interactions**
- Add albums to favorites
- Write and read reviews
- Rate albums (0-5 stars)
- Personal music library

✅ **Database Features**
- Complete relational database design
- Optimized queries with joins
- Sample data included
- Support for genres, playlists, and more

## Technology Stack

- **Backend**: Python Flask
- **Database**: SQLite (easily convertible to MySQL)
- **Frontend**: HTML5, CSS3
- **Templating**: Jinja2

## Quick Start

### 1. Install Dependencies

```bash
pip install flask --break-system-packages
```

### 2. Run the Application

```bash
cd music_database_app
python app.py
```

### 3. Access the Application

Open your browser and navigate to:
```
http://localhost:5000
```

The database will be automatically created with sample data on first run.

## Project Structure

```
music_database_app/
├── app.py                 # Main Flask application
├── music_database.db      # SQLite database (auto-generated)
├── templates/             # HTML templates
│   ├── base.html         # Base template with navigation
│   ├── index.html        # Homepage
│   ├── login.html        # Login page
│   ├── register.html     # Registration page
│   ├── albums.html       # Albums listing
│   ├── album_detail.html # Album details
│   ├── artists.html      # Artists listing
│   ├── artist_detail.html# Artist details
│   ├── library.html      # User library
│   └── search.html       # Search results
└── static/
    ├── css/
    │   └── style.css     # Main stylesheet
    └── images/           # Image assets
        ├── albums/       # Album covers
        └── artists/      # Artist images
```

## Sample Users

For testing, you can create a new account or use these sample credentials after registering:
- Username: testuser
- Password: testpass123

## Database Schema

### Main Tables:
- **User**: User accounts and authentication
- **Artist**: Musicians and bands
- **Album**: Music albums
- **Track**: Individual songs
- **Genre**: Music genres
- **Playlist**: User playlists
- **Review**: Album reviews and ratings

### Junction Tables:
- **Track_Genre**: Many-to-many relationship between tracks and genres
- **Playlist_Track**: Tracks in playlists
- **User_Favorite_Album**: User's favorite albums

## Converting to MySQL

To use MySQL instead of SQLite:

### 1. Install MySQL Connector

```bash
pip install pymysql --break-system-packages
```

### 2. Create MySQL Database

```sql
CREATE DATABASE music_database_system;
```

### 3. Update app.py

Replace the SQLite connection code with MySQL:

```python
import pymysql

# Replace the get_db() function with:
def get_db():
    """Get database connection"""
    conn = pymysql.connect(
        host='localhost',
        user='your_username',
        password='your_password',
        database='music_database_system',
        cursorclass=pymysql.cursors.DictCursor
    )
    return conn
```

### 4. Update SQL Syntax

Change SQLite-specific syntax to MySQL:
- Replace `AUTOINCREMENT` with `AUTO_INCREMENT`
- Replace `INTEGER` with `INT`
- Replace `REAL` with `DECIMAL(3,1)` for ratings

## API Routes

### Public Routes:
- `GET /` - Homepage with featured albums
- `GET /albums` - Browse all albums
- `GET /album/<id>` - Album details
- `GET /artists` - Browse all artists
- `GET /artist/<id>` - Artist details
- `GET /search?q=query` - Search music
- `GET /login` - Login page
- `GET /register` - Registration page
- `POST /login` - Process login
- `POST /register` - Process registration

### Protected Routes (require login):
- `GET /library` - User's personal library
- `POST /album/<id>/review` - Add review to album
- `POST /album/<id>/favorite` - Toggle favorite status
- `GET /logout` - Logout user

## Features to Explore

1. **Browse Music**: Click on "Albums" or "Artists" in the navigation
2. **Search**: Use the search bar to find music by name
3. **View Details**: Click on any album to see tracks, reviews, and ratings
4. **Register Account**: Create an account to access personalized features
5. **Add Favorites**: Save your favorite albums to your library
6. **Write Reviews**: Share your thoughts and rate albums
7. **My Library**: Access all your favorited albums in one place

## Sample Data Included

The application comes with sample data:
- **Artists**: The Beatles, Pink Floyd, Miles Davis, Radiohead
- **Albums**: Abbey Road, The Dark Side of the Moon, Kind of Blue, OK Computer
- **Genres**: Rock, Jazz, Pop, Progressive Rock, Alternative Rock
- **9 Sample Tracks** from various albums

## Customization

### Adding More Data

You can add more music data by:
1. Modifying the `insert_sample_data()` function in `app.py`
2. Or using SQL INSERT statements directly in the database

### Styling

Modify `static/css/style.css` to customize:
- Colors and gradients
- Layout and spacing
- Typography
- Responsive breakpoints

### Adding Features

The codebase is modular and easy to extend:
- Add new routes in `app.py`
- Create new templates in `templates/`
- Extend the database schema in `init_db()`

## Security Notes

⚠️ **Important for Production**:
1. Change the `app.secret_key` in `app.py`
2. Use environment variables for sensitive data
3. Implement CSRF protection
4. Use HTTPS in production
5. Add rate limiting for API routes
6. Implement proper input validation
7. Use prepared statements (already done)

## Troubleshooting

### Database locked error
- Close all connections before restarting
- Use `conn.close()` after queries

### Port already in use
- Change the port in `app.run(port=5001)`
- Or kill the process using port 5000

### Template not found
- Ensure templates/ directory exists
- Check template filenames match routes

### CSS not loading
- Check static/css/style.css exists
- Clear browser cache
- Verify Flask is serving static files

## Future Enhancements

Potential features to add:
- [ ] User profiles with avatars
- [ ] Playlist creation and management
- [ ] Social features (follow users, share playlists)
- [ ] Music player integration
- [ ] Advanced filtering (by year, rating, genre combinations)
- [ ] Recommendation system
- [ ] Admin dashboard
- [ ] Export/import playlists
- [ ] API endpoints for mobile apps
- [ ] Email verification

## License

This project is created for educational purposes as part of a database systems course.

## Credits

Developed as an Online Music Database System project demonstrating:
- Full-stack web development
- Database design and implementation
- RESTful API principles
- User authentication
- CRUD operations
- Responsive design

---

For questions or issues, please refer to the inline comments in the code or create an issue in the project repository.
