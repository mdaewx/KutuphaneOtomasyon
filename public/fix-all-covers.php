<?php
// Script to fix all book covers
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

echo "<h1>Book Cover Fixer</h1>";

// Create fixed sample image
$sampleImage = 'sample_cover_' . time() . '.jpg';
$samplePath = 'public/covers/' . $sampleImage;

// Create a placeholder image
$width = 150;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Set colors
$bgColor = imagecolorallocate($image, 238, 238, 238);
$textColor = imagecolorallocate($image, 102, 102, 102);
$borderColor = imagecolorallocate($image, 200, 200, 200);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

// Add border
imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);

// Add text
$text = "Book Cover";
$font = 4; // Built-in font
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);
$x = ($width - $textWidth) / 2;
$y = ($height - $textHeight) / 2;
imagestring($image, $font, $x, $y, $text, $textColor);

// Save image
$tempFile = tempnam(sys_get_temp_dir(), 'cover');
imagepng($image, $tempFile);
imagedestroy($image);

// Make sure the directory exists
if (!Storage::exists('public/covers')) {
    Storage::makeDirectory('public/covers');
}

// Copy to storage
$fileContent = file_get_contents($tempFile);
Storage::put($samplePath, $fileContent);
unlink($tempFile);

echo "<p>Created sample image: <code>$sampleImage</code></p>";

// Update all books with missing cover images
$updatedBooks = DB::table('books')
    ->whereNull('cover_image')
    ->orWhere('cover_image', '')
    ->update(['cover_image' => $sampleImage]);

echo "<p>Updated $updatedBooks books with missing cover images</p>";

// Update all books where the cover image file doesn't exist
$books = DB::table('books')->whereNotNull('cover_image')->get();
$fixedBooks = 0;

echo "<h2>Checking existing book covers</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Title</th><th>Cover Path</th><th>Status</th></tr>";

foreach ($books as $book) {
    if (!empty($book->cover_image) && !file_exists(public_path('storage/covers/' . $book->cover_image))) {
        // Update with sample image
        DB::table('books')
            ->where('id', $book->id)
            ->update(['cover_image' => $sampleImage]);
        $fixedBooks++;
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        echo "<td>{$book->cover_image}</td>";
        echo "<td style='background-color:#ffd3b6;'>Fixed - missing file</td>";
        echo "</tr>";
    } else if (empty($book->cover_image)) {
        // Update with sample image
        DB::table('books')
            ->where('id', $book->id)
            ->update(['cover_image' => $sampleImage]);
        $fixedBooks++;
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        echo "<td>Empty</td>";
        echo "<td style='background-color:#ffd3b6;'>Fixed - empty value</td>";
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
echo "<img src='/storage/covers/$sampleImage' style='border:1px solid #ccc;'>";

echo "<p><a href='/'>Return to home page</a></p>";
?> 