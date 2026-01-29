# How to Add Real Audio to Your Music Database

## Option 1: Upload Your Own MP3 Files

### Step 1: Create Audio Folder
1. Create a folder: `C:\xampp\htdocs\music-database-app-main\audio\`
2. Put your MP3 files in this folder

### Step 2: Update Database
Go to phpMyAdmin and run SQL:

```sql
UPDATE Track SET audio_url = 'audio/song1.mp3' WHERE track_id = 1;
UPDATE Track SET audio_url = 'audio/song2.mp3' WHERE track_id = 2;
```

Or update all at once:
```sql
UPDATE Track SET audio_url = CONCAT('audio/', REPLACE(LOWER(track_title), ' ', '_'), '.mp3');
```

---

## Option 2: Use Public Domain Music

### Free Legal Sources:
1. **Free Music Archive** - https://freemusicarchive.org/
2. **Internet Archive** - https://archive.org/details/audio
3. **Musopen** - https://musopen.org/ (classical music)
4. **ccMixter** - http://ccmixter.org/

### Example: Internet Archive URLs
```sql
UPDATE Track 
SET audio_url = 'https://archive.org/download/collection_name/song.mp3' 
WHERE track_title = 'Song Name';
```

---

## Option 3: Use the Audio Upload Script

Run the provided script:
```
http://localhost/music-database-app-main/add_audio.php
```

This will add sample audio URLs to popular tracks.

---

## Option 4: Add Custom Audio via PHP Script

Create `upload_audio.php`:

```php
<?php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $track_id = $_POST['track_id'];
    $file = $_FILES['audio'];
    
    $upload_dir = 'audio/';
    $filename = time() . '_' . basename($file['name']);
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $stmt = $conn->prepare("UPDATE Track SET audio_url = ? WHERE track_id = ?");
        $stmt->bind_param('si', $upload_path, $track_id);
        $stmt->execute();
        echo "Audio uploaded successfully!";
    }
}
?>
```

---

## Quick Test with Sample Audio

The `add_audio.php` script includes sample MP3 URLs from:
- SoundHelix (free sample music generator)
- Internet Archive (public domain)

These are placeholder audio files you can use for testing.

---

## Important Notes:

⚠️ **Copyright Warning:** Only use audio files you have rights to:
- Your own recordings
- Public domain music
- Creative Commons licensed tracks
- Legally purchased music (for personal use only)

✅ **File Formats:** Use MP3 format for best browser compatibility

✅ **File Size:** Keep files under 10MB for faster loading
