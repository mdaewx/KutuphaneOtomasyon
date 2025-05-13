<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

class SyncBookStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:sync-status {--book_id= : Specific book ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize book status and available_quantity with active borrowings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookId = $this->option('book_id');
        
        if ($bookId) {
            $this->syncSpecificBook($bookId);
        } else {
            $this->syncAllBooks();
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Sync a specific book's status
     */
    private function syncSpecificBook($bookId)
    {
        $book = Book::find($bookId);
        
        if (!$book) {
            $this->error("Kitap bulunamadı: ID {$bookId}");
            return;
        }
        
        $this->syncBook($book);
        $this->info("Kitap durumu güncellendi: {$book->title}");
    }
    
    /**
     * Sync all books' statuses
     */
    private function syncAllBooks()
    {
        $this->info('Tüm kitapların durum senkronizasyonu başlatılıyor...');
        
        $count = 0;
        $fixed = 0;
        
        Book::chunk(100, function ($books) use (&$count, &$fixed) {
            foreach ($books as $book) {
                $count++;
                
                $changes = $this->syncBook($book);
                if ($changes) {
                    $fixed++;
                }
                
                if ($count % 100 === 0) {
                    $this->info("{$count} kitap işlendi, {$fixed} kitap güncellendi");
                }
            }
        });
        
        $this->info("Toplam {$count} kitap işlendi, {$fixed} kitap güncellendi");
    }
    
    /**
     * Sync a single book's status
     */
    private function syncBook(Book $book)
    {
        $activeLoans = $book->activeBorrowings()->count();
        $expectedAvailable = max(0, $book->quantity - $activeLoans);
        $changes = false;
        
        if ($book->available_quantity != $expectedAvailable) {
            $this->line("Kitap: {$book->title} - available_quantity: {$book->available_quantity} -> {$expectedAvailable}");
            $book->available_quantity = $expectedAvailable;
            $changes = true;
        }
        
        $expectedStatus = $expectedAvailable > 0 ? 'available' : 'borrowed';
        if ($book->status !== $expectedStatus) {
            $this->line("Kitap: {$book->title} - status: {$book->status} -> {$expectedStatus}");
            $book->status = $expectedStatus;
            $changes = true;
        }
        
        if ($changes) {
            $book->save();
        }
        
        return $changes;
    }
}
