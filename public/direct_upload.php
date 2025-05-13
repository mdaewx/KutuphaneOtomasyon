<?php
// Direct file upload test script
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

echo "<h1>Direct Image Upload Test</h1>";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    $file = $_FILES['test_image'];
    
    echo "<h2>Upload Information</h2>";
    echo "<pre>";
    print_r($file);
    echo "</pre>";
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "<p style='color: red;'>Error: " . uploadErrorMessage($file['error']) . "</p>";
    } else {
        // Ensure the directories exist
        if (!Storage::exists('public/covers')) {
            Storage::makeDirectory('public/covers');
            echo "<p>Created public/covers directory</p>";
        }
        
        // Process the upload
        try {
            $filename = time() . '_' . $file['name'];
            $path = Storage::putFileAs('public/covers', new \Illuminate\Http\File($file['tmp_name']), $filename);
            
            echo "<p style='color: green;'>File uploaded successfully!</p>";
            echo "<p>Storage path: {$path}</p>";
            echo "<p>File exists in storage: " . (Storage::exists($path) ? 'Yes' : 'No') . "</p>";
            echo "<p>File exists in public: " . (file_exists(public_path('storage/covers/' . $filename)) ? 'Yes' : 'No') . "</p>";
            
            // Display the uploaded image
            echo "<h3>Uploaded Image:</h3>";
            echo "<img src='/storage/covers/{$filename}' style='max-width: 400px; border: 1px solid #ddd;'>";
        } catch (\Exception $e) {
            echo "<p style='color: red;'>Upload failed: " . $e->getMessage() . "</p>";
        }
    }
}

// Display upload form if this is a GET request or the form wasn't submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['test_image'])) {
    echo "<h2>Upload Test Image</h2>";
    echo "<form action='' method='POST' enctype='multipart/form-data'>";
    echo "<input type='file' name='test_image' accept='image/*' required><br><br>";
    echo "<button type='submit'>Upload Image</button>";
    echo "</form>";
}

// Link back to the debug page
echo "<p><a href='image_debug.php'>Return to Image Debug</a></p>";

/**
 * Get human-readable upload error message
 */
function uploadErrorMessage($error) {
    switch ($error) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload";
        default:
            return "Unknown upload error";
    }
}
?> 