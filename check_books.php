<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Kitapları kontrol et
$books = DB::table('books')->select('id', 'title', 'publisher_id')->get();
echo "Kitaplar:\n";
foreach ($books as $book) {
    $publisher = DB::table('publishers')->where('id', $book->publisher_id)->first();
    echo "ID: {$book->id}, Başlık: {$book->title}, Yayınevi ID: {$book->publisher_id}, ";
    echo "Yayınevi: " . ($publisher ? $publisher->name : '-') . "\n";
}

// Yayınevlerini kontrol et
$publishers = DB::table('publishers')->get();
echo "\nYayınevleri:\n";
foreach ($publishers as $publisher) {
    echo "ID: {$publisher->id}, Ad: {$publisher->name}\n";
}

// Yayınevi olmayan kitapları düzelt
$defaultPublisher = DB::table('publishers')->first();
if ($defaultPublisher) {
    DB::table('books')
        ->whereNull('publisher_id')
        ->update(['publisher_id' => $defaultPublisher->id]);
    echo "\nYayınevi olmayan kitaplar varsayılan yayınevine atandı.\n";
} 