<?php
// Extremely Simple Cover Fix - Single Standard Image for All Books
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

echo "<h1>📚 TEK STANDART KAPAK ATAYIN</h1>";
echo "<p>Bu araç tüm kitaplar için aynı standart kapak görselini ayarlar.</p>";

// Storage link kontrolü
$publicStoragePath = public_path('storage');
if (!is_link($publicStoragePath) && file_exists($publicStoragePath)) {
    @rmdir($publicStoragePath);
}

if (!file_exists($publicStoragePath)) {
    system('php artisan storage:link');
    echo "<p style='color:green'>✅ Storage link yeniden oluşturuldu!</p>";
}

// Gerekli tüm dizinleri oluştur
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

// Varsayılan kapak adı - sistemde var olan tüm olası adlardan birini seçiyoruz
$standardCover = 'default-cover.jpg';

// Tüm olası konumlar için standart kapak oluştur
$coverLocations = [
    public_path('img/no-cover.png'),
    public_path('images/no-cover.png'),
    storage_path('app/public/covers/' . $standardCover),
    public_path('storage/covers/' . $standardCover),
    public_path('img/' . $standardCover),
    public_path('images/' . $standardCover)
];

// Her konum için tekst dosyası oluştur
$standardContent = "DEFAULT BOOK COVER\nThis is a placeholder for the book cover image\n";
$standardContent .= "Bu dosya kapak resmi yerine kullanılmaktadır.\n";
$standardContent .= date('Y-m-d H:i:s');

foreach ($coverLocations as $location) {
    file_put_contents($location, $standardContent);
    echo "<p style='color:green'>✅ Standart kapak oluşturuldu: $location</p>";
}

// Tüm kitapların veritabanı kayıtlarını güncelle
echo "<h2>Kitap Kayıtlarını Güncelleme</h2>";

try {
    // Tüm kitapların kapak alanını güncelle
    $affectedRows = DB::table('books')->update(['cover_image' => $standardCover]);
    
    echo "<p style='color:green; font-weight:bold;'>✅ Toplam $affectedRows kitap aynı standart kapak ile güncellendi!</p>";
    
    // Birkaç kitabı detaylı göster
    $books = DB::table('books')->select('id', 'title', 'cover_image')->take(5)->get();
    
    echo "<h3>Örnek Güncellenmiş Kitaplar:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Kitap Adı</th><th>Kapak Resmi</th></tr>";
    
    foreach ($books as $book) {
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        echo "<td>{$book->cover_image}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Laravel önbelleğini temizle
    echo "<h2>Laravel Önbelleğini Temizleme</h2>";
    system('php artisan cache:clear');
    system('php artisan view:clear');
    system('php artisan config:clear');
    echo "<p style='color:green'>✅ Önbellek temizlendi!</p>";
    
    echo "<div style='background-color:#e8f5e9; padding:15px; margin-top:20px; border:1px solid #66bb6a;'>";
    echo "<h2 style='color:#2e7d32;'>🎉 TAMAMLANDI!</h2>";
    echo "<p><strong>Tüm kitapların kapak resmi '$standardCover' olarak ayarlandı.</strong></p>";
    echo "<p>Ana sayfayı yenileyerek (CTRL+F5) kontrol edebilirsiniz.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Hata: " . $e->getMessage() . "</p>";
}

echo "<p><a href='/'>Ana sayfaya dön</a> | <a href='/test_images.php'>Teşhis Aracını Çalıştır</a></p>";
?> 