# Music Database System - PHP Version with XAMPP

This is a PHP-based music database application using MySQL (via XAMPP).

## Requirements
- XAMPP (Apache + MySQL + PHP)
- Web browser

## Installation Instructions

### 1. Install XAMPP
Download and install XAMPP from https://www.apachefriends.org/

### 2. Setup Database
1. Start XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Open phpMyAdmin: http://localhost/phpmyadmin
4. Create a new database named: `music_database_system`
5. Leave it empty (tables will be created by setup script)

### 3. Deploy Application
1. Copy the entire `music-database-app-main` folder to:
   ```
   C:\xampp\htdocs\
   ```
2. The path should be: `C:\xampp\htdocs\music-database-app-main\`

### 4. Run Database Setup
1. Open your browser and navigate to:
   ```
   http://localhost/music-database-app-main/setup_database.php
   ```
2. This will create all necessary database tables
3. Wait for confirmation message

### 5. Access the Application
Once setup is complete, access the application at:
```
http://localhost/music-database-app-main/index.php
```

## Database Configuration

The database connection settings are in `db_config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Default XAMPP user
define('DB_PASS', '');               // Default XAMPP password (empty)
define('DB_NAME', 'music_database_system');
```

If you changed your MySQL password, update `DB_PASS` in `db_config.php`.

## File Structure

### Core PHP Files
- **db_config.php** - Database connection and helper functions (used by all pages)
- **index.php** - Home page
- **login.php** - User login
- **register.php** - User registration
- **logout.php** - Logout handler
- **setup_database.php** - Database setup script

### Include Files
- **includes/header.php** - Common header/navigation
- **includes/footer.php** - Common footer

### Static Assets
- **static/css/** - Stylesheets
- **static/images/** - Images

## Features

✅ **Converted from Python/Flask to PHP**
- All database operations now use MySQL instead of SQLite
- Session management using PHP sessions
- Secure password hashing with password_hash()

✅ **Centralized Database Connection**
- `db_config.php` provides connection for all pages
- Helper functions: `fetchAll()`, `fetchOne()`, `executeQuery()`
- Prepared statements for SQL injection prevention

✅ **User Authentication**
- Registration with password hashing
- Login/logout functionality
- Session-based authentication

✅ **Database Features**
- 16 tables for complete music database
- Artists, Albums, Tracks, Genres
- User reviews and ratings
- Playlists and favorites
- Achievements system
- Listening activity tracking

## Usage

### First Time Setup
1. Run `setup_database.php` to create tables
2. Register a new user account
3. Login and start exploring

### Adding Data
You can add sample data through:
- phpMyAdmin (manual SQL inserts)
- Create admin pages for data management
- Import SQL dump files

## Troubleshooting

**Error: Connection failed**
- Make sure MySQL is running in XAMPP
- Verify database name is `music_database_system`
- Check credentials in `db_config.php`

**Error: Table doesn't exist**
- Run `setup_database.php` to create tables

**Error: Page not found**
- Verify files are in `C:\xampp\htdocs\music-database-app-main\`
- Make sure Apache is running

**Error: Cannot start Apache**
- Port 80 might be in use
- Stop IIS or change Apache port in httpd.conf

## Security Notes

🔒 **For Production Use:**
- Change default MySQL password
- Use strong session secret
- Enable HTTPS
- Add CSRF protection
- Implement rate limiting
- Validate all user inputs
- Use environment variables for credentials

## Next Steps

To complete the conversion, you may want to create PHP versions of:
- albums.php (browse albums)
- artists.php (browse artists)
- album_detail.php (album details)
- artist_detail.php (artist details)
- search.php (search functionality)
- library.php (user library)
- achievements.php (user achievements)

Each should follow the same pattern:
1. Include `require_once 'db_config.php';`
2. Query database using helper functions
3. Include header and footer
4. Display data with PHP/HTML

---

Done by Yuvraj Singh Bahia 24129212
