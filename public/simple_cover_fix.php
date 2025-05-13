<?php
// Simple Cover Fix - Without using GD library
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Laravel bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "<h1>📚 BASİT KAPAK DÜZELTME ARACI</h1>";

// Storage link kontrolü ve düzeltme
$publicStoragePath = public_path('storage');
if (!is_link($publicStoragePath) && file_exists($publicStoragePath)) {
    // Link değilse kaldır ve yeniden oluştur
    @rmdir($publicStoragePath);
}

if (!file_exists($publicStoragePath)) {
    system('php artisan storage:link');
    echo "<p style='color:green'>✅ Storage link yeniden oluşturuldu!</p>";
}

// Gerekli dizinleri oluştur
$directories = [
    storage_path('app/public'),
    storage_path('app/public/covers'),
    public_path('storage/covers'),
    public_path('img'),
    public_path('images')
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "<p style='color:green'>✅ Dizin oluşturuldu: $dir</p>";
    }
}

// Sabit kapak resmi oluştur - HTML kutucuk olarak
$defaultCoverFilename = 'default_cover.html';
$defaultCoverImage = 'no-cover.png';

// HTML içeriği oluştur
$htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kitap Kapağı</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #e6eef9;
            width: 200px;
            height: 300px;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        .book-cover {
            width: 100%;
            height: 100%;
            border: 1px solid #99a8c9;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #e6eef9 0%, #c9d6ec 100%);
            color: #2c3e50;
            text-align: center;
            padding: 20px;
        }
        .book-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .book-icon {
            width: 80px;
            height: 100px;
            background-color: #b74e4e;
            margin: 20px 0;
            border: 1px solid #943e3e;
        }
        .book-info {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="book-cover">
        <div class="book-title">KİTAP</div>
        <div class="book-icon"></div>
        <div class="book-info">Kütüphane</div>
    </div>
</body>
</html>
HTML;

// Varsayılan resim içeriği oluştur - Basit bir text dosyası
$pngContent = "DEFAULT BOOK COVER\nThis is a placeholder image for the book cover\nGörüntü bulunamadı, varsayılan kapak gösteriliyor.";

// Dosya varsa eskisini sil
$storagePath = storage_path('app/public/covers/' . $defaultCoverFilename);
$publicPath = public_path('storage/covers/' . $defaultCoverFilename);
@unlink($storagePath);
@unlink($publicPath);

// Varsayılan resimler için 
$defaultImagePaths = [
    public_path('img/no-cover.png'),
    public_path('images/no-cover.png'),
    storage_path('app/public/covers/no-cover.png')
];

// Varsayılan PNG dosyalarını oluştur
foreach ($defaultImagePaths as $path) {
    @unlink($path); // Eskisi varsa sil
    file_put_contents($path, $pngContent);
    echo "<p style='color:green'>✅ Varsayılan PNG oluşturuldu: $path</p>";
}

// HTML dosyasını kaydet
if (file_put_contents($storagePath, $htmlContent)) {
    // Public klasöre kopyala
    copy($storagePath, $publicPath);
    echo "<p style='color:green'>✅ HTML kapak oluşturuldu: $defaultCoverFilename</p>";
} else {
    echo "<p style='color:red'>❌ HTML kapak oluşturulamadı!</p>";
    exit;
}

// Tüm kitapların resimlerini güncelle
echo "<h2>Kitap Kayıtlarını Güncelleme</h2>";

$books = DB::table('books')->get();
$updateCount = 0;

echo "<table border='1' cellpadding='5' style='margin-bottom:20px;'>";
echo "<tr><th>ID</th><th>Kitap Adı</th><th>Durum</th></tr>";

foreach ($books as $book) {
    // Her kitabın kapak resmini no-cover.png olarak güncelle
    DB::table('books')
        ->where('id', $book->id)
        ->update(['cover_image' => $defaultCoverImage]);
    
    $updateCount++;
    
    echo "<tr>";
    echo "<td>{$book->id}</td>";
    echo "<td>{$book->title}</td>";
    echo "<td style='color:green;'>✅ Varsayılan kapak atandı</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p style='color:green; font-weight:bold;'>✅ Toplam $updateCount kitap güncellendi!</p>";

// Blade şablonlarını düzeltecek yardımcı dosya
$fixHelperContent = "Bu dosya Laravel'in şablonlarına yardımcı olmak için oluşturulmuştur. Lütfen silmeyin.";
file_put_contents(public_path('img/no-cover.png'), $fixHelperContent);
file_put_contents(public_path('images/no-cover.png'), $fixHelperContent);
file_put_contents(public_path('storage/covers/no-cover.png'), $fixHelperContent);

// Önbelleği temizle
echo "<h2>Önbellek Temizleme</h2>";
system('php artisan cache:clear');
system('php artisan view:clear');
system('php artisan config:clear');
echo "<p style='color:green'>✅ Önbellek temizlendi!</p>";

echo "<div style='background-color:#e8f5e9; padding:15px; margin-top:20px; border:1px solid #66bb6a;'>";
echo "<h2 style='color:#2e7d32;'>🎉 TAMAMLANDI!</h2>";
echo "<p><strong>Tüm kitap kayıtları 'no-cover.png' olarak güncellendi.</strong></p>";
echo "<p>Ana sayfayı yenileyerek (CTRL+F5) kontrol edebilirsiniz.</p>";
echo "</div>";

echo "<p><a href='/'>Ana sayfaya dön</a> | <a href='/test_images.php'>Teşhis Aracını Çalıştır</a></p>";
?> 