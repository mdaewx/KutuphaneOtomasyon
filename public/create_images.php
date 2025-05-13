<?php
// Script to create test image files - direct approach
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Image Creator</h1>";

// Directory paths
$storageDir = __DIR__ . '/../storage/app/public/covers/';
$publicDir = __DIR__ . '/storage/covers/';
$imgDir = __DIR__ . '/img/';

// Create directories if they don't exist
if (!file_exists($storageDir)) {
    mkdir($storageDir, 0777, true);
    echo "<p>Created directory: $storageDir</p>";
}

if (!file_exists($publicDir)) {
    mkdir($publicDir, 0777, true);
    echo "<p>Created directory: $publicDir</p>";
}

if (!file_exists($imgDir)) {
    mkdir($imgDir, 0777, true);
    echo "<p>Created directory: $imgDir</p>";
}

// Create sample_cover.jpg in storage
$sampleContent = "This is a sample cover image file (would normally be binary data)";
$sampleFilename = 'sample_cover.jpg';

if (file_put_contents($storageDir . $sampleFilename, $sampleContent)) {
    echo "<p>Created {$sampleFilename} in storage</p>";
} else {
    echo "<p>Failed to create {$sampleFilename} in storage</p>";
}

// Create no-cover.png in public/img
$nocoverContent = "This is a placeholder for no-cover image";
if (file_put_contents($imgDir . 'no-cover.png', $nocoverContent)) {
    echo "<p>Created no-cover.png in public/img</p>";
} else {
    echo "<p>Failed to create no-cover.png</p>";
}

// Create a unique book cover for testing
$uniqueFilename = '0000000720708-1.jpg';
if (file_put_contents($storageDir . $uniqueFilename, "This is a unique test cover image")) {
    echo "<p>Created {$uniqueFilename} in storage</p>";
} else {
    echo "<p>Failed to create {$uniqueFilename} in storage</p>";
}

// Copy to public directory too
if (file_put_contents($publicDir . $uniqueFilename, "This is a unique test cover image")) {
    echo "<p>Created {$uniqueFilename} in public</p>";
} else {
    echo "<p>Failed to create {$uniqueFilename} in public</p>";
}

// Copy sample cover to public directory
if (file_put_contents($publicDir . $sampleFilename, $sampleContent)) {
    echo "<p>Created {$sampleFilename} in public</p>";
} else {
    echo "<p>Failed to create {$sampleFilename} in public</p>";
}

echo "<h2>Image Check</h2>";
echo "<p>Sample cover exists in storage: " . (file_exists($storageDir . $sampleFilename) ? 'Yes' : 'No') . "</p>";
echo "<p>Sample cover exists in public: " . (file_exists($publicDir . $sampleFilename) ? 'Yes' : 'No') . "</p>";
echo "<p>No-cover exists: " . (file_exists($imgDir . 'no-cover.png') ? 'Yes' : 'No') . "</p>";
echo "<p>Unique test cover exists in storage: " . (file_exists($storageDir . $uniqueFilename) ? 'Yes' : 'No') . "</p>";
echo "<p>Unique test cover exists in public: " . (file_exists($publicDir . $uniqueFilename) ? 'Yes' : 'No') . "</p>";

echo "<p>Storage directory: $storageDir</p>";
echo "<p>Public directory: $publicDir</p>";
echo "<p>Img directory: $imgDir</p>";

echo "<p><a href='/'>Return to home page</a></p>";
?> 