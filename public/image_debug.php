<?php
// Script to diagnose image upload issues
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "<h1>Image Debug Information</h1>";

// Check symbolic link
$publicPath = public_path('storage');
$storagePath = storage_path('app/public');
$linkExists = file_exists($publicPath) && is_link($publicPath);

echo "<h2>Storage Link</h2>";
echo "<p>Public path: {$publicPath}</p>";
echo "<p>Storage path: {$storagePath}</p>";
echo "<p>Symbolic link exists: " . ($linkExists ? 'Yes' : 'No') . "</p>";
if ($linkExists) {
    $target = readlink($publicPath);
    echo "<p>Link target: {$target}</p>";
}

// Display recent uploads
echo "<h2>Recent Book Covers</h2>";
$books = DB::table('books')->whereNotNull('cover_image')->orderBy('updated_at', 'desc')->take(10)->get();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Book Title</th><th>Cover Image</th><th>Status</th><th>Preview</th></tr>";

foreach ($books as $book) {
    echo "<tr>";
    echo "<td>{$book->id}</td>";
    echo "<td>{$book->title}</td>";
    echo "<td>{$book->cover_image}</td>";
    
    // Check if file exists in public storage
    $publicExists = file_exists(public_path('storage/covers/' . $book->cover_image));
    // Check if file exists in storage
    $storageExists = Storage::exists('public/covers/' . $book->cover_image);
    
    if ($publicExists && $storageExists) {
        echo "<td style='background-color:#d4edda;'>OK</td>";
    } else if (!$publicExists && $storageExists) {
        echo "<td style='background-color:#fff3cd;'>In storage but not in public</td>";
    } else if ($publicExists && !$storageExists) {
        echo "<td style='background-color:#fff3cd;'>In public but not in storage</td>";
    } else {
        echo "<td style='background-color:#f8d7da;'>Missing</td>";
    }
    
    // Preview
    echo "<td>";
    if ($publicExists) {
        echo "<img src='/storage/covers/{$book->cover_image}' height='100'>";
    } else {
        echo "No preview available";
    }
    echo "</td>";
    
    echo "</tr>";
}

echo "</table>";

// Check for directories
echo "<h2>Directory Structure</h2>";
$dirs = [
    'storage' => is_dir(public_path('storage')),
    'storage/covers' => is_dir(public_path('storage/covers')),
    'app/public' => is_dir(storage_path('app/public')),
    'app/public/covers' => is_dir(storage_path('app/public/covers'))
];

foreach ($dirs as $dir => $exists) {
    echo "<p>{$dir}: " . ($exists ? 'Exists' : 'Missing') . "</p>";
}

// Direct upload form for testing
echo "<h2>Test Image Upload</h2>";
echo "<form action='direct_upload.php' method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='test_image'>";
echo "<button type='submit'>Upload Test Image</button>";
echo "</form>";
?> 