<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\Publisher;
use Illuminate\Support\Facades\DB;

class FixPublisherRelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:publisher-relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix publisher relationships for books';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix publisher relationships...');

        // 1. Ensure we have publishers
        if (Publisher::count() === 0) {
            $this->info('No publishers found. Creating default publishers...');
            $this->createDefaultPublishers();
        }

        // 2. Get all books
        $books = Book::all();
        $this->info("Found {$books->count()} books to check.");

        // 3. Fix each book's publisher relation
        $fixed = 0;
        foreach ($books as $book) {
            if (!$book->publisher_id || !Publisher::find($book->publisher_id)) {
                // Get a random publisher
                $publisher = Publisher::inRandomOrder()->first();
                
                if ($publisher) {
                    $book->publisher_id = $publisher->id;
                    $book->save();
                    $fixed++;
                    
                    $this->info("Fixed book ID {$book->id}: '{$book->title}' - assigned to publisher: {$publisher->name}");
                }
            }
        }

        $this->info("Fixed publisher relationships for {$fixed} books.");
        return Command::SUCCESS;
    }

    private function createDefaultPublishers()
    {
        $publishers = [
            ['name' => 'Yapı Kredi Yayınları'],
            ['name' => 'İş Bankası Kültür Yayınları'],
            ['name' => 'Can Yayınları'],
            ['name' => 'Doğan Kitap'],
            ['name' => 'İletişim Yayınları'],
        ];

        foreach ($publishers as $publisher) {
            Publisher::create($publisher);
        }

        $this->info('Created ' . count($publishers) . ' default publishers.');
    }
}
