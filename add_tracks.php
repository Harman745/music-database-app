<?php
/**
 * Add More Tracks to Albums
 * Run this to populate all albums with their complete track listings
 * Developers: Harman Singh & Hasham
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Add Album Tracks</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2, h3 { color: #333; }
.btn { display: inline-block; padding: 10px 20px; background: #1db954; color: white; 
       text-decoration: none; border-radius: 4px; margin-top: 20px; }
</style></head><body>";

echo "<h1>🎵 Adding Tracks to Albums...</h1>";

try {
    // Get album IDs
    $albums = [];
    $albumNames = [
        'Led Zeppelin IV',
        'A Love Supreme',
        'OK Computer',
        'Nevermind',
        'Highway 61 Revisited',
        'Sgt. Pepper\'s Lonely Hearts Club Band',
        'The Wall'
    ];
    
    foreach ($albumNames as $name) {
        $stmt = $conn->prepare("SELECT album_id FROM Album WHERE album_title = ?");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $albums[$name] = $row['album_id'];
        }
        $stmt->close();
    }
    
    // Led Zeppelin IV tracks
    if (isset($albums['Led Zeppelin IV'])) {
        echo "<h3>Adding Led Zeppelin IV tracks...</h3>";
        $tracks = [
            ['Black Dog', 1, 296],
            ['Rock and Roll', 2, 220],
            ['The Battle of Evermore', 3, 351],
            ['Stairway to Heaven', 4, 482],
            ['Misty Mountain Hop', 5, 278],
            ['Four Sticks', 6, 284],
            ['Going to California', 7, 215],
            ['When the Levee Breaks', 8, 427]
        ];
        
        foreach ($tracks as $track) {
            $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('siii', $track[0], $albums['Led Zeppelin IV'], $track[1], $track[2]);
            $stmt->execute();
            echo "✓ {$track[0]}<br>";
            $stmt->close();
        }
    }
    
    // A Love Supreme tracks
    if (isset($albums['A Love Supreme'])) {
        echo "<h3>Adding A Love Supreme tracks...</h3>";
        $tracks = [
            ['Acknowledgement', 1, 450],
            ['Resolution', 2, 441],
            ['Pursuance', 3, 650],
            ['Psalm', 4, 420]
        ];
        
        foreach ($tracks as $track) {
            $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('siii', $track[0], $albums['A Love Supreme'], $track[1], $track[2]);
            $stmt->execute();
            echo "✓ {$track[0]}<br>";
            $stmt->close();
        }
    }
    
    // OK Computer tracks
    if (isset($albums['OK Computer'])) {
        echo "<h3>Adding OK Computer tracks...</h3>";
        $tracks = [
            ['Airbag', 1, 284],
            ['Paranoid Android', 2, 383],
            ['Subterranean Homesick Alien', 3, 267],
            ['Exit Music (For a Film)', 4, 265],
            ['Let Down', 5, 299],
            ['Karma Police', 6, 261],
            ['Fitter Happier', 7, 117],
            ['Electioneering', 8, 210],
            ['Climbing Up the Walls', 9, 285],
            ['No Surprises', 10, 229],
            ['Lucky', 11, 259],
            ['The Tourist', 12, 324]
        ];
        
        foreach ($tracks as $track) {
            $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('siii', $track[0], $albums['OK Computer'], $track[1], $track[2]);
            $stmt->execute();
            echo "✓ {$track[0]}<br>";
            $stmt->close();
        }
    }
    
    // Nevermind tracks
    if (isset($albums['Nevermind'])) {
        echo "<h3>Adding Nevermind tracks...</h3>";
        $tracks = [
            ['Smells Like Teen Spirit', 1, 301],
            ['In Bloom', 2, 254],
            ['Come as You Are', 3, 219],
            ['Breed', 4, 183],
            ['Lithium', 5, 257],
            ['Polly', 6, 177],
            ['Territorial Pissings', 7, 143],
            ['Drain You', 8, 224],
            ['Lounge Act', 9, 156],
            ['Stay Away', 10, 212],
            ['On a Plain', 11, 196],
            ['Something in the Way', 12, 232]
        ];
        
        foreach ($tracks as $track) {
            $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('siii', $track[0], $albums['Nevermind'], $track[1], $track[2]);
            $stmt->execute();
            echo "✓ {$track[0]}<br>";
            $stmt->close();
        }
    }
    
    // Highway 61 Revisited tracks
    if (isset($albums['Highway 61 Revisited'])) {
        echo "<h3>Adding Highway 61 Revisited tracks...</h3>";
        $tracks = [
            ['Like a Rolling Stone', 1, 369],
            ['Tombstone Blues', 2, 359],
            ['It Takes a Lot to Laugh, It Takes a Train to Cry', 3, 259],
            ['From a Buick 6', 4, 199],
            ['Ballad of a Thin Man', 5, 357],
            ['Queen Jane Approximately', 6, 330],
            ['Highway 61 Revisited', 7, 209],
            ['Just Like Tom Thumb\'s Blues', 8, 331],
            ['Desolation Row', 9, 671]
        ];
        
        foreach ($tracks as $track) {
            $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('siii', $track[0], $albums['Highway 61 Revisited'], $track[1], $track[2]);
            $stmt->execute();
            echo "✓ {$track[0]}<br>";
            $stmt->close();
        }
    }
    
    // Sgt. Pepper's Lonely Hearts Club Band tracks
    if (isset($albums['Sgt. Pepper\'s Lonely Hearts Club Band'])) {
        echo "<h3>Adding Sgt. Pepper's tracks...</h3>";
        $tracks = [
            ['Sgt. Pepper\'s Lonely Hearts Club Band', 1, 122],
            ['With a Little Help from My Friends', 2, 164],
            ['Lucy in the Sky with Diamonds', 3, 208],
            ['Getting Better', 4, 168],
            ['Fixing a Hole', 5, 156],
            ['She\'s Leaving Home', 6, 215],
            ['Being for the Benefit of Mr. Kite!', 7, 157],
            ['Within You Without You', 8, 305],
            ['When I\'m Sixty-Four', 9, 157],
            ['Lovely Rita', 10, 162],
            ['Good Morning Good Morning', 11, 161],
            ['Sgt. Pepper\'s Lonely Hearts Club Band (Reprise)', 12, 79],
            ['A Day in the Life', 13, 337]
        ];
        
        foreach ($tracks as $track) {
            $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('siii', $track[0], $albums['Sgt. Pepper\'s Lonely Hearts Club Band'], $track[1], $track[2]);
            $stmt->execute();
            echo "✓ {$track[0]}<br>";
            $stmt->close();
        }
    }
    
    // The Wall - Disc 1 tracks
    if (isset($albums['The Wall'])) {
        echo "<h3>Adding The Wall tracks...</h3>";
        $tracks = [
            ['In the Flesh?', 1, 194],
            ['The Thin Ice', 2, 154],
            ['Another Brick in the Wall (Part 1)', 3, 192],
            ['The Happiest Days of Our Lives', 4, 101],
            ['Another Brick in the Wall (Part 2)', 5, 238],
            ['Mother', 6, 332],
            ['Goodbye Blue Sky', 7, 164],
            ['Empty Spaces', 8, 128],
            ['Young Lust', 9, 195],
            ['One of My Turns', 10, 215],
            ['Don\'t Leave Me Now', 11, 251],
            ['Another Brick in the Wall (Part 3)', 12, 188],
            ['Goodbye Cruel World', 13, 78],
            ['Hey You', 14, 284],
            ['Is There Anybody Out There?', 15, 164],
            ['Nobody Home', 16, 206],
            ['Vera', 17, 95],
            ['Bring the Boys Back Home', 18, 85],
            ['Comfortably Numb', 19, 382],
            ['The Show Must Go On', 20, 96],
            ['In the Flesh', 21, 253],
            ['Run Like Hell', 22, 263],
            ['Waiting for the Worms', 23, 236],
            ['Stop', 24, 30],
            ['The Trial', 25, 317],
            ['Outside the Wall', 26, 103]
        ];
        
        foreach ($tracks as $track) {
            $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('siii', $track[0], $albums['The Wall'], $track[1], $track[2]);
            $stmt->execute();
            echo "✓ {$track[0]}<br>";
            $stmt->close();
        }
    }
    
    // Complete remaining Abbey Road tracks
    $abbeyRoadResult = $conn->query("SELECT album_id FROM Album WHERE album_title = 'Abbey Road'");
    if ($abbeyRoadResult && $abbeyRoadRow = $abbeyRoadResult->fetch_assoc()) {
        echo "<h3>Adding remaining Abbey Road tracks...</h3>";
        $abbeyRoadId = $abbeyRoadRow['album_id'];
        
        $tracks = [
            ['Oh! Darling', 5, 207],
            ['Octopus\'s Garden', 6, 171],
            ['I Want You (She\'s So Heavy)', 7, 467],
            ['Here Comes the Sun', 8, 185],
            ['Because', 9, 165],
            ['You Never Give Me Your Money', 10, 242],
            ['Sun King', 11, 146],
            ['Mean Mr. Mustard', 12, 66],
            ['Polythene Pam', 13, 72],
            ['She Came in Through the Bathroom Window', 14, 117],
            ['Golden Slumbers', 15, 91],
            ['Carry That Weight', 16, 96],
            ['The End', 17, 143]
        ];
        
        foreach ($tracks as $track) {
            $checkStmt = $conn->prepare("SELECT track_id FROM Track WHERE album_id = ? AND track_number = ?");
            $checkStmt->bind_param('ii', $abbeyRoadId, $track[1]);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('siii', $track[0], $abbeyRoadId, $track[1], $track[2]);
                $stmt->execute();
                echo "✓ {$track[0]}<br>";
                $stmt->close();
            }
            $checkStmt->close();
        }
    }
    
    // Complete remaining Dark Side of the Moon tracks
    $darkSideResult = $conn->query("SELECT album_id FROM Album WHERE album_title = 'The Dark Side of the Moon'");
    if ($darkSideResult && $darkSideRow = $darkSideResult->fetch_assoc()) {
        echo "<h3>Adding remaining Dark Side of the Moon tracks...</h3>";
        $darkSideId = $darkSideRow['album_id'];
        
        $tracks = [
            ['The Great Gig in the Sky', 5, 283],
            ['Us and Them', 6, 468],
            ['Any Colour You Like', 7, 205],
            ['Brain Damage', 8, 228],
            ['Eclipse', 9, 123],
            ['On the Run', 10, 216]
        ];
        
        foreach ($tracks as $track) {
            $checkStmt = $conn->prepare("SELECT track_id FROM Track WHERE album_id = ? AND track_number = ?");
            $checkStmt->bind_param('ii', $darkSideId, $track[1]);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO Track (track_title, album_id, track_number, duration_seconds) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('siii', $track[0], $darkSideId, $track[1], $track[2]);
                $stmt->execute();
                echo "✓ {$track[0]}<br>";
                $stmt->close();
            }
            $checkStmt->close();
        }
    }
    
    echo "<h2 style='color: green;'>✓ All tracks added successfully!</h2>";
    echo "<p>Albums now have complete track listings!</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>";
}

echo "<p><a href='albums.php' class='btn'>🎵 Browse Albums</a></p>";
echo "</body></html>";
?>
