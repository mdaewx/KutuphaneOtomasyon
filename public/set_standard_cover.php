<?php
// Standard Cover Image Generator for Library System
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

echo "<h1>📚 STANDART KAPAK RESMİ EKLEME ARACI</h1>";

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
    public_path('storage/covers')
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "<p style='color:green'>✅ Dizin oluşturuldu: $dir</p>";
    }
}

// Standart resim oluştur
$standardCoverFilename = 'standard_cover_' . time() . '.png';
$storagePath = storage_path('app/public/covers/' . $standardCoverFilename);
$publicPath = public_path('storage/covers/' . $standardCoverFilename);

// Gerçek PNG resim oluşturan fonksiyon
function createStandardCoverImage($filename, $width = 200, $height = 300) {
    // Gerçek PNG resim oluştur
    $image = imagecreatetruecolor($width, $height);
    
    // Renkleri belirle - Daha çekici bir tasarım için
    $bgColor = imagecolorallocate($image, 220, 230, 240); // Açık mavi
    $textColor = imagecolorallocate($image, 40, 40, 100); // Koyu mavi
    $borderColor = imagecolorallocate($image, 100, 120, 180); // Orta mavi
    $accentColor = imagecolorallocate($image, 180, 50, 50); // Kırmızı aksan
    
    // Arkaplanı doldur
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    
    // Kenarlık çiz
    imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);
    imagefilledrectangle($image, 10, 10, $width-11, $height-11, $borderColor);
    imagefilledrectangle($image, 15, 15, $width-16, $height-16, $bgColor);
    
    // Kitap simgesi çiz
    imagefilledrectangle($image, $width/2 - 40, 80, $width/2 + 40, 180, $accentColor);
    
    // Başlık ekle
    $text = "KİTAP";
    $font = 5; // Built-in font (larger)
    $textWidth = imagefontwidth($font) * strlen($text);
    $x = ($width - $textWidth) / 2;
    imagestring($image, $font, $x, 40, $text, $textColor);
    
    // Alt yazı
    $subtext = "Kütüphane";
    $font = 3;
    $textWidth = imagefontwidth($font) * strlen($subtext);
    $x = ($width - $textWidth) / 2;
    imagestring($image, $font, $x, 200, $subtext, $textColor);
    
    // Dosyaya kaydet
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);
    
    file_put_contents($filename, $imageData);
    return true;
}

// Standart kapak resmi oluştur
if (createStandardCoverImage($storagePath)) {
    // Public klasöre kopyala
    copy($storagePath, $publicPath);
    echo "<p style='color:green'>✅ Standart kapak resmi oluşturuldu: $standardCoverFilename</p>";
    echo "<p>Resim önizleme:</p>";
    echo "<img src='/storage/covers/$standardCoverFilename' style='border:1px solid #ccc; max-height:300px;'>";
} else {
    echo "<p style='color:red'>❌ Standart kapak resmi oluşturulamadı!</p>";
    exit;
}

// Tüm kitapların resimlerini güncelle
echo "<h2>Kitap Kayıtlarını Güncelleme</h2>";

$books = DB::table('books')->get();
$updateCount = 0;

echo "<table border='1' cellpadding='5' style='margin-bottom:20px;'>";
echo "<tr><th>ID</th><th>Kitap Adı</th><th>Durum</th></tr>";

foreach ($books as $book) {
    // Her kitabın kapak resmini standart resimle güncelle
    DB::table('books')
        ->where('id', $book->id)
        ->update(['cover_image' => $standardCoverFilename]);
    
    $updateCount++;
    
    echo "<tr>";
    echo "<td>{$book->id}</td>";
    echo "<td>{$book->title}</td>";
    echo "<td style='color:green;'>✅ Standart kapak atandı</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p style='color:green; font-weight:bold;'>✅ Toplam $updateCount kitap güncellendi!</p>";

// Önbelleği temizle
echo "<h2>Önbellek Temizleme</h2>";
system('php artisan cache:clear');
system('php artisan view:clear');
system('php artisan config:clear');
echo "<p style='color:green'>✅ Önbellek temizlendi!</p>";

echo "<div style='background-color:#e8f5e9; padding:15px; margin-top:20px; border:1px solid #66bb6a;'>";
echo "<h2 style='color:#2e7d32;'>🎉 TAMAMLANDI!</h2>";
echo "<p><strong>Tüm kitaplara standart kapak resmi atandı.</strong></p>";
echo "<p>Ana sayfayı yenileyerek (CTRL+F5) kontrol edebilirsiniz.</p>";
echo "</div>";

echo "<p><a href='/'>Ana sayfaya dön</a> | <a href='/test_images.php'>Teşhis Aracını Çalıştır</a></p>";
?> 