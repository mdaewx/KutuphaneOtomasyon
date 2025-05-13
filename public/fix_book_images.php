<?php
// Fix book images script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Laravel bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Use Laravel DB and Storage facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "<h1>Book Image Fixer</h1>";

// Check if sample image exists
$sampleImageExists = file_exists(public_path('storage/covers/sample_cover.jpg'));
echo "<p>Sample image exists: " . ($sampleImageExists ? "YES" : "NO") . "</p>";

if (!$sampleImageExists) {
    echo "<p>Sample image is missing. Please copy a JPG image to 'storage/app/public/covers/sample_cover.jpg'</p>";
    exit;
}

// Get books without cover images or with broken cover images
$books = DB::table('books')->get();

$fixed = 0;
$errors = 0;

echo "<h2>Book Cover Status</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Title</th><th>Current Cover</th><th>Status</th></tr>";

foreach ($books as $book) {
    $hasCover = !empty($book->cover_image);
    $coverExists = $hasCover && file_exists(public_path('storage/covers/' . $book->cover_image));
    
    echo "<tr>";
    echo "<td>" . $book->id . "</td>";
    echo "<td>" . $book->title . "</td>";
    echo "<td>" . ($hasCover ? $book->cover_image : 'No cover') . "</td>";
    
    // If broken or missing cover image
    if (!$coverExists) {
        try {
            // Create a copy of sample image with unique name
            $newFilename = 'book_' . $book->id . '_' . time() . '.jpg';
            if (copy(public_path('storage/covers/sample_cover.jpg'), public_path('storage/covers/' . $newFilename))) {
                // Update book record
                DB::table('books')
                    ->where('id', $book->id)
                    ->update(['cover_image' => $newFilename]);
                
                echo "<td style='background-color:#dff0d8;'>Fixed: $newFilename</td>";
                $fixed++;
            } else {
                echo "<td style='background-color:#f2dede;'>Error copying file</td>";
                $errors++;
            }
        } catch (Exception $e) {
            echo "<td style='background-color:#f2dede;'>Error: " . $e->getMessage() . "</td>";
            $errors++;
        }
    } else {
        echo "<td style='background-color:#d9edf7;'>OK</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<p>Total books: " . count($books) . "</p>";
echo "<p>Fixed: $fixed</p>";
echo "<p>Errors: $errors</p>";

echo "<p><a href='book_image_test.php'>Go to Image Test Page</a></p>";
?> 