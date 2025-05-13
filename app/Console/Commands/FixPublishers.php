<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\Publisher;

class FixPublishers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-publishers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing or invalid publisher relationships for books';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting publisher relationship fixes...');

        // Get or create a default publisher
        $defaultPublisher = Publisher::first();
        
        if (!$defaultPublisher) {
            $defaultPublisher = Publisher::create([
                'name' => 'Varsayılan Yayınevi',
                'address' => 'İstanbul',
                'phone' => '-'
            ]);
            $this->info('Created default publisher: ' . $defaultPublisher->name);
        } else {
            $this->info('Using existing publisher as default: ' . $defaultPublisher->name);
        }

        // Fix books with NULL publisher_id
        $nullCount = Book::whereNull('publisher_id')->count();
        if ($nullCount > 0) {
            Book::whereNull('publisher_id')->update(['publisher_id' => $defaultPublisher->id]);
            $this->info("Fixed {$nullCount} books with NULL publisher_id");
        } else {
            $this->info('No books with NULL publisher_id found');
        }

        // Find and fix orphaned books (with publisher_id that doesn't exist)
        $validPublisherIds = Publisher::pluck('id')->toArray();
        $invalidCount = Book::whereNotIn('publisher_id', $validPublisherIds)
            ->whereNotNull('publisher_id')
            ->count();

        if ($invalidCount > 0) {
            Book::whereNotIn('publisher_id', $validPublisherIds)
                ->whereNotNull('publisher_id')
                ->update(['publisher_id' => $defaultPublisher->id]);
            $this->info("Fixed {$invalidCount} books with invalid publisher_id");
        } else {
            $this->info('No books with invalid publisher_id found');
        }

        // Confirm all books now have valid publishers
        $total = Book::count();
        $fixed = Book::whereNotNull('publisher_id')->count();
        
        $this->info("Process complete. {$fixed}/{$total} books now have valid publisher relationships.");
        
        return Command::SUCCESS;
    }
} 