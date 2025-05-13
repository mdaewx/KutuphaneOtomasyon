<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Publisher;

echo "Starting publisher and book repair script...\n";

// 1. Create default publishers if they don't exist
$defaultPublishers = [
    ['name' => 'Yapı Kredi Yayınları', 'address' => 'İstanbul', 'phone' => ''],
    ['name' => 'İş Bankası Kültür Yayınları', 'address' => 'İstanbul', 'phone' => ''],
    ['name' => 'Can Yayınları', 'address' => 'İstanbul', 'phone' => ''],
    ['name' => 'Doğan Kitap', 'address' => 'İstanbul', 'phone' => ''],
    ['name' => 'İletişim Yayınları', 'address' => 'İstanbul', 'phone' => ''],
];

$createdCount = 0;
foreach ($defaultPublishers as $publisherData) {
    if (!Publisher::where('name', $publisherData['name'])->exists()) {
        Publisher::create($publisherData);
        $createdCount++;
    }
}
echo "Added {$createdCount} new publishers\n";

// 2. Get the first publisher as default
$defaultPublisher = Publisher::first();
if (!$defaultPublisher) {
    echo "ERROR: No publishers found in the database!\n";
    exit(1);
}

echo "Default publisher: {$defaultPublisher->name} (ID: {$defaultPublisher->id})\n";

// 3. Find and fix books with null publisher_id
$nullPublisherBooks = Book::whereNull('publisher_id')->get();
echo "Found {$nullPublisherBooks->count()} books with NULL publisher_id\n";

foreach ($nullPublisherBooks as $book) {
    echo "Fixing book ID {$book->id}: '{$book->title}' - setting publisher_id to {$defaultPublisher->id}\n";
    $book->publisher_id = $defaultPublisher->id;
    $book->save();
}

// 4. Find and fix books with invalid publisher_id
$validPublisherIds = Publisher::pluck('id')->toArray();
$invalidPublisherBooks = Book::whereNotNull('publisher_id')
    ->whereNotIn('publisher_id', $validPublisherIds)
    ->get();

echo "Found {$invalidPublisherBooks->count()} books with invalid publisher_id\n";

foreach ($invalidPublisherBooks as $book) {
    echo "Fixing book ID {$book->id}: '{$book->title}' - changing publisher_id from {$book->publisher_id} to {$defaultPublisher->id}\n";
    $book->publisher_id = $defaultPublisher->id;
    $book->save();
}

// 5. Clear caches
echo "Clearing caches...\n";
\Artisan::call('cache:clear');
\Artisan::call('view:clear');
\Artisan::call('config:clear');

echo "Repair complete!\n";
echo "Total books fixed: " . ($nullPublisherBooks->count() + $invalidPublisherBooks->count()) . "\n";
echo "Run the application and try adding/editing books again.\n"; 