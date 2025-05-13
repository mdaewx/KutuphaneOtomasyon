<?php
// Find a placeholder image and copy it to no-cover.png

// Set image source - could be an existing image or download from web
$source = 'https://via.placeholder.com/150x200/eeeeee/999999?text=No+Cover';
$destination = __DIR__ . '/no-cover.png';

echo "Creating no-cover image...<br>";

// Try to download the image
$imageData = @file_get_contents($source);
if ($imageData) {
    if (file_put_contents($destination, $imageData)) {
        echo "Success! Created no-cover.png<br>";
        echo "<img src='/img/no-cover.png'>";
    } else {
        echo "Failed to save image to $destination";
    }
} else {
    echo "Failed to download placeholder image. Please create a no-cover.png file manually.";
}
?> 