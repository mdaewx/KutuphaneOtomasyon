<?php
// Script to copy test images to match database records
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

echo "<h1>Test Image Generator</h1>";

// Get book records with cover images
$books = DB::table('books')
    ->whereNotNull('cover_image')
    ->where('cover_image', '<>', '')
    ->select('id', 'title', 'cover_image')
    ->get();

// Source image to copy
$sampleImagePath = __DIR__ . '/img/no-cover.png';
if (!file_exists($sampleImagePath)) {
    // Create a simple text file as fallback
    file_put_contents($sampleImagePath, "This is a sample image placeholder");
    echo "<p>Created fallback sample image</p>";
}

$sampleImageContent = file_get_contents($sampleImagePath);

echo "<h2>Processing Book Cover Images</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Title</th><th>Cover Image</th><th>Status</th></tr>";

$fixed = 0;
foreach ($books as $book) {
    $targetPath = storage_path('app/public/covers/' . $book->cover_image);
    $publicPath = public_path('storage/covers/' . $book->cover_image);
    
    $status = "";
    
    // Create directory if it doesn't exist
    if (!is_dir(dirname($targetPath))) {
        mkdir(dirname($targetPath), 0777, true);
        $status .= "Created directory. ";
    }
    
    // Copy the image to storage
    if (!file_exists($targetPath)) {
        if (file_put_contents($targetPath, $sampleImageContent)) {
            $status .= "Created in storage. ";
            $fixed++;
        } else {
            $status .= "Failed to create in storage. ";
        }
    } else {
        $status .= "Already exists in storage. ";
    }
    
    // Make sure it exists in public too
    if (!file_exists($publicPath)) {
        // Check if directory exists
        if (!is_dir(dirname($publicPath))) {
            mkdir(dirname($publicPath), 0777, true);
        }
        
        if (copy($targetPath, $publicPath)) {
            $status .= "Copied to public. ";
        } else {
            $status .= "Failed to copy to public. ";
        }
    } else {
        $status .= "Already exists in public. ";
    }
    
    echo "<tr>";
    echo "<td>{$book->id}</td>";
    echo "<td>{$book->title}</td>";
    echo "<td>{$book->cover_image}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p>Fixed {$fixed} missing images</p>";

// Recreate the symbolic link
echo "<h2>Checking Storage Link</h2>";
$publicStorage = public_path('storage');
$appStorage = storage_path('app/public');

if (is_link($publicStorage)) {
    echo "<p>Storage link exists and points to: " . readlink($publicStorage) . "</p>";
} else {
    echo "<p>Storage link doesn't exist or isn't a symlink. Attempting to create...</p>";
    echo "<pre>";
    system('php artisan storage:link --force');
    echo "</pre>";
}

// Check if covers directory exists in both locations
echo "<h2>Directory Check</h2>";
echo "<p>storage/app/public/covers exists: " . (is_dir(storage_path('app/public/covers')) ? 'Yes' : 'No') . "</p>";
echo "<p>public/storage/covers exists: " . (is_dir(public_path('storage/covers')) ? 'Yes' : 'No') . "</p>";

echo "<h2>Test Links</h2>";
foreach ($books as $book) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<p>Book: {$book->title} (ID: {$book->id})</p>";
    echo "<p>Image path: {$book->cover_image}</p>";
    echo "<img src='/storage/covers/{$book->cover_image}' style='max-height: 100px; border: 1px solid #ddd;'>";
    echo "</div>";
}

echo "<p><a href='/'>Return to home page</a></p>";
?> 