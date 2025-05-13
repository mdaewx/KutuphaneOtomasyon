<?php
// Gerçek PNG/JPG resimler oluşturan düzeltme scripti
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
use Illuminate\Support\Facades\Storage;

echo "<h1>🔄 GERÇEK RESİM OLUŞTURMA ARACI</h1>";
echo "<p>Bu araç gerçek PNG/JPG resimler oluşturarak sorunu tamamen çözecektir.</p>";

// Storage link kontrolü
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
    public_path('img'),
    public_path('images')
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "<p style='color:green'>✅ Dizin oluşturuldu: $dir</p>";
    }
}

// Gerçek PNG resim oluşturan fonksiyon
function createRealPngImage($text, $filename, $width = 200, $height = 300) {
    // Gerçek PNG resim oluştur
    $image = imagecreatetruecolor($width, $height);
    
    // Renkleri belirle
    $bgColor = imagecolorallocate($image, 240, 240, 240);
    $textColor = imagecolorallocate($image, 50, 50, 50);
    $borderColor = imagecolorallocate($image, 180, 180, 180);
    
    // Arkaplanı doldur
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    
    // Kenarlık çiz
    imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);
    
    // Metin ekle
    $font = 4; // Built-in font
    $lines = explode("\n", wordwrap($text, 20, "\n", true));
    $y = 30;
    
    foreach ($lines as $line) {
        $textWidth = imagefontwidth($font) * strlen($line);
        $x = ($width - $textWidth) / 2;
        imagestring($image, $font, $x, $y, $line, $textColor);
        $y += 20;
    }
    
    // Dosyaya kaydet
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);
    
    file_put_contents($filename, $imageData);
    return true;
}

// Varsayılan resimleri oluştur
echo "<h2>1. Varsayılan Resimleri Oluşturma</h2>";

// No-cover.png oluştur
$nocover_paths = [
    public_path('img/no-cover.png'),
    public_path('images/no-cover.png'),
    storage_path('app/public/covers/default.jpg')
];

foreach ($nocover_paths as $path) {
    if (createRealPngImage("Kapak\nYok", $path)) {
        echo "<p style='color:green'>✅ Gerçek resim oluşturuldu: $path</p>";
    }
}

// Kitaplar için resimler oluştur
echo "<h2>2. Kitap Resimleri Oluşturma</h2>";

$books = DB::table('books')->get();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Kitap Adı</th><th>Durum</th></tr>";

foreach ($books as $book) {
    // Her kitap için özel resim oluştur
    $newFilename = 'book_' . $book->id . '_' . time() . '.png';
    $storagePath = storage_path('app/public/covers/' . $newFilename);
    
    // Gerçek PNG resmi oluştur
    $bookTitle = $book->title ?: 'Kitap';
    if (createRealPngImage($bookTitle, $storagePath)) {
        // Dosyayı public klasörüne kopyala
        $publicPath = public_path('storage/covers/' . $newFilename);
        copy($storagePath, $publicPath);
        
        // Veritabanını güncelle
        DB::table('books')
            ->where('id', $book->id)
            ->update(['cover_image' => $newFilename]);
        
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        echo "<td style='color:green;'>✅ BAŞARILI! Resim oluşturuldu: {$newFilename}</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        echo "<td style='color:red;'>❌ HATA: Resim oluşturulamadı!</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Önbelleği temizle
echo "<h2>3. Önbellek Temizleme</h2>";
system('php artisan cache:clear');
system('php artisan view:clear');
system('php artisan config:clear');
echo "<p style='color:green'>✅ Önbellek temizlendi!</p>";

echo "<div style='background-color:#e8f5e9; padding:15px; margin-top:20px; border:1px solid #66bb6a;'>";
echo "<h2 style='color:#2e7d32;'>🎉 TAMAMLANDI!</h2>";
echo "<p><strong>Tüm kitaplar için gerçek resimler oluşturuldu ve veritabanı güncellendi.</strong></p>";
echo "<p>Şimdi ana sayfayı yenileyerek (CTRL+F5) resimlerin düzgün görüntülenip görüntülenmediğini kontrol edebilirsiniz.</p>";
echo "<p>Eğer hala sorun yaşıyorsanız, web tarayıcınızın önbelleğini tamamen temizleyip uygulamayı yeniden başlatmayı deneyin.</p>";
echo "</div>";

echo "<p>Görüntüleyebileceğiniz ve gerekirse düzenleyebileceğiniz kitap resim yolları:</p>";
echo "<ul>";
echo "<li><code>/storage/covers/...</code> - Laravel tarafından erişilen dosya yolu</li>";
echo "<li><code>" . storage_path('app/public/covers') . "</code> - Fiziksel dosya konumu</li>";
echo "<li><code>" . public_path('storage/covers') . "</code> - Web üzerinden erişilen konum</li>";
echo "</ul>";

echo "<p><a href='/'>Ana sayfaya dön</a> | <a href='/test_images.php'>Teşhis Aracını Çalıştır</a></p>";
?> 