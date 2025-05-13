<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Varsayılan yayınevlerini ekle
$publishers = [
    ['name' => 'Yapı Kredi Yayınları'],
    ['name' => 'İş Bankası Kültür Yayınları'],
    ['name' => 'Can Yayınları'],
    ['name' => 'Doğan Kitap'],
    ['name' => 'İletişim Yayınları'],
];

foreach ($publishers as $publisher) {
    if (!DB::table('publishers')->where('name', $publisher['name'])->exists()) {
        DB::table('publishers')->insert(array_merge($publisher, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}

// İlk yayınevini al ve tüm kitapları ona ata
$defaultPublisher = DB::table('publishers')->first();
if ($defaultPublisher) {
    DB::table('books')
        ->whereNull('publisher_id')
        ->update(['publisher_id' => $defaultPublisher->id]);
}

echo "Publishers added and books updated successfully!\n"; 