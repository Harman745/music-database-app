<?php
/**
 * Add Missing Image Columns to Database
 */

require_once 'db_config.php';

echo "<!DOCTYPE html><html><head><title>Add Image Columns</title><style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1, h2 { color: #333; }
.success { color: green; }
.error { color: red; }
</style></head><body>";

echo "<h1>Adding Missing Image Columns...</h1>";

// Check if profile_image_url column exists in Artist table
$result = $conn->query("SHOW COLUMNS FROM Artist LIKE 'profile_image_url'");
if ($result->num_rows == 0) {
    // Add the column
    $sql = "ALTER TABLE Artist ADD COLUMN profile_image_url VARCHAR(500)";
    if ($conn->query($sql)) {
        echo "<p class='success'>✓ Added profile_image_url column to Artist table</p>";
    } else {
        echo "<p class='error'>❌ Error: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Column profile_image_url already exists in Artist table</p>";
}

echo "<h2 class='success'>✓ Database updated!</h2>";
echo "<p><a href='add_images.php'>Now click here to add images</a></p>";

echo "</body></html>";
?>
