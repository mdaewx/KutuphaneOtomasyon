<?php
// Script to fix all book covers by using an existing sample image
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Laravel bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Use Laravel DB facade
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "<h1>Book Cover Fix Tool</h1>";

// Check if sample cover exists
$sampleImageName = 'sample_cover.jpg';
$sampleExists = Storage::exists('public/covers/' . $sampleImageName);

if (!$sampleExists) {
    echo "<p style='color:red;'>Sample cover image does not exist! Please place a sample_cover.jpg file in storage/app/public/covers/ directory.</p>";
    exit;
}

// Update all books with missing cover images
$updatedBooks = DB::table('books')
    ->whereNull('cover_image')
    ->orWhere('cover_image', '')
    ->update(['cover_image' => $sampleImageName]);

echo "<p>Updated $updatedBooks books with missing cover images</p>";

// Update all books where the cover image file doesn't exist
$books = DB::table('books')->whereNotNull('cover_image')->get();
$fixedBooks = 0;

echo "<h2>Checking existing book covers</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Title</th><th>Cover Path</th><th>Status</th></tr>";

foreach ($books as $book) {
    if (!empty($book->cover_image) && 
        $book->cover_image != $sampleImageName && 
        !Storage::exists('public/covers/' . $book->cover_image)) {
            
        // Update with sample image
        DB::table('books')
            ->where('id', $book->id)
            ->update(['cover_image' => $sampleImageName]);
            
        $fixedBooks++;
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        echo "<td>{$book->cover_image}</td>";
        echo "<td style='background-color:#ffd3b6;'>Fixed - missing file</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        echo "<td>{$book->cover_image}</td>";
        echo "<td style='background-color:#d4edda;'>OK</td>";
        echo "</tr>";
    }
}
echo "</table>";

echo "<p>Fixed $fixedBooks additional books with broken cover image references</p>";
echo "<p>Total books updated: " . ($updatedBooks + $fixedBooks) . "</p>";

echo "<h3>Sample Image Preview</h3>";
echo "<img src='/storage/covers/$sampleImageName' style='border:1px solid #ccc;'>";

echo "<p><a href='/'>Return to home page</a></p>";
?> 