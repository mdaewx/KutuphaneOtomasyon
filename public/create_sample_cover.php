<?php
// Script to create a sample cover image in storage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Laravel bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Use Laravel facades
use Illuminate\Support\Facades\Storage;

echo "<h1>Sample Cover Image Creator</h1>";

// Make sure the covers directory exists
if (!Storage::exists('public/covers')) {
    Storage::makeDirectory('public/covers');
    echo "<p>Created public/covers directory</p>";
}

// Set image source - a placeholder image
$source = 'https://via.placeholder.com/300x450/eeeeee/999999?text=Sample+Book+Cover';
$filename = 'sample_cover.jpg';

// Try to download the image
$imageData = @file_get_contents($source);
if ($imageData) {
    if (Storage::put('public/covers/' . $filename, $imageData)) {
        echo "<p style='color:green;'>Success! Created sample_cover.jpg</p>";
        echo "<p>The image is saved at: storage/app/public/covers/$filename</p>";
        echo "<p>It should be accessible at: /storage/covers/$filename</p>";
        
        // Check if the public link exists
        echo "<p>Public symlink exists: " . (file_exists(public_path('storage')) ? 'Yes' : 'No') . "</p>";
        echo "<p>File exists in storage: " . (Storage::exists('public/covers/' . $filename) ? 'Yes' : 'No') . "</p>";
        echo "<p>File exists in public: " . (file_exists(public_path('storage/covers/' . $filename)) ? 'Yes' : 'No') . "</p>";
        
        // Display the image
        echo "<h3>Sample Cover Image:</h3>";
        echo "<img src='/storage/covers/$filename' style='border:1px solid #ccc;'>";
    } else {
        echo "<p style='color:red;'>Failed to save image to storage</p>";
    }
} else {
    echo "<p style='color:red;'>Failed to download placeholder image.</p>";
}

// Create no-cover.png in the public/img directory
$nocover_source = 'https://via.placeholder.com/150x200/eeeeee/999999?text=No+Cover';
$nocover_destination = __DIR__ . '/img/no-cover.png';

// Create img directory if it doesn't exist
if (!file_exists(__DIR__ . '/img')) {
    mkdir(__DIR__ . '/img', 0777, true);
}

echo "<h2>Creating no-cover.png</h2>";

// Try to download the image
$nocoverData = @file_get_contents($nocover_source);
if ($nocoverData) {
    if (file_put_contents($nocover_destination, $nocoverData)) {
        echo "<p style='color:green;'>Success! Created no-cover.png</p>";
        echo "<img src='/img/no-cover.png' style='border:1px solid #ccc;'>";
    } else {
        echo "<p style='color:red;'>Failed to save no-cover.png to $nocover_destination</p>";
    }
} else {
    echo "<p style='color:red;'>Failed to download no-cover placeholder image.</p>";
}

echo "<p><a href='/'>Return to home page</a></p>";
?> 