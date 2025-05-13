<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Publisher;

echo "=== Publisher Debug Script ===\n\n";

// 1. List all publishers
$publishers = Publisher::all();
echo "Found " . $publishers->count() . " publishers:\n";
foreach ($publishers as $publisher) {
    echo "ID: {$publisher->id}, Name: {$publisher->name}\n";
}

echo "\n";

// 2. Check for books with null publisher_id
$nullPublisherBooks = Book::whereNull('publisher_id')->get();
echo "Books with NULL publisher_id: " . $nullPublisherBooks->count() . "\n";

// 3. Check for books with invalid publisher_id
$validPublisherIds = Publisher::pluck('id')->toArray();
$invalidPublisherBooks = Book::whereNotNull('publisher_id')
    ->whereNotIn('publisher_id', $validPublisherIds)
    ->get();
echo "Books with invalid publisher_id: " . $invalidPublisherBooks->count() . "\n";

// 4. List a few books with their publisher info
echo "\nSample Books and Their Publishers:\n";
$sampleBooks = Book::take(5)->get();
foreach ($sampleBooks as $book) {
    $directPublisher = null;
    if ($book->publisher_id) {
        $directPublisher = DB::table('publishers')->where('id', $book->publisher_id)->first();
    }
    
    echo "Book ID: {$book->id}, Title: {$book->title}\n";
    echo "  Publisher ID: " . ($book->publisher_id ?? 'NULL') . "\n";
    echo "  Direct DB Query Publisher Name: " . ($directPublisher ? $directPublisher->name : 'None') . "\n";
    echo "  Relationship Publisher Name: " . ($book->publisher ? $book->publisher->name : 'None') . "\n";
    echo "  Is Using Default Publisher: " . ($book->publisher && $book->publisher->id === null ? 'YES' : 'NO') . "\n";
    echo "\n";
}

// 5. Check database table structure
echo "Publisher Table Structure:\n";
$publisherColumns = DB::select('SHOW COLUMNS FROM publishers');
foreach ($publisherColumns as $column) {
    echo "  {$column->Field}: {$column->Type}, Nullable: " . ($column->Null === 'YES' ? 'YES' : 'NO') . ", Default: " . ($column->Default ?? 'NULL') . "\n";
}

echo "\nBook Table Structure (publisher_id field):\n";
$bookColumns = DB::select('SHOW COLUMNS FROM books WHERE Field = "publisher_id"');
if (count($bookColumns) > 0) {
    $column = $bookColumns[0];
    echo "  {$column->Field}: {$column->Type}, Nullable: " . ($column->Null === 'YES' ? 'YES' : 'NO') . ", Default: " . ($column->Default ?? 'NULL') . "\n";
} else {
    echo "  publisher_id field not found in books table!\n";
}

// 6. Check foreign key constraints
echo "\nForeign Key Constraints:\n";
$constraints = DB::select("
    SELECT 
        CONSTRAINT_NAME,
        TABLE_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE 
        REFERENCED_TABLE_NAME = 'publishers' 
        OR (TABLE_NAME = 'books' AND COLUMN_NAME = 'publisher_id')
");

if (count($constraints) > 0) {
    foreach ($constraints as $constraint) {
        echo "  Constraint: {$constraint->CONSTRAINT_NAME}\n";
        echo "  Table: {$constraint->TABLE_NAME}, Column: {$constraint->COLUMN_NAME}\n";
        echo "  References: {$constraint->REFERENCED_TABLE_NAME}.{$constraint->REFERENCED_COLUMN_NAME}\n";
        echo "\n";
    }
} else {
    echo "  No foreign key constraints found for publishers!\n";
}

echo "\n=== Debug Complete ===\n"; 