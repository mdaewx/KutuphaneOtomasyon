<?php
// Storage path test for debugging image uploads

echo "<h1>Storage Path Test</h1>";

// Test 1: Check if symbolic link exists
$symlink_exists = file_exists(__DIR__ . '/storage');
echo "<p><strong>Symbolic link exists:</strong> " . ($symlink_exists ? "Yes" : "No") . "</p>";

// Test 2: Check public directory permissions
$public_writable = is_writable(__DIR__);
echo "<p><strong>Public directory writable:</strong> " . ($public_writable ? "Yes" : "No") . "</p>";

// Test 3: Check covers directory
$covers_path = __DIR__ . '/storage/covers';
$covers_exists = file_exists($covers_path);
echo "<p><strong>Covers directory exists:</strong> " . ($covers_exists ? "Yes" : "No") . "</p>";

if ($covers_exists) {
    $covers_writable = is_writable($covers_path);
    echo "<p><strong>Covers directory writable:</strong> " . ($covers_writable ? "Yes" : "No") . "</p>";
}

// Test 4: Check storage directory structure
echo "<h2>Storage Directory Structure</h2>";
echo "<pre>";
function listDirectories($dir, $indent = 0) {
    if (!file_exists($dir)) return;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $dir . '/' . $file;
        echo str_repeat('  ', $indent) . $file . (is_dir($path) ? '/' : '') . "\n";
        
        if (is_dir($path)) {
            listDirectories($path, $indent + 1);
        }
    }
}

listDirectories(__DIR__ . '/storage');
echo "</pre>";

// Test 5: Check real storage path
echo "<h2>Real Storage Path</h2>";
echo "<pre>";
echo "Storage public path: " . realpath(__DIR__ . '/storage') . "\n";
echo "Storage app public path: " . realpath(__DIR__ . '/../storage/app/public') . "\n";
echo "</pre>"; 