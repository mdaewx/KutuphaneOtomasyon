<?php
// Book image test file
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Book Image Test</h1>";

// Get book ID from query string
$bookId = $_GET['id'] ?? null;
$path = $_GET['path'] ?? null;

echo "<h2>Test Image Display</h2>";

// Show sample image
echo "<p>Sample image from storage:</p>";
echo "<img src='/storage/covers/sample_cover.jpg' style='max-width:300px;'><br>";
echo "<small>Path: /storage/covers/sample_cover.jpg</small>";

// Test database connection if book ID provided
if ($bookId) {
    try {
        // Include Laravel bootstrap to access DB
        require __DIR__.'/../vendor/autoload.php';
        $app = require_once __DIR__.'/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(
            $request = Illuminate\Http\Request::capture()
        );
        
        // Get book info
        $book = DB::table('books')->find($bookId);
        
        if ($book) {
            echo "<h2>Book Info (ID: $bookId)</h2>";
            echo "<pre>";
            print_r($book);
            echo "</pre>";
            
            if ($book->cover_image) {
                echo "<p>Book cover image:</p>";
                echo "<img src='/storage/covers/{$book->cover_image}' style='max-width:300px;'><br>";
                echo "<small>Path: /storage/covers/{$book->cover_image}</small>";
                
                // Check if file exists
                $coverPath = public_path('storage/covers/' . $book->cover_image);
                echo "<p>File exists: " . (file_exists($coverPath) ? "YES" : "NO") . "</p>";
                echo "<p>Full path: $coverPath</p>";
            } else {
                echo "<p>This book has no cover image.</p>";
            }
        } else {
            echo "<p>Book not found.</p>";
        }
    } catch (Exception $e) {
        echo "<h2>Error</h2>";
        echo "<p>Message: " . $e->getMessage() . "</p>";
        echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    }
}

// If specific path provided
if ($path) {
    echo "<h2>Testing Specific Path</h2>";
    echo "<p>Path: $path</p>";
    
    // Check if file exists
    $filePath = public_path($path);
    echo "<p>File exists: " . (file_exists($filePath) ? "YES" : "NO") . "</p>";
    echo "<p>Full path: $filePath</p>";
    
    // Try to display
    echo "<p>Image display attempt:</p>";
    echo "<img src='/$path' style='max-width:300px;'><br>";
}

echo "<h2>Storage Directory Structure</h2>";
echo "<pre>";
function listDirectories($dir, $indent = 0) {
    if (!file_exists($dir)) {
        echo str_repeat(' ', $indent) . "Directory does not exist: $dir\n";
        return;
    }
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $dir . '/' . $file;
        echo str_repeat(' ', $indent) . $file . (is_dir($path) ? '/' : '') . "\n";
        
        if (is_dir($path)) {
            listDirectories($path, $indent + 2);
        }
    }
}

listDirectories(public_path('storage'));
echo "</pre>";
?> 