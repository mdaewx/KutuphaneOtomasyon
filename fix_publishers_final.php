<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Publisher;

echo "=== Final Publisher Fix Script ===\n\n";

// 1. Get all publishers and output them
$publishers = Publisher::all();
echo "Found " . $publishers->count() . " publishers:\n";
foreach ($publishers as $publisher) {
    echo "ID: {$publisher->id}, Name: {$publisher->name}\n";
}

// 2. Fix database schema issues
echo "\nEnsuring database schema is correct...\n";

// Make sure the publisher_id in the books table accepts NULL values
DB::statement('ALTER TABLE books MODIFY publisher_id BIGINT UNSIGNED NULL');

// 3. Make sure all books have a valid publisher
echo "\nEnsuring all books have valid publishers...\n";

// Select a default publisher
$defaultPublisher = Publisher::first();
if (!$defaultPublisher) {
    echo "Creating default publisher...\n";
    $defaultPublisher = Publisher::create([
        'name' => 'Varsayılan Yayınevi',
        'address' => 'İstanbul',
        'phone' => '-'
    ]);
}

echo "Default publisher: {$defaultPublisher->name} (ID: {$defaultPublisher->id})\n";

// Get all valid publisher IDs
$validPublisherIds = Publisher::pluck('id')->toArray();

// Fix books with null or invalid publisher_id
$invalidBooks = Book::where(function($query) use ($validPublisherIds) {
    $query->whereNull('publisher_id')
          ->orWhereNotIn('publisher_id', $validPublisherIds);
})->get();

echo "Found " . $invalidBooks->count() . " books with invalid or null publisher_id\n";

foreach ($invalidBooks as $book) {
    $oldId = $book->publisher_id;
    $book->publisher_id = $defaultPublisher->id;
    $book->save();
    echo "Fixed book ID {$book->id}: '{$book->title}' - changed publisher_id from " . 
         ($oldId ?? 'NULL') . " to {$defaultPublisher->id}\n";
}

// 4. Clear all caches
echo "\nClearing caches...\n";
\Artisan::call('cache:clear');
\Artisan::call('view:clear');
\Artisan::call('config:clear');
\Artisan::call('route:clear');

echo "\n=== Fix Complete ===\n";
echo "All books now have valid publishers. The publisher dropdown should work correctly now.\n"; 