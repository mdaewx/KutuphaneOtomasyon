<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Publisher;

class FixPublisherRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Önce yayınevi tablosunda kayıt olduğundan emin olalım
        if (Publisher::count() == 0) {
            $publishers = [
                ['name' => 'Yapı Kredi Yayınları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'İş Bankası Kültür Yayınları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Can Yayınları', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Doğan Kitap', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'İletişim Yayınları', 'created_at' => now(), 'updated_at' => now()],
            ];
            
            foreach ($publishers as $publisher) {
                Publisher::create($publisher);
            }
            
            $this->command->info('Yayınevleri eklendi.');
        }
        
        // Varsayılan yayınevi ID'sini al
        $defaultPublisher = Publisher::first();
        
        if ($defaultPublisher) {
            // publisher_id'si NULL olan kitapları güncelle
            $nullUpdated = Book::whereNull('publisher_id')->update([
                'publisher_id' => $defaultPublisher->id
            ]);
            
            // publisher_id'si dolu olup da yayınevi tablosunda karşılığı olmayanları güncelle
            $orphanedUpdated = Book::whereNotNull('publisher_id')
                ->whereDoesntHave('publisher')
                ->update([
                    'publisher_id' => $defaultPublisher->id
                ]);
            
            $this->command->info("Güncellenen kitap sayısı: Boş olanlar: {$nullUpdated}, Karşılıksız olanlar: {$orphanedUpdated}");
        } else {
            $this->command->error('Yayınevi bulunamadı!');
        }
    }
} 