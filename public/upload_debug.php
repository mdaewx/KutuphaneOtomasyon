<?php
// Debug script for image uploads
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

echo "<h1>Upload Debug Tool</h1>";

// Verify storage:link connection
$publicStoragePath = public_path('storage');
$appStoragePath = storage_path('app/public');

echo "<h2>Storage Link</h2>";
if (is_link($publicStoragePath)) {
    $target = readlink($publicStoragePath);
    echo "<p>✅ Storage link exists and points to: {$target}</p>";
} else {
    echo "<p>❌ Storage link doesn't exist or isn't a symlink</p>";
}

// Check permissions
$publicStorageWritable = is_writable($publicStoragePath);
$appStorageWritable = is_writable($appStoragePath);

echo "<p>Public storage writable: " . ($publicStorageWritable ? "✅ Yes" : "❌ No") . "</p>";
echo "<p>App storage writable: " . ($appStorageWritable ? "✅ Yes" : "❌ No") . "</p>";

// Check directories
echo "<h2>Directory Structure</h2>";
$dirs = [
    'storage/app' => storage_path('app'),
    'storage/app/public' => storage_path('app/public'),
    'storage/app/public/covers' => storage_path('app/public/covers'),
    'public/storage' => public_path('storage'),
    'public/storage/covers' => public_path('storage/covers')
];

foreach ($dirs as $name => $path) {
    $exists = is_dir($path);
    $writable = is_writable($path);
    $perms = decoct(fileperms($path) & 0777);
    
    echo "<p>";
    echo "Directory <strong>{$name}</strong>: " . ($exists ? "✅ Exists" : "❌ Missing");
    if ($exists) {
        echo " | Permissions: {$perms} | Writable: " . ($writable ? "✅ Yes" : "❌ No");
    }
    echo "</p>";
}

// Test Storage::put method
echo "<h2>Testing Storage Functions</h2>";

$testContent = "This is a test file created at " . date('Y-m-d H:i:s');
$testFilename = 'test_' . time() . '.txt';

try {
    // Test basic put
    $result1 = Storage::put('public/covers/' . $testFilename, $testContent);
    echo "<p>Storage::put result: " . ($result1 ? "✅ Success" : "❌ Failed") . "</p>";
    
    // Test file exists
    $exists1 = Storage::exists('public/covers/' . $testFilename);
    echo "<p>Storage::exists result: " . ($exists1 ? "✅ File exists" : "❌ File missing") . "</p>";
    
    // Test file exists in physical location
    $physicalPath = storage_path('app/public/covers/' . $testFilename);
    $publicPath = public_path('storage/covers/' . $testFilename);
    
    echo "<p>File exists in app/public/covers: " . (file_exists($physicalPath) ? "✅ Yes" : "❌ No") . "</p>";
    echo "<p>File exists in public/storage/covers: " . (file_exists($publicPath) ? "✅ Yes" : "❌ No") . "</p>";
    
    // Test storeAs - simulates what happens in controllers
    if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        $tmpFile = $_FILES['test_file']['tmp_name'];
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tmpFile,
            $_FILES['test_file']['name'],
            $_FILES['test_file']['type'],
            $_FILES['test_file']['error']
        );
        
        $storeFilename = 'upload_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs('public/covers', $storeFilename);
        
        echo "<p>File uploaded successfully!</p>";
        echo "<p>Storage path: {$path}</p>";
        echo "<p>File exists in storage: " . (Storage::exists($path) ? "✅ Yes" : "❌ No") . "</p>";
        echo "<p>File exists in app/public: " . (file_exists(storage_path('app/' . $path)) ? "✅ Yes" : "❌ No") . "</p>";
        echo "<p>File exists in public/storage: " . (file_exists(public_path(str_replace('public/', 'storage/', $path))) ? "✅ Yes" : "❌ No") . "</p>";
        
        echo "<h3>Uploaded File Preview:</h3>";
        echo "<img src='/" . str_replace('public/', 'storage/', $path) . "' style='max-width: 400px; border: 1px solid #ddd;'>";
    }
    
} catch (\Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Display upload form
echo "<h2>Test File Upload</h2>";
echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file' accept='image/*' required><br><br>";
echo "<button type='submit'>Upload File</button>";
echo "</form>";

// Show PHP info
echo "<h2>PHP Configuration Info</h2>";
$uploadMaxSize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
$memoryLimit = ini_get('memory_limit');

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>upload_max_filesize</td><td>{$uploadMaxSize}</td></tr>";
echo "<tr><td>post_max_size</td><td>{$postMaxSize}</td></tr>";
echo "<tr><td>memory_limit</td><td>{$memoryLimit}</td></tr>";
echo "</table>";

echo "<p><a href='/'>Return to home page</a></p>";
?> 