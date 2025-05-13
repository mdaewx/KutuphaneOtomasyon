<?php
// Text-based Cover Image Generator
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

echo "<h1>📚 METİN TABANLI KAPAK RESMİ OLUŞTURMA ARACI</h1>";

// Storage link kontrolü ve düzeltme
$publicStoragePath = public_path('storage');
if (!is_link($publicStoragePath) && file_exists($publicStoragePath)) {
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
    public_path('images'),
    public_path('storage/covers')
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "<p style='color:green'>✅ Dizin oluşturuldu: $dir</p>";
    }
}

// Varsayılan "no-cover" dosyaları oluştur
$defaultFiles = [
    public_path('img/no-cover.png'),
    public_path('images/no-cover.png'),
    storage_path('app/public/covers/no-cover.png'),
    public_path('storage/covers/no-cover.png')
];

$defaultContent = <<<TEXT
+----------------------------+
|                            |
|          KÜTÜPHANE         |
|                            |
|                            |
|           KİTAP            |
|                            |
|                            |
|       VARSAYILAN KAPAK     |
|                            |
|                            |
+----------------------------+
TEXT;

foreach ($defaultFiles as $file) {
    file_put_contents($file, $defaultContent);
    echo "<p style='color:green'>✅ Varsayılan dosya oluşturuldu: $file</p>";
}

// Kitaplar için kapak oluştur
echo "<h2>Kitaplar için Kapak Oluşturma</h2>";

$books = DB::table('books')->get();
$updateCount = 0;

echo "<table border='1' cellpadding='5' style='margin-bottom:20px;'>";
echo "<tr><th>ID</th><th>Kitap Adı</th><th>Dosya Adı</th><th>Durum</th></tr>";

// Her kitap için metin tabanlı kapak oluştur
foreach ($books as $book) {
    $bookTitle = $book->title ?: "Kitap #{$book->id}";
    $safeTitle = preg_replace('/[^\w\d]/u', '_', $bookTitle); // Güvenli dosya adı
    $filename = "kitap_{$book->id}_{$safeTitle}.txt";
    
    // Kitabın başlığını kullanarak ASCII art kapak oluştur
    $coverContent = <<<TEXT
+----------------------------+
|                            |
|          KÜTÜPHANE         |
|                            |
|                            |
|      {$bookTitle}          |
|                            |
|                            |
|       ID: {$book->id}      |
|                            |
|                            |
+----------------------------+
TEXT;
    
    $storagePath = storage_path('app/public/covers/' . $filename);
    $publicPath = public_path('storage/covers/' . $filename);
    
    // Dosyaları kaydet
    if (file_put_contents($storagePath, $coverContent)) {
        // Public klasöre kopyala
        copy($storagePath, $publicPath);
        
        // Veritabanını güncelle
        DB::table('books')
            ->where('id', $book->id)
            ->update(['cover_image' => $filename]);
        
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$bookTitle}</td>";
        echo "<td>{$filename}</td>";
        echo "<td style='color:green;'>✅ Metin kapak oluşturuldu</td>";
        echo "</tr>";
        
        $updateCount++;
    } else {
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$bookTitle}</td>";
        echo "<td>{$filename}</td>";
        echo "<td style='color:red;'>❌ Dosya oluşturulamadı</td>";
        echo "</tr>";
    }
}

echo "</table>";
echo "<p style='color:green; font-weight:bold;'>✅ Toplam $updateCount kitap için metin tabanlı kapak oluşturuldu!</p>";

// Otomatik test dosyası
echo "<h2>Test Kapağı Önizleme</h2>";
echo "<pre style='border:1px solid #ccc; padding:10px; background:#f8f9fa;'>";
echo $defaultContent;
echo "</pre>";

// Önbelleği temizle
echo "<h2>Laravel Önbellek Temizleme</h2>";
system('php artisan cache:clear');
system('php artisan view:clear');
system('php artisan config:clear');
echo "<p style='color:green'>✅ Önbellek temizlendi!</p>";

echo "<div style='background-color:#e8f5e9; padding:15px; margin-top:20px; border:1px solid #66bb6a;'>";
echo "<h2 style='color:#2e7d32;'>🎉 TAMAMLANDI!</h2>";
echo "<p><strong>Tüm kitaplar için metin tabanlı kapak oluşturuldu.</strong></p>";
echo "<p>Bu basit metin dosyaları, gerçek resim olmasa da web sitesinde gösterilmeye çalışılan yerlerde bir dosya adı olarak görünür.</p>";
echo "<p>Ana sayfayı yenileyerek (CTRL+F5) kontrol edebilirsiniz.</p>";
echo "</div>";

echo "<p><a href='/'>Ana sayfaya dön</a> | <a href='/test_images.php'>Teşhis Aracını Çalıştır</a></p>";
?> 