<?php
// Resim Görüntüleme Sorunları - Detaylı Teşhis Aracı
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Resim Teşhis Aracı</title>
    <meta charset='utf-8'>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .test-box { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
        img { border: 1px solid #ddd; max-height: 100px; }
        img.broken { background: #f8d7da; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .img-test { display: inline-block; margin: 10px; text-align: center; }
    </style>
</head>
<body>
    <h1>Kitap Resmi Detaylı Teşhis Aracı</h1>
";

// Include Laravel bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

echo "<div class='test-box'>
    <h2>1. Web Sunucusu Bilgileri</h2>
    <p>PHP Sürümü: " . phpversion() . "</p>
    <p>Web Sunucu: " . $_SERVER['SERVER_SOFTWARE'] . "</p>
    <p>Bellek Limiti: " . ini_get('memory_limit') . "</p>
    <p>Maksimum Upload: " . ini_get('upload_max_filesize') . "</p>
    <p>Çalışma Dizini: " . getcwd() . "</p>
</div>";

// Storage link kontrolü
echo "<div class='test-box'>
    <h2>2. Storage Link Kontrol</h2>";
$publicStoragePath = public_path('storage');
$storageAppPublicPath = storage_path('app/public');

if (file_exists($publicStoragePath)) {
    if (is_link($publicStoragePath)) {
        $target = readlink($publicStoragePath);
        echo "<p class='success'>✅ Storage link mevcut: $target</p>";
        
        if (strpos($target, 'app/public') !== false || strpos($target, 'app\\public') !== false) {
            echo "<p class='success'>✅ Link doğru konuma işaret ediyor</p>";
        } else {
            echo "<p class='error'>❌ Link yanlış konuma işaret ediyor: $target</p>";
        }
    } else {
        echo "<p class='error'>❌ storage dizini var ama sembolik link değil! Normal klasör.</p>";
    }
} else {
    echo "<p class='error'>❌ Storage link bulunamadı!</p>";
}

if (file_exists($storageAppPublicPath)) {
    echo "<p class='success'>✅ storage/app/public dizini mevcut</p>";
} else {
    echo "<p class='error'>❌ storage/app/public dizini bulunamadı!</p>";
}
echo "</div>";

// Klasör izinleri
echo "<div class='test-box'>
    <h2>3. Klasör İzinleri ve Yapısı</h2>";
$directories = [
    'public' => public_path(),
    'public/storage' => public_path('storage'),
    'public/storage/covers' => public_path('storage/covers'),
    'storage/app/public' => storage_path('app/public'),
    'storage/app/public/covers' => storage_path('app/public/covers'),
];

echo "<table>
    <tr>
        <th>Klasör</th>
        <th>Mevcut</th>
        <th>İzinler</th>
        <th>Yazılabilir</th>
    </tr>";

foreach ($directories as $name => $path) {
    echo "<tr>";
    echo "<td>$name</td>";
    
    $exists = file_exists($path);
    echo "<td>" . ($exists ? "<span class='success'>✅ Evet</span>" : "<span class='error'>❌ Hayır</span>") . "</td>";
    
    if ($exists) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "<td>$perms</td>";
        
        $writable = is_writable($path);
        echo "<td>" . ($writable ? "<span class='success'>✅ Evet</span>" : "<span class='error'>❌ Hayır</span>") . "</td>";
    } else {
        echo "<td colspan='2'><span class='error'>Klasör bulunamadı</span></td>";
    }
    
    echo "</tr>";
}
echo "</table>
</div>";

// Veritabanı kayıtlarını kontrol etme
echo "<div class='test-box'>
    <h2>4. Veritabanı Kayıtları</h2>";
try {
    $books = DB::table('books')->select('id', 'title', 'cover_image')->get();
    
    echo "<table>
        <tr>
            <th>ID</th>
            <th>Kitap</th>
            <th>Cover Image Değeri</th>
            <th>Dosya Var Mı?</th>
            <th>URL Test</th>
        </tr>";
    
    foreach ($books as $book) {
        echo "<tr>";
        echo "<td>{$book->id}</td>";
        echo "<td>{$book->title}</td>";
        
        // Cover image değeri
        if (!empty($book->cover_image)) {
            echo "<td>{$book->cover_image}</td>";
            
            // Dosya kontrolü
            $storageFilePath = storage_path('app/public/covers/' . $book->cover_image);
            $publicFilePath = public_path('storage/covers/' . $book->cover_image);
            
            $storageExists = file_exists($storageFilePath);
            $publicExists = file_exists($publicFilePath);
            
            if ($storageExists && $publicExists) {
                echo "<td class='success'>✅ Var (Her iki konumda)</td>";
            } else if ($storageExists) {
                echo "<td class='warning'>⚠️ Sadece storage'da var</td>";
            } else if ($publicExists) {
                echo "<td class='warning'>⚠️ Sadece public'de var</td>";
            } else {
                echo "<td class='error'>❌ Dosya yok!</td>";
            }
            
            // URL Test
            $imageUrl = "/storage/covers/{$book->cover_image}";
            echo "<td><img src='{$imageUrl}' alt='{$book->title}' onerror=\"this.classList.add('broken');this.alt='HATA';\"></td>";
        } else {
            echo "<td class='error'>❌ Boş!</td>";
            echo "<td class='error'>❌ Resim yok</td>";
            echo "<td>-</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Veritabanı hatası: " . $e->getMessage() . "</p>";
}
echo "</div>";

// HTML ve Javascript test
echo "<div class='test-box'>
    <h2>5. HTML Görüntüleme Testi</h2>
    <p>Her formatta test edilen resimler:</p>
    
    <div class='img-test'>
        <img src='/storage/covers/sample_cover.jpg' alt='Test 1'>
        <p>storage/covers/sample_cover.jpg</p>
    </div>
    
    <div class='img-test'>
        <img src='/img/no-cover.png' alt='Test 2'>
        <p>img/no-cover.png</p>
    </div>
    
    <div class='img-test'>
        <img src='/images/no-cover.png' alt='Test 3'>
        <p>images/no-cover.png</p>
    </div>
    
    <div class='img-test'>
        <img src='/storage/test.jpg' alt='Test 4' onerror=\"this.classList.add('broken');this.alt='HATA';\">
        <p>Olmayan Dosya Testi</p>
    </div>
</div>";

// Manuel dosya oluşturma
echo "<div class='test-box'>
    <h2>6. Dosya Oluşturma Testi</h2>";

$testFilename = 'test_' . time() . '.txt';
$testContent = "Test içerik - " . date('Y-m-d H:i:s');

// Storage path'e yazma
$storageFilePath = storage_path('app/public/covers/' . $testFilename);
$storageResult = @file_put_contents($storageFilePath, $testContent);

if ($storageResult !== false) {
    echo "<p class='success'>✅ Storage'a yazma başarılı: $storageFilePath</p>";
} else {
    echo "<p class='error'>❌ Storage'a yazma başarısız: $storageFilePath</p>";
}

// Public path'e yazma
$publicFilePath = public_path('storage/covers/' . $testFilename);
$publicResult = @file_put_contents($publicFilePath, $testContent);

if ($publicResult !== false) {
    echo "<p class='success'>✅ Public'e yazma başarılı: $publicFilePath</p>";
} else {
    echo "<p class='error'>❌ Public'e yazma başarısız: $publicFilePath</p>";
}
echo "</div>";

// Çözüm önerileri
echo "<div class='test-box'>
    <h2>7. Çözüm Önerileri</h2>
    <ol>";

if (!is_link($publicStoragePath)) {
    echo "<li class='error'>❌ Storage link hatalı! Şu komutu çalıştırın: <code>php artisan storage:link</code></li>";
}

if (!file_exists(storage_path('app/public/covers'))) {
    echo "<li class='error'>❌ Covers klasörü eksik! Oluşturun: <code>mkdir -p " . storage_path('app/public/covers') . "</code></li>";
}

if (!is_writable(storage_path('app/public'))) {
    echo "<li class='error'>❌ Storage klasöründe yazma izni yok! İzinleri düzeltin.</li>";
}

$placeholderMissing = true;
if (file_exists(public_path('img/no-cover.png'))) {
    $placeholderMissing = false;
}

if ($placeholderMissing) {
    echo "<li class='error'>❌ Varsayılan resim eksik! <code>php public/fix_all_images.php</code> betiğini çalıştırın.</li>";
}

echo "<li>Tarayıcı önbelleğini temizleyin (CTRL+F5)</li>";
echo "<li>Hala sorun varsa, <code>php public/fix_all_images.php</code> betiğini tekrar çalıştırın.</li>";
echo "<li>Laravel önbelleğini temizlemek için: <code>php artisan cache:clear && php artisan view:clear</code></li>";

echo "</ol>
</div>";

echo "<p><strong>Ek Bilgi:</strong> Sorunun devam etmesi durumunda <code>php public/fix_all_images.php</code> betiğini tekrar çalıştırın ve sonucu kontrol edin.</p>";

echo "<p><a href='/'>Ana sayfaya dön</a> | <a href='/fix_all_images.php'>Tüm Resimleri Düzelt</a></p>";

echo "</body></html>";
?> 